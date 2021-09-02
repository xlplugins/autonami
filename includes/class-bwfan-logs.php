<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BWFAN_Logs {
	private static $ins = null;
	public $show_filtered_logs_count = false;
	public $filtered_logs_count = 0;

	public function __construct() {
		//
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Insert a single log into the db table.
	 *
	 * @param $automation_id
	 * @param $log_data
	 *
	 * @return int
	 */
	public function insert_log( $automation_id, $log_data ) {
		$new_log_data = array(
			'e_date'             => current_time( 'timestamp', 1 ),
			'c_date'             => current_time( 'mysql', 1 ),
			'status'             => $log_data['status'],
			'integration_slug'   => $log_data['integration_slug'],
			'integration_action' => $log_data['action_slug'],
			'automation_id'      => $automation_id,
		);

		BWFAN_Model_Logs::insert( $new_log_data );

		return BWFAN_Model_Logs::insert_id();
	}

	/**
	 * Insert meta of a single log in db table.
	 *
	 * @param $log_id
	 * @param $key
	 * @param $value
	 */
	public function insert_logmeta( $log_id, $key, $value ) {
		$meta_data = array(
			'bwfan_log_id' => $log_id,
			'meta_key'     => $key,
			'meta_value'   => maybe_serialize( $value ),
		);
		BWFAN_Model_Logmeta::insert( $meta_data );
	}

	/**
	 * Retry all the failed logs.
	 *
	 * @param $automation_id
	 */
	public function retry_failed_logs( $automation_id ) {
		$logs = BWFAN_Model_Logs::get_logs( 1, array( $automation_id ), 4 );
		if ( is_array( $logs ) && count( $logs ) > 0 ) {
			$log_ids = [];
			foreach ( $logs as $log_details ) {
				$log_ids[] = $log_details['ID'];
			}
			$this->rescheduled_logs( $log_ids );
		}
	}

	/**
	 * Reschedule logs to run at user specified time when user bulk execute logs
	 *
	 * @param $log_ids
	 */
	public function rescheduled_logs( $log_ids ) {
		if ( ! is_array( $log_ids ) || empty( $log_ids ) ) {
			return;
		}

		foreach ( $log_ids as $log_id ) {
			$log_details = BWFAN_Model_Logs::get( $log_id );

			if ( ! is_array( $log_details ) || 0 === count( $log_details ) ) {
				continue;
			}

			$tasks_meta_value = BWFAN_Model_Logmeta::get_meta( $log_id, 'integration_data' );
			$task_log_message = BWFAN_Model_Logmeta::get_meta( $log_id, 'task_message' );

			if ( empty( $tasks_meta_value ) ) {
				continue;
			}

			unset( $log_details['ID'] );
			unset( $log_details['c_date'] );

			$log_details['c_date'] = current_time( 'mysql', 1 );
			$log_details['e_date'] = current_time( 'timestamp', 1 );
			$log_details['status'] = 0;
			$new_task_data         = $log_details;

			BWFAN_Model_Tasks::insert( $new_task_data );
			$task_id = BWFAN_Model_Tasks::insert_id();
			BWFAN_Core()->tasks->insert_taskmeta( $task_id, 'integration_data', $tasks_meta_value );
			BWFAN_Core()->tasks->insert_taskmeta( $task_id, 'task_message', $task_log_message );

			$this->delete_logs( array( $log_id ) );
		}
	}

	/**
	 * Delete logs.
	 *
	 * @param array $log_ids
	 * @param array $automation_ids
	 */
	public function delete_logs( $log_ids = array(), $automation_ids = array() ) {
		global $wpdb;
		if ( is_array( $automation_ids ) && count( $automation_ids ) > 0 ) {
			$automationCount        = count( $automation_ids );
			$stringPlaceholders     = array_fill( 0, $automationCount, '%s' );
			$placeholdersautomation = implode( ', ', $stringPlaceholders );
			$sql_query              = "Select ID FROM {table_name} WHERE automation_id IN ($placeholdersautomation)";
			$sql_query              = $wpdb->prepare( $sql_query, $automation_ids ); // WPCS: unprepared SQL OK
			$tasks                  = BWFAN_Model_Logs::get_results( $sql_query );
			if ( is_array( $tasks ) && count( $tasks ) > 0 ) {
				$log_ids = array();
				foreach ( $tasks as $value1 ) {
					$log_ids[] = $value1['ID'];
				}
			}
		}

		if ( is_array( $log_ids ) && count( $log_ids ) > 0 ) {
			/** Delete Logs */
			$automationCount        = count( $log_ids );
			$stringPlaceholders     = array_fill( 0, $automationCount, '%s' );
			$placeholdersautomation = implode( ', ', $stringPlaceholders );
			$sql_query              = "Delete FROM {table_name} WHERE ID IN ($placeholdersautomation)";
			$sql_query              = $wpdb->prepare( $sql_query, $log_ids ); // WPCS: unprepared SQL OK
			BWFAN_Model_Logs::delete_multiple( $sql_query );

			/** Delete Logs Meta */
			$sql_query = "Delete FROM {table_name} WHERE bwfan_log_id IN ($placeholdersautomation)";
			$sql_query = $wpdb->prepare( $sql_query, $log_ids ); // WPCS: unprepared SQL OK
			BWFAN_Model_Logmeta::delete_multiple( $sql_query );
		}
	}

	/**
	 * Return all the logs
	 * @return array
	 */
	public function get_all_logs( $no_limit = null ) {
		global $wpdb;
		$per_page = BWFAN_Logs_Table::$per_page;
		$offset   = ( BWFAN_Logs_Table::$current_page - 1 ) * $per_page;

		BWFAN_Core()->automations->return_all = true;
		$active_automations                   = BWFAN_Core()->automations->get_all_automations();
		BWFAN_Core()->automations->return_all = false;

		/* Filter option - Automation handling */
		$automation_id = ( isset( $_GET['filter_aid'] ) && ! empty( $_GET['filter_aid'] ) ) ? sanitize_text_field( $_GET['filter_aid'] ) : null; //phpcs:ignore WordPress.Security.NonceVerification
		if ( is_null( $automation_id ) && isset( $_GET['edit'] ) && ! empty( $_GET['edit'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			$automation_id = sanitize_text_field( $_GET['edit'] ); //phpcs:ignore WordPress.Security.NonceVerification
		}

		/* Filter option - Action handling */
		$log_action = ( isset( $_GET['filter_action'] ) && ! empty( $_GET['filter_action'] ) ) ? sanitize_text_field( $_GET['filter_action'] ) : null; //phpcs:ignore WordPress.Security.NonceVerification

		/* Filter option - Status handling */
		$log_status = ( isset( $_GET['status'] ) && '' !== $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'l_1'; //phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $log_status, '_' ) !== false ) {
			$log_status = explode( '_', $log_status );
			$log_status = intval( $log_status[1] );
		} else {
			$log_status = 1;
		}

		$query_select = 'SELECT `ID`, `integration_slug`, `integration_action`, `automation_id`, `status`, `e_date`';
		$query_from   = 'FROM {table_name}';
		$query_where  = 'WHERE 1=1';
		$query_order  = 'ORDER BY e_date DESC, ID DESC';
		/** e_date DESC, ID DESC */
		$query_limit = '';
		$params      = [];

		/** Filter option - Search */
		$search = false;
		if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$search                         = true;
			$query_select                   = 'SELECT l.ID, l.integration_slug, l.integration_action, l.automation_id, l.status, l.e_date ';
			$query_from                     .= ' as l';
			$query_from                     .= ' LEFT JOIN ' . $wpdb->prefix . 'bwfan_logmeta as m';
			$query_from                     .= ' ON l.ID = m.bwfan_log_id';
			$query_where                    .= ' AND m.meta_value LIKE %s';
			$params[]                       = '%' . sanitize_text_field( $_GET['s'] ) . '%'; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$query_order                    = ' ORDER BY l.e_date DESC, l.ID DESC';
			$this->show_filtered_logs_count = true;
		}

		if ( ! is_null( $automation_id ) ) {
			$query_where                    .= ( true === $search ) ? ' AND l.automation_id = %d' : ' AND `automation_id` = %d';
			$params[]                       = $automation_id;
			$this->show_filtered_logs_count = true;
		}

		if ( ! is_null( $log_action ) ) {
			$query_where                    .= ( true === $search ) ? ' AND l.integration_action = %s' : ' AND `integration_action` = %s';
			$params[]                       = $log_action;
			$this->show_filtered_logs_count = true;
		}

		if ( ! is_null( $log_status ) ) {
			$query_where                    .= ( true === $search ) ? ' AND l.status = %d' : ' AND `status` = %d';
			$params[]                       = $log_status;
			$this->show_filtered_logs_count = true;
		}

		$query_limit .= 'LIMIT %d OFFSET %d';
		$params[]    = $per_page;
		$params[]    = $offset;

		$new_query = $wpdb->prepare( "{$query_select} {$query_from} {$query_where} {$query_order} {$query_limit}", $params ); //phpcs:ignore WordPress.DB.PreparedSQLPlaceholders, WordPress.DB.PreparedSQL

		$active_logs = BWFAN_Model_Logs::get_results( $new_query );
		if ( $this->show_filtered_logs_count ) {
			$logs_count   = 0;
			$query_select = 'SELECT COUNT(`ID`) as `count`';
			if ( true === $search ) {
				$query_select = 'SELECT COUNT(l.ID) as `count`';
			}

			array_pop( $params );
			array_pop( $params );
			$new_query  = $wpdb->prepare( "{$query_select} {$query_from} {$query_where} {$query_order}", $params ); //phpcs:ignore WordPress.DB.PreparedSQLPlaceholders, WordPress.DB.PreparedSQL
			$count_logs = BWFAN_Model_Logs::get_results( $new_query );
			if ( is_array( $count_logs ) && count( $count_logs ) > 0 ) {
				$logs_count = $count_logs[0]['count'];
			}
			$this->filtered_logs_count = $logs_count;
		}

		$result = BWFAN_Core()->tasks->make_data_for_tasks( $active_automations, $active_logs, 'log' );

		return $result;
	}

	/**
	 * Update log meta of a single log.
	 *
	 * @param $log_ids
	 * @param $metakey
	 * @param $metavalue
	 */
	public function update_logmeta( $log_ids, $metakey, $metavalue ) {
		global $wpdb;
		if ( is_array( $log_ids ) && count( $log_ids ) > 0 ) {
			$automationCount        = count( $log_ids );
			$stringPlaceholders     = array_fill( 0, $automationCount, '%s' );
			$placeholdersautomation = implode( ', ', $stringPlaceholders );
			$sql_query              = "Update {table_name} SET meta_value = '$metavalue' WHERE meta_key = '$metakey' AND bwfan_log_id IN ($placeholdersautomation)";
			$sql_query              = $wpdb->prepare( $sql_query, $log_ids ); // WPCS: unprepared SQL OK
			BWFAN_Model_Logmeta::update_multiple( $sql_query );
		}
	}

	/**
	 * Remove all the logs of a connector or integration.
	 *
	 * @param $integration_slug
	 */
	public function remove_logs_on_connector_disconnection( $integration_slug ) {
		$logs = $this->get_logs_by_key( 'integration_slug', $integration_slug );
		if ( ! is_array( $logs ) || 0 === count( $logs ) ) {
			return;
		}

		// Get all log ids related to integration
		$logs_to_delete = [];
		foreach ( $logs as $log_details ) {
			switch ( $log_details['integration_slug'] ) {
				case $integration_slug:
					$logs_to_delete[] = $log_details['ID'];
					break;
			}
		}

		// Delete all logs
		if ( is_array( $logs_to_delete ) && count( $logs_to_delete ) > 0 ) {
			$this->delete_logs( $logs_to_delete );
		}
	}

	public function get_logs_by_key( $col_key, $col_value ) {
		global $wpdb;
		$query = $wpdb->prepare( 'Select ID, integration_slug, integration_action, automation_id, status, e_date from {table_name} WHERE {col_name} = %s ORDER BY e_date ASC', $col_value );
		$query = str_replace( '{col_name}', $col_key, $query );
		$tasks = BWFAN_Model_Logs::get_results( $query );

		return $tasks;
	}

	/**
	 * Returns Logs count
	 *
	 * @param string $automation_id
	 *
	 * @return int|string|null
	 */
	public function get_logs_count( $automation_id = '' ) {
		global $wpdb;

		if ( empty( $automation_id ) ) {
			$tasks_count = BWFAN_Model_Logs::count_rows();
		} else {
			$args        = array(
				'automation_id'    => $automation_id,
				'automation_table' => $wpdb->prefix . 'bwfan_automations',
				'automation_col'   => 'ID',
			);
			$tasks_count = BWFAN_Model_Logs::count_rows( $args );
		}

		return $tasks_count;
	}

	public function fetch_logs_count( $status ) {
		global $wpdb;
		$query         = 'SELECT count(ID) as logs_count FROM {table_name} WHERE status = %d';
		$query         = $wpdb->prepare( $query, $status ); // WPCS: unprepared SQL OK
		$automation_id = null;

		if ( isset( $_GET['filter_aid'] ) && ! empty( $_GET['filter_aid'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			$automation_id = sanitize_text_field( $_GET['filter_aid'] ); //phpcs:ignore WordPress.Security.NonceVerification
		}

		if ( isset( $_GET['automations'] ) && ! empty( $_GET['automations'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			$automation_id = sanitize_text_field( $_GET['automations'] ); //phpcs:ignore WordPress.Security.NonceVerification
		}

		if ( ! is_null( $automation_id ) ) {
			$query = 'SELECT count(ID) as logs_count FROM {table_name} WHERE status = %d and automation_id = %d';
			$query = $wpdb->prepare( $query, $status, $automation_id ); // WPCS: unprepared SQL OK
		}
		$logs_count = BWFAN_Model_Logs::get_results( $query );
		$logs_count = $logs_count[0]['logs_count'];

		return $logs_count;
	}

	/**
	 * Delete all the tasks of the automation by tasks indexes which is present in log meta.
	 *
	 * @param $automation_id
	 * @param $t_to_delete
	 */
	public function delete_by_index_ids( $automation_id, $t_to_delete ) {
		global $wpdb;
		$meta_key             = 't_track_id';
		$t_to_delete_count    = count( $t_to_delete );
		$prepare_placeholders = array_fill( 0, $t_to_delete_count, '%s' );
		$prepare_placeholders = implode( ', ', $prepare_placeholders );
		$sql_query            = "Select bwfan_log_id FROM {table_name} WHERE meta_key = %s AND meta_value IN ($prepare_placeholders)";
		$sql_query            = $wpdb->prepare( $sql_query, array_merge( array( $meta_key ), $t_to_delete ) ); // WPCS: unprepared SQL OK
		$log_ids              = BWFAN_Model_Logmeta::get_results( $sql_query );

		if ( ! is_array( $log_ids ) || 0 === count( $log_ids ) ) {
			return;
		}

		$log_ids = array_column( $log_ids, 'bwfan_log_id' );

		// Now get all tasks by automation_id
		$log_ids_count        = count( $log_ids );
		$prepare_placeholders = array_fill( 0, $log_ids_count, '%s' );
		$prepare_placeholders = implode( ', ', $prepare_placeholders );
		$sql_query            = "Select ID FROM {table_name} WHERE automation_id = %d AND status = %d AND ID IN ($prepare_placeholders)";
		$sql_query            = $wpdb->prepare( $sql_query, array_merge( array( intval( $automation_id ), 0 ), $log_ids ) ); // WPCS: unprepared SQL OK
		$log_ids              = BWFAN_Model_Logs::get_results( $sql_query );

		if ( ! is_array( $log_ids ) || 0 === count( $log_ids ) ) {
			return;
		}

		$log_ids = array_column( $log_ids, 'ID' );

		$this->delete_logs( $log_ids );
	}

	/** get task history data
	 *
	 * @param string $status
	 * @param string $automation_id
	 * @param string $log_action
	 * @param string $search
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array
	 */
	public function get_history( $status = 'l_1', $automation_id = '', $log_action = '', $search = '', $offset = 0, $limit = 25 ) {
		global $wpdb;

		BWFAN_Core()->automations->return_all = true;
		$active_automations                   = BWFAN_Core()->automations->get_all_automations();
		BWFAN_Core()->automations->return_all = false;

		// If logs are filtered, then show the count of filtered data
		if ( BWFAN_Core()->logs->show_filtered_logs_count ) {
			$found_posts['found_posts'] = BWFAN_Core()->logs->filtered_logs_count;
		}

		/* Filter option - Status handling */
		$log_status = ( ! empty( $status ) ) ? $status : 'l_0'; //phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $log_status, '_' ) !== false ) {
			$log_status = explode( '_', $log_status );
			$log_status = intval( $log_status[1] );
		} else {
			$log_status = 1;
		}
		$log_table    = $wpdb->prefix . 'bwfan_logs';
		$query_select = 'SELECT `ID`, `integration_slug`, `integration_action`, `automation_id`, `status`, `e_date`';
		$query_from   = 'FROM ' . $log_table;
		$query_where  = ' WHERE 1=1';
		$query_order  = 'ORDER BY e_date DESC, ID DESC';
		/** e_date DESC, ID DESC */
		$query_limit = '';
		$params      = [];

		/** Filter option - Search */
		if ( isset( $search ) && ! empty( $search ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$query_select                   = 'SELECT l.ID, l.integration_slug, l.integration_action, l.automation_id, l.status, l.e_date ';
			$query_from                     .= ' as l';
			$query_from                     .= ' LEFT JOIN ' . $wpdb->prefix . 'bwfan_logmeta as m';
			$query_from                     .= ' ON l.ID = m.bwfan_log_id';
			$query_where                    .= ' AND m.meta_value LIKE %s';
			$params[]                       = '%' . $search . '%'; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$query_order                    = ' ORDER BY l.e_date DESC, l.ID DESC';
			$this->show_filtered_logs_count = true;
		}

		if ( ! empty( $automation_id ) ) {
			$query_where                    .= ( ! empty( $search ) ) ? ' AND l.automation_id = %d' : ' AND `automation_id` = %d';
			$params[]                       = $automation_id;
			$this->show_filtered_logs_count = true;
		}

		if ( ! empty( $log_action ) ) {
			$query_where                    .= ( ! empty( $search ) ) ? ' AND l.integration_action = %s' : ' AND `integration_action` = %s';
			$params[]                       = $log_action;
			$this->show_filtered_logs_count = true;
		}

		$query_where                    .= ( ! empty( $search ) ) ? ' AND l.status = %d' : ' AND `status` = %d';
		$params[]                       = $log_status;
		$this->show_filtered_logs_count = true;

		$query_limit .= 'LIMIT %d OFFSET %d';
		$params[]    = $limit;
		$params[]    = $offset;

		$new_query   = $wpdb->prepare( "{$query_select} {$query_from} {$query_where} {$query_order} {$query_limit}", $params ); //phpcs:ignore WordPress.DB.PreparedSQLPlaceholders, WordPress.DB.PreparedSQL
		$active_logs = BWFAN_Model_Logs::get_results( $new_query );
		if ( $this->show_filtered_logs_count ) {
			$logs_count   = 0;
			$query_select = 'SELECT COUNT(`ID`) as `count`';
			if ( true === $search ) {
				$query_select = 'SELECT COUNT(l.ID) as `count`';
			}

			array_pop( $params );
			array_pop( $params );
			$new_query  = $wpdb->prepare( "{$query_select} {$query_from} {$query_where} {$query_order}", $params ); //phpcs:ignore WordPress.DB.PreparedSQLPlaceholders, WordPress.DB.PreparedSQL
			$count_logs = BWFAN_Model_Logs::get_results( $new_query );
			if ( is_array( $count_logs ) && count( $count_logs ) > 0 ) {
				$logs_count = $count_logs[0]['count'];
			}
			$this->filtered_logs_count = $logs_count;
		}

		$result = BWFAN_Core()->tasks->make_data_for_tasks( $active_automations, $active_logs, 'log' );
		$result = $this->make_data_for_log_table( $result );

		return $result;
	}

	/** prepare final data for log table
	 *
	 * @param $rows
	 *
	 * @return array
	 */
	public function make_data_for_log_table( $rows ) {

		if ( ! is_array( $rows ) || count( $rows ) === 0 ) {
			return array();
		}

		$found_posts                = array();
		$found_posts['found_posts'] = BWFAN_Model_Logs::count_rows( null );

		// If logs are filtered, then show the count of filtered data
		if ( BWFAN_Core()->logs->show_filtered_logs_count ) {
			$found_posts['found_posts'] = BWFAN_Core()->logs->filtered_logs_count;
		}

		$items = array();
		$gif   = admin_url() . 'images/wpspin_light.gif';

		BWFAN_Core()->automations->return_all = true;
		$active_automations                   = BWFAN_Core()->automations->get_all_automations();
		BWFAN_Core()->automations->return_all = false;

		foreach ( $rows as $task_id => $task ) {
			$item          = array();
			$automation_id = $task['automation_id'];
			if ( ! isset( $active_automations[ $automation_id ] ) ) {
				continue;
			}

			$source_slug      = isset( $task['meta']['integration_data']['event_data'] ) ? $task['meta']['integration_data']['event_data']['event_source'] : null;
			$event_slug       = isset( $task['meta']['integration_data']['event_data'] ) ? $task['meta']['integration_data']['event_data']['event_slug'] : null;
			$integration_slug = $task['integration_slug'];

			// Event plugin is deactivated, so don't show the automations
			$source_instance = BWFAN_Core()->sources->get_source( $source_slug );

			/**
			 * @var $event_instance BWFAN_Event
			 */
			$event_instance = BWFAN_Core()->sources->get_event( $event_slug );

			$task_details   = isset( $task['meta']['integration_data']['global'] ) ? $task['meta']['integration_data']['global'] : array();
			$message        = ( isset( $task['meta']['task_message'] ) ) ? BWFAN_Common::get_parsed_time( BWFAN_Common::get_date_format(), maybe_unserialize( $task['meta']['task_message'] ) ) : array();
			$status         = $task['status'];
			$automation_url = add_query_arg( array(
				'page' => 'autonami-automations',
				'edit' => $automation_id,
			), admin_url( 'admin.php' ) );

			$action_slug = $task['integration_action'];
			$item        = array(
				'id'                      => $task_id,
				'task_id'                 => $task['meta']['task_id'],
				'automation_id'           => $automation_id,
				'automation_name'         => $task['title'],
				'automation_url'          => $automation_url,
				'automation_source'       => ! is_null( $source_instance ) ? $source_instance->get_name() : __( 'Data unavailable. Contact Support.', 'wp-marketing-automations' ),
				'automation_event'        => ! is_null( $event_instance ) ? $event_instance->get_name() : __( 'Data unavailable. Contact Support.', 'wp-marketing-automations' ),
				'task_integration'        => esc_html__( 'Not Found', 'wp-marketing-automations' ),
				'task_integration_action' => esc_html__( 'Not Found', 'wp-marketing-automations' ),
				'task_date'               => BWFAN_Common::get_human_readable_time( $task['e_date'], get_date_from_gmt( date( 'Y-m-d H:i:s', $task['e_date'] ), BWFAN_Common::get_date_format() ) ),
				'status'                  => $status,
				'gif'                     => $gif,
				'task_message'            => $message,
				'task_details'            => '',
			);
			/**
			 * @var $action_instance BWFAN_Action
			 */
			$action_instance = BWFAN_Core()->integration->get_action( $action_slug );
			if ( $action_instance instanceof BWFAN_Action ) {
				$item['task_integration_action'] = $action_instance->get_name();
			} else {
				$action_name = BWFAN_Common::get_entity_nice_name( 'action', $action_slug );
				if ( ! empty( $action_name ) ) {
					$item['task_integration_action'] = $action_name;
				}
			}

			/**
			 * @var $event_instance BWFAN_Event
			 */
			$integration_instance = BWFAN_Core()->integration->get_integration( $integration_slug );
			if ( $integration_instance instanceof BWFAN_Integration ) {
				$item['task_integration']         = $integration_instance->get_name();
				$task_details['task_integration'] = $integration_instance->get_name();
			} else {
				$integration_name = BWFAN_Common::get_entity_nice_name( 'integration', $integration_slug );
				if ( ! empty( $integration_name ) ) {
					$item['task_integration']         = $integration_name;
					$task_details['task_integration'] = $integration_name;
				}
			}
			$item['task_details']   = ! is_null( $event_instance ) ? $event_instance->get_task_view( $task_details ) : '<b>' . __( 'Data unavailable. Contact Support.', 'wp-marketing-automations' ) . '</b>';
			$item['task_corrupted'] = is_null( $event_instance ) || is_null( $source_instance );
			$items[]                = $item;
		}

		$found_posts['items'] = $items;

		return $found_posts;
	}
}

BWFAN_Core::register( 'logs', 'BWFAN_Logs' );
