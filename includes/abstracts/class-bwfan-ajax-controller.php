<?php

/**
 * Class BWFAN_AJAX_Controller
 * Handles All the request came from front end or the backend
 */
abstract class BWFAN_AJAX_Controller {

	public static function init() {
		/**
		 * Run on front end backend
		 */
		add_action( 'wp_ajax_bwf_update_automation', array( __CLASS__, 'update_automation' ) );
		add_action( 'wp_ajax_bwf_toggle_automation_state', array( __CLASS__, 'toggle_automation_state' ) );
		add_action( 'wp_ajax_bwf_handle_delete_automation', array( __CLASS__, 'handle_delete_automation' ) );
		add_action( 'wp_ajax_bwf_handle_delete_batch_process', array( __CLASS__, 'handle_delete_batch_process' ) );
		add_action( 'wp_ajax_bwf_handle_terminate_batch_process', array( __CLASS__, 'handle_terminate_batch_process' ) );
		add_action( 'wp_ajax_bwf_run_task', array( __CLASS__, 'run_task_from_tasks_screen' ) );
		add_action( 'wp_ajax_bwf_delete_task', array( __CLASS__, 'delete_task_from_tasks_screen' ) );
		add_action( 'wp_ajax_bwf_delete_log', array( __CLASS__, 'delete_log_from_logs_screen' ) );
		add_action( 'wp_ajax_bwf_select2ajax', array( __CLASS__, 'bwfan_select2ajax' ) );
		add_action( 'wp_ajax_bwf_show_email_preview', array( __CLASS__, 'bwfan_save_temporary_preview_data' ) );
		add_action( 'wp_ajax_bwf_global_settings_save', array( __CLASS__, 'handle_bwfan_global_settings_save' ) );
		add_action( 'wp_ajax_bwf_test_email', array( __CLASS__, 'test_email' ) );
		add_action( 'wp_ajax_bwf_test_sms', array( __CLASS__, 'test_sms' ) );
		add_action( 'wp_ajax_bwf_automation_submit', array( __CLASS__, 'handle_automation_post_submit' ) );
		add_action( 'wp_ajax_bwf_run_global_tools', array( __CLASS__, 'handle_bwfan_global_tools' ) );
		add_action( 'wp_ajax_bwf_recipe_dependency_check', array( __CLASS__, 'handle_bwfan_recipe_dependency' ) );

		add_action( 'wp_ajax_bwf_export_single_automation', array( __CLASS__, 'handle_single_automation_export' ) );
		add_action( 'wp_ajax_bwf_duplicate_single_automation', array( __CLASS__, 'handle_duplicate_single_automation' ) );
		add_action( 'wp_ajax_bwf_export_all_automation', array( __CLASS__, 'handle_export_all_automation' ) );
		add_action( 'wp_ajax_bwf_import_automations_json_file', array( __CLASS__, 'handle_import_automations_json_file' ) );

		add_action( 'wp_ajax_bwf_importing_recipe', array( __CLASS__, 'handle_bwfan_recipe_import' ) );
		add_action( 'wp_ajax_bwf_select_syncable_automation', array( __CLASS__, 'select_syncable_automation' ) );
		add_action( 'wp_ajax_bwf_bwfan_api_woofunnels_working', array( __CLASS__, 'handle_woofunnels_working' ) );
		add_action( 'wp_ajax_bwf_bwfan_autonami_working', array( __CLASS__, 'handle_autonami_working' ) );
		add_action( 'wp_ajax_bwf_bwfan_event_working', array( __CLASS__, 'handle_event_working' ) );
		add_action( 'wp_ajax_bwfan_regenerate_tables', array( __CLASS__, 'handle_regenerate_tables' ) );
	}

	public static function bwfan_select2ajax() {
		$callback = apply_filters( 'bwfan_select2_ajax_callable', '', $_POST ); //phpcs:ignore WordPress.Security.NonceVerification
		if ( ! is_callable( $callback ) ) {
			wp_send_json( [] );
		}

		$items = call_user_func( $callback, sanitize_text_field( $_POST['search_term']['term'] ) );//phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		wp_send_json( $items );
	}

	/**
	 * Runs when an automation is saved from single automation screen.
	 * @throws Exception
	 */
	public static function handle_automation_post_submit() {
		BWFAN_Common::check_nonce();
		//phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_POST['automation_id'] ) && empty( $_POST['automation_id'] ) ) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			return;
		}

		$automation_id = $_POST['automation_id']; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$a_track_id    = ( isset( $_POST['a_track_id'] ) && ! empty( $_POST['a_track_id'] ) ) ? $_POST['a_track_id'] : 0; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$t_to_delete   = ( isset( $_POST['t_to_delete'] ) && ! empty( $_POST['t_to_delete'] ) ) ? stripslashes( $_POST['t_to_delete'] ) : null; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		//make sure following is in array just like following
		$data    = stripslashes( $_POST['data'] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$data    = json_decode( $data, true );
		$actions = ( isset( $data['actions'] ) && is_array( $data['actions'] ) ) ? $data['actions'] : [];

		/** Make actions array if not */
		if ( ! array( $actions ) ) {
			$actions = [];
		}

		foreach ( $actions as $group_id => $action_data ) {
			if ( null === $action_data ) {
				continue;
			}
			$actions[ $group_id ] = BWFAN_Common::remove_back_slash_from_automation( $action_data );
		}

		$actions = BWFAN_Common::sort_actions( $actions );

		/** Validate action data before save - unset temp_action_slug as of no use */
		$actions = BWFAN_Common::validate_action_date_before_save( $actions );

		$ui                        = stripslashes( $_POST['ui'] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$ui                        = json_decode( $ui, true );
		$uiData                    = stripslashes( $_POST['uiData'] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$uiData                    = json_decode( $uiData, true );
		$automation_data           = [];
		$automation_data['event']  = $data['trigger']['event'];
		$automation_data['source'] = $data['trigger']['source'];
		$where                     = [];
		$where['ID']               = $automation_id;

		BWFAN_Model_Automations::update( $automation_data, $where );
		BWFAN_Core()->automations->set_automation_data( 'event', $data['trigger']['event'] );
		BWFAN_Core()->automations->set_automation_data( 'source', $data['trigger']['source'] );

		/** Remove active automations transient */
		BWFAN_Core()->automations->remove_automation_transient( $automation_id );

		$automation_meta_data              = [];
		$automation_meta_data['condition'] = [];

		if ( isset( $data['condition'] ) ) {
			$automation_meta_data['condition'] = $data['condition'];
		}
		$automation_meta_data['actions']    = $actions;
		$automation_meta_data['event_meta'] = ( isset( $data['trigger']['event_meta'] ) ) ? $data['trigger']['event_meta'] : [];
		$automation_meta_data['ui']         = $ui;
		$automation_meta_data['uiData']     = $uiData;
		$automation_meta_data['a_track_id'] = $a_track_id;
		$automation_meta                    = BWFAN_Model_Automationmeta::get_automation_meta( $automation_id );
		$db_a_track_id                      = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'a_track_id' );

		/** For saving subject of send email action of automation meta for tracking purpose */
		do_action( 'bwfan_automation_email_tracking_post_data', $automation_id, $automation_meta, $automation_meta_data['actions'], $db_a_track_id );

		/** Update automation meta */
		foreach ( $automation_meta_data as $meta_key => $meta_value ) {
			$meta_value = maybe_serialize( $meta_value );
			BWFAN_Core()->automations->set_automation_data( $meta_key, $meta_value );

			$where       = [];
			$update_data = [
				'bwfan_automation_id' => $automation_id,
				'meta_key'            => $meta_key,
				'meta_value'          => $meta_value,
			];
			if ( array_key_exists( $meta_key, $automation_meta ) ) {
				// Update Meta
				$where['bwfan_automation_id'] = $automation_id;
				$where['meta_key']            = $meta_key;
			}
			if ( count( $where ) > 0 ) {
				BWFAN_Model_Automationmeta::update( $update_data, $where );
			} else {
				BWFAN_Model_Automationmeta::insert( $update_data );
			}
		}

		/** Update the modified date of automation */
		$meta_data = array(
			'meta_value' => current_time( 'mysql', 1 ),
		);
		$where     = array(
			'bwfan_automation_id' => $automation_id,
			'meta_key'            => 'm_date',
		);
		BWFAN_Model_Automationmeta::update( $meta_data, $where );
		BWFAN_Core()->automations->set_automation_data( 'm_date', $meta_data['meta_value'] );
		BWFAN_Core()->automations->set_automation_data( 'run_count', isset( $automation_meta['run_count'] ) ? $automation_meta['run_count'] : 0 );

		// Update requires_update key to 0 on update which implies that user has verified and saved the automation
		$meta_data = array(
			'meta_value' => 0,
		);
		$where     = array(
			'bwfan_automation_id' => $automation_id,
			'meta_key'            => 'requires_update',
		);

		BWFAN_Model_Automationmeta::update( $meta_data, $where );
		BWFAN_Core()->automations->set_automation_data( 'requires_update', 0 );
		BWFAN_Core()->automations->set_automation_id( $automation_id );
		do_action( 'bwfan_automation_saved', $automation_id );

		// Send async call to delete all the tasks except for completed tasks (actually logs)
		if ( ! is_null( $t_to_delete ) ) {
			$url       = rest_url( '/autonami/v1/delete-tasks' );
			$body_data = array(
				'automation_id' => $automation_id,
				'a_track_id'    => $db_a_track_id,
				't_to_delete'   => $t_to_delete,
				'unique_key'    => get_option( 'bwfan_u_key', false ),
			);
			$args      = bwf_get_remote_rest_args( $body_data );
			wp_remote_post( $url, $args );
		}

		$resp = array(
			'id'     => $automation_id,
			'status' => true,
			'msg'    => __( 'Automation Updated', 'wp-marketing-automations' ),
		);
		wp_send_json( $resp );

		//phpcs:enable WordPress.Security.NonceVerification
	}

	/**
	 * Runs when the title of the automation is updated from single automation screen.
	 */
	public static function update_automation() {
		BWFAN_Common::check_nonce();

		$resp = array(
			'msg'    => 'automation not found',
			'status' => false,
		);
		if ( ! isset( $_POST['automation_id'] ) || empty( $_POST['automation_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			wp_send_json( $resp );
		}

		$automation_id = sanitize_text_field( $_POST['automation_id'] ); //phpcs:ignore WordPress.Security.NonceVerification

		$where = [
			'bwfan_automation_id' => $automation_id,
		];
		$meta  = array();

		$where['meta_key']  = 'm_date';
		$meta['meta_key']   = 'm_date';
		$meta['meta_value'] = current_time( 'mysql', 1 );
		BWFAN_Model_Automationmeta::update( $meta, $where );

		$where['meta_key']  = 'title';
		$meta['meta_key']   = 'title';
		$meta['meta_value'] = sanitize_text_field( stripslashes( $_POST['title'] ) ); //phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
		BWFAN_Model_Automationmeta::update( $meta, $where );

		$resp['msg']             = __( 'Automation Successfully Updated', 'wp-marketing-automations' );
		$resp['status']          = true;
		$resp['automation_name'] = sanitize_text_field( stripslashes( $_POST['title'] ) ); //phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput

		BWFAN_Core()->automations->set_automation_id( $automation_id );
		do_action( 'bwfan_automation_saved', $automation_id );

		wp_send_json( $resp );
	}

	/**
	 * Runs when automation is activated/deactivated
	 */
	public static function toggle_automation_state() {
		BWFAN_Common::check_nonce();
		$resp = array(
			'msg'    => '',
			'status' => true,
		);
		// phpcs:disable WordPress.Security.NonceVerification
		if ( empty( $_POST['id'] ) ) {
			$resp = array(
				'msg'    => 'Automation Id is missing',
				'status' => false,
			);
			wp_send_json( $resp );
		}

		$automation_id        = sanitize_text_field( $_POST['id'] );
		$automation           = array();
		$automation['status'] = 2;
		if ( isset( $_POST['state'] ) && 'true' === $_POST['state'] ) {
			$automation['status'] = 1;
		}

		BWFAN_Core()->automations->toggle_state( $automation_id, $automation );

		//phpcs:enable WordPress.Security.NonceVerification
		wp_send_json( $resp );
	}

	/**
	 * Runs from logs listing screen. Runs when a single log is deleted.
	 */
	public static function delete_log_from_logs_screen() {
		BWFAN_Common::check_nonce();

		$resp = array(
			'msg'    => __( 'Log Deleted Successfully', 'wp-marketing-automations' ),
			'status' => true,
		);

		$log_id = sanitize_text_field( $_POST['id'] );//phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput

		BWFAN_Core()->logs->delete_logs( array( $log_id ) );
		wp_send_json( $resp );
	}

	/**
	 * Runs from tasks listing screen. Runs when a single task is deleted.
	 */
	public static function delete_task_from_tasks_screen() {
		BWFAN_Common::check_nonce();

		$resp = array(
			'msg'    => __( 'Task Deleted Successfully', 'wp-marketing-automations' ),
			'status' => true,
		);

		$task_id = sanitize_text_field( $_POST['id'] );  //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput

		BWFAN_Core()->tasks->delete_tasks( array( $task_id ) );
		wp_send_json( $resp );
	}

	/**
	 * Execute a single task from tasks listing screen.
	 */
	public static function run_task_from_tasks_screen() {
		BWFAN_Common::check_nonce();

		$resp = array(
			'msg'    => __( 'Task Executed Successfully', 'wp-marketing-automations' ),
			'status' => true,
		);

		$task_id = sanitize_text_field( $_POST['id'] );//phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput

		try {
			BWFAN_Core()->tasks->bwfan_ac_execute_task( $task_id );

		} catch ( Exception $exception ) {
			$resp['status'] = false;
			$resp['msg']    = $exception->getMessage();
			wp_send_json( $resp );
		}

		if ( BWFAN_Core()->tasks->ajax_status ) {
			wp_send_json( $resp );
		}

		$resp = array(
			'msg'    => BWFAN_Core()->tasks->ajax_msg,
			'status' => BWFAN_Core()->tasks->ajax_status,
		);

		wp_send_json( $resp );
	}

	/**
	 * Runs when a single automation is deleted from automations listing screen.
	 */
	public static function handle_delete_automation() {
		BWFAN_Common::check_nonce();
		$resp = array(
			'msg'    => '',
			'status' => true,
		);

		$automation_id    = sanitize_text_field( $_POST['id'] ); //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		$automation_ids   = array( $automation_id );
		$event_details    = BWFAN_Model_Automations::get( $automation_id );
		$automation_event = $event_details['event'];
		$event_object     = BWFAN_Core()->sources->get_event( $automation_event );

		if ( ! is_null( $event_object ) && $event_object->is_time_independent() ) {
			wp_delete_post( $automation_id );
		}

		BWFAN_Core()->automations->delete_automation( $automation_ids );
		BWFAN_Core()->automations->delete_automationmeta( $automation_ids );
		BWFAN_Core()->automations->delete_migrations( $automation_id );
		BWFAN_Core()->tasks->delete_tasks( array(), $automation_ids );
		BWFAN_Core()->logs->delete_logs( array(), $automation_ids );
		BWFAN_Core()->automations->set_automation_id( $automation_id );
		do_action( 'bwfan_automation_deleted', $automation_id );

		// Set status of logs to 0, so that run now option for those logs can be hided
		BWFAN_Model_Logs::update( array(
			'status' => 0,
		), array(
			'automation_id' => $automation_id,
		) );
		wp_send_json( $resp );
	}

	/**
	 * Runs when a single automation is deleted from automations listing screen.
	 */
	public static function handle_delete_batch_process() {
		BWFAN_Common::check_nonce();
		$resp = array(
			'msg'    => '',
			'status' => true,
		);

		$sync_id     = absint( sanitize_text_field( $_POST['id'] ) );
		$sync_record = BWFAN_Model_Syncrecords::get( $sync_id );
		$delete_sync = array( 2, 3 );

		if ( 2 !== absint( $sync_record['status'] ) ) {
			wp_send_json( array(
				'msg'    => 'This Batch process is not completed yet.',
				'status' => false,
			) );
		}

		if ( is_array( $sync_record ) && empty( $sync_record['sync_data'] ) ) {
			wp_send_json( array(
				'msg'    => '',
				'status' => false,
			) );
		}

		$sync_data = json_decode( $sync_record['sync_data'], true );
		if ( isset( $sync_data['automation_event'] ) && ! empty( $sync_data['automation_event'] ) ) {
			$hook = 'bwfan_process_old_records_for_' . $sync_data['automation_event'];
			if ( bwf_has_action_scheduled( $hook ) ) {
				bwf_unschedule_actions( $hook );
			}
		}

		BWFAN_Model_Syncrecords::delete( $sync_id );

		do_action( 'bwfan_batch_process_deleted', $sync_id );

		wp_send_json( $resp );
	}

	/**
	 * Runs when a single automation is terminate from automations listing screen.
	 */
	public static function handle_terminate_batch_process() {
		BWFAN_Common::check_nonce();
		$resp = array(
			'msg'    => '',
			'status' => true,
		);

		$sync_id     = absint( sanitize_text_field( $_POST['id'] ) );
		$sync_record = BWFAN_Model_Syncrecords::get( $sync_id );

		if ( 1 !== absint( $sync_record['status'] ) ) {
			wp_send_json( array(
				'msg'    => 'This Batch process is not in running state.',
				'status' => false,
			) );
		}

		if ( is_array( $sync_record ) && empty( $sync_record['sync_data'] ) ) {
			wp_send_json( array(
				'msg'    => '',
				'status' => false,
			) );
		}

		$sync_data = json_decode( $sync_record['sync_data'], true );
		if ( isset( $sync_data['automation_event'] ) && ! empty( $sync_data['automation_event'] ) ) {
			$hook = 'bwfan_process_old_records_for_' . $sync_data['automation_event'];
			if ( bwf_has_action_scheduled( $hook ) ) {
				bwf_unschedule_actions( $hook );
			}
		}

		$where = array(
			'ID' => $sync_id,
		);

		$data = array(
			'status' => 3,
		);
		BWFAN_Model_Syncrecords::update( $data, $where );

		do_action( 'bwfan_batch_process_terminated', $sync_id );

		wp_send_json( $resp );
	}

	public static function is_wfocu_front_ajax() {

		if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX && null !== filter_input( INPUT_POST, 'action' ) && false !== strpos( filter_input( INPUT_POST, 'action' ), 'wfocu_front' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Runs when `preview` option is clicked in email action. It temporarily saved data in options table.
	 */
	public static function bwfan_save_temporary_preview_data() {
		BWFAN_Common::check_nonce();

		//phpcs:disable WordPress.Security.NonceVerification
		$automation_id = sanitize_text_field( $_POST['automation_id'] );

		if ( absint( $automation_id ) < 1 ) {
			wp_send_json( array(
				'status' => false,
			) );
		}

		$post                     = $_POST;
		$post['data']['to']       = stripslashes( sanitize_text_field( $post['data']['to'] ) );
		$post['data']['subject']  = stripslashes( sanitize_text_field( $post['data']['subject'] ) );
		$post['data']['body']     = stripslashes( $post['data']['body'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$post['data']['body_raw'] = stripslashes( $post['data']['body_raw'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		$meta               = array();
		$meta['meta_key']   = 'email_preview';
		$meta['meta_value'] = maybe_serialize( $post );

		$current_data = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'email_preview' );
		if ( false === $current_data ) {
			$meta['bwfan_automation_id'] = $automation_id;
			BWFAN_Model_Automationmeta::insert( $meta );
		} else {
			$where = [
				'bwfan_automation_id' => $automation_id,
				'meta_key'            => 'email_preview',
			];
			BWFAN_Model_Automationmeta::update( $meta, $where );
		}

		//phpcs:enable WordPress.Security.NonceVerification
		wp_send_json( array(
			'status' => true,
		) );
	}

	/**
	 * Save global settings of the plugin into options table.
	 */
	public static function handle_bwfan_global_settings_save() {
		BWFAN_Common::check_nonce();
		// phpcs:disable WordPress.Security.NonceVerification
		unset( $_POST['action'] );
		unset( $_POST['_wpnonce'] );
		$settings_to_save = [];

		foreach ( $_POST as $setting_key => $setting_value ) {
			if ( is_array( $setting_value ) ) {
				$setting_value = array_map( 'sanitize_text_field', $setting_value );
			}
			$settings_to_save[ $setting_key ] = is_array( $setting_value ) ? $setting_value : stripslashes( $setting_value );
		}

		if ( ! isset( $_POST['bwfan_make_logs'] ) ) {
			$settings_to_save['bwfan_make_logs'] = 0;
		}

		$global_settings = BWFAN_Common::get_global_settings();

		if ( isset( $_POST['bwfan_unsubscribe_page'] ) && absint( $_POST['bwfan_unsubscribe_page'] ) > 0 ) {
			$settings_to_save['bwfan_unsubscribe_page'] = $_POST['bwfan_unsubscribe_page'];
		} elseif ( isset( $global_settings['bwfan_unsubscribe_page'] ) && absint( $global_settings['bwfan_unsubscribe_page'] ) > 0 ) {
			$settings_to_save['bwfan_unsubscribe_page'] = $global_settings['bwfan_unsubscribe_page'];
		}

		//phpcs:enable WordPress.Security.NonceVerification
		update_option( 'bwfan_global_settings', $settings_to_save );
		wp_send_json( array(
			'status' => true,
		) );
	}

	/**
	 * Runs when global tools operations are performed from global settings screen.
	 */
	public static function handle_bwfan_global_tools() {
		BWFAN_Common::check_nonce();
		$result = array(
			'status' => true,
		);
		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_POST['tool_type'] ) ) {
			wp_send_json( array(
				'status' => false,
			) );
		}

		$result = BWFAN_Common::run_global_tools( $_POST['tool_type'] );

		if ( ! isset( $result['status'] ) || false !== $result['status'] ) {
			$result['status'] = true;
		}

		//phpcs:enable WordPress.Security.NonceVerification
		wp_send_json( $result );
	}

	public static function select_syncable_automation() {
		if ( ! isset( $_POST['id'] ) || empty( sanitize_text_field( $_POST['id'] ) ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			wp_send_json( array(
				'success'  => 0,
				'response' => __( 'Security check failed', 'wp-marketing-automations' ),
			) );
		}

		global $wpdb;
		$automation_id      = absint( sanitize_text_field( $_POST['id'] ) ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		$query              = $wpdb->prepare( 'Select ID from {table_name} WHERE a_id = %d AND status = %d', $automation_id, 1 ); // WPCS: unprepared SQL OK
		$active_automations = BWFAN_Model_Syncrecords::get_results( $query );
		if ( is_array( $active_automations ) && count( $active_automations ) > 0 ) {
			wp_send_json( array(
				'success'  => 0,
				'response' => __( 'Sync is in process for this automation, cannot initialise another sync.', 'wp-marketing-automations' ),
			) );
		}

		$automations = $wpdb->get_results( $wpdb->prepare( "
                SELECT `event`
                FROM {$wpdb->prefix}bwfan_automations
                WHERE `status` != %d
                AND `ID` = %d
                ", 2, $automation_id ), ARRAY_A );

		if ( empty( $automations ) ) {
			wp_send_json( array(
				'success'  => 0,
				'response' => __( 'No event found for the automation.', 'wp-marketing-automations' ),
			) );
		}

		$event_slug           = $automations[0]['event'];
		$current_event_object = BWFAN_Core()->sources->get_event( $event_slug );

		BWFAN_Core()->automations->automation_id = $automation_id;

		ob_start();
		$current_event_object->get_sync_view();
		$response = ob_get_clean();

		wp_send_json( array(
			'success'  => 1,
			'response' => $response,
		) );
	}

	public static function test_email() {
		BWFAN_Common::check_nonce();
		// phpcs:disable WordPress.Security.NonceVerification
		$result = array(
			'status' => false,
			'msg'    => __( 'Error', 'wp-marketing-automations' ),
		);

		if ( ! isset( $_POST['email'] ) || ! filter_var( $_POST['email'], FILTER_VALIDATE_EMAIL ) ) {
			$result['msg'] = __( 'Email not valid', 'wp-marketing-automations' );
			wp_send_json( $result );
		}

		$post = $_POST;

		$automation_id = sanitize_text_field( $post['automation_id'] );
		if ( absint( $automation_id ) < 1 ) {
			$result['msg']    = __( 'Automation ID missing', 'wp-marketing-automations' );
			$result['status'] = false;
			wp_send_json( $result );
		}

		$post['data']['to']         = sanitize_email( $post['email'] );
		$post['data']['subject']    = isset( $post['data']['subject'] ) && ! empty( $post['data']['subject'] ) ? stripslashes( $post['data']['subject'] ) : __( 'This is a fake subject line, enter subject to fix it', 'wp-marketing-automations' );
		$post['data']['preheading'] = isset( $post['data']['preheading'] ) ? stripslashes( $post['data']['preheading'] ) : '';
		$post['data']['body']       = stripslashes( $post['data']['body'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$post['data']['body_raw']   = stripslashes( $post['data']['body_raw'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( isset( $post['data']['editor'] ) ) {
			$post['data']['editor']['body']   = stripslashes( $post['data']['editor']['body'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$post['data']['editor']['design'] = stripslashes( $post['data']['editor']['design'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}

		$meta               = array();
		$meta['meta_key']   = 'email_preview';
		$meta['meta_value'] = maybe_serialize( $post );

		$current_data = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'email_preview' );
		if ( false === $current_data ) {
			$meta['bwfan_automation_id'] = $automation_id;
			BWFAN_Model_Automationmeta::insert( $meta );
		} else {
			$where = [
				'bwfan_automation_id' => $automation_id,
				'meta_key'            => 'email_preview',
			];
			BWFAN_Model_Automationmeta::update( $meta, $where );
		}

		BWFAN_Merge_Tag_Loader::set_data( array(
			'is_preview' => true,
			'test_email' => $post['email'],
		) );

		$post['event_data']['event_slug'] = $post['event'];
		$action_object                    = BWFAN_Core()->integration->get_action( 'wp_sendemail' );
		$action_object->is_preview        = true;
		$data_to_set                      = $action_object->make_data( '', $post );
		$data_to_set['test']              = true;

		$action_object->set_data( $data_to_set );
		$response = $action_object->send_email();

		if ( true === $response ) {
			$result['msg']    = __( 'Test email sent.', 'wp-marketing-automations' );
			$result['status'] = true;
		} elseif ( is_array( $response ) && isset( $response['message'] ) ) {
			$result['msg']    = $response['message'];
			$result['status'] = false;
		} else {
			$result['msg']    = __( 'Server does not support email facility', 'wp-marketing-automations' );
			$result['status'] = false;

		}
		//phpcs:enable WordPress.Security.NonceVerification
		wp_send_json( $result );
	}


	public static function test_sms() {
		BWFAN_Common::check_nonce();
		// phpcs:disable WordPress.Security.NonceVerification
		$result = array(
			'status' => false,
			'msg'    => __( 'Error', 'wp-marketing-automations' ),
		);
		if ( ! isset( $_POST['data']['sms_to'] ) ) {
			$result['msg'] = __( 'Phone number can\'t be blank', 'wp-marketing-automations' );
			wp_send_json( $result );
		}

		$post                 = $_POST;
		$post['data']['to']   = sanitize_email( $post['data']['sms_to'] );
		$post['data']['body'] = isset( $post['data']['sms_body'] ) ? stripslashes( $post['data']['sms_body'] ) : '';

		$post['event_data']['event_slug'] = $post['event'];

		$action_object       = BWFAN_Core()->integration->get_action( 'twilio_send_sms' );
		$data_to_set         = $action_object->make_data( '', $post );
		$data_to_set['test'] = true;

		/** @var  $global_settings */
		$global_settings = WFCO_Common::$connectors_saved_data;
		if ( ! array_key_exists( 'bwfco_twilio', $global_settings ) ) {
			wp_send_json( array(
				'msg'    => __( 'Twilio is not connected', 'wp-marketing-automations' ),
				'status' => false,
			) );
		}

		$twilio_settings = $global_settings['bwfco_twilio'];

		$load_connector = WFCO_Load_Connectors::get_instance();
		$call_class     = $load_connector->get_call( 'wfco_twilio_send_sms' );

		$data_to_set['account_sid'] = $twilio_settings['account_sid'];
		$data_to_set['auth_token']  = $twilio_settings['auth_token'];
		$data_to_set['twilio_no']   = $twilio_settings['twilio_no'];

		/** Media handling */
		if ( isset( $post['data']['attach_custom_img'] ) && ! empty( $post['data']['attach_custom_img'] ) ) {
			$img = stripslashes( $post['data']['attach_custom_img'] );
			$img = json_decode( $img, true );
			if ( is_array( $img ) && count( $img ) > 0 ) {
				$data_to_set['mediaUrl'] = $img[0];
			}
		}

		// is_preview set to true for merge tag before sending data for sms;
		BWFAN_Merge_Tag_Loader::set_data( array(
			'is_preview' => true,
		) );
		$call_class->set_data( $data_to_set );

		$response = $call_class->process();

		if ( is_array( $response ) && 200 === $response['response'] && is_null( $response['body']['error_message'] ) ) {

			wp_send_json( array(
				'status' => true,
				'msg'    => __( 'SMS sent successfully.', 'wp-marketing-automations' ),
			) );
		}

		$message = __( 'SMS could not be sent', 'wp-marketing-automations' );
		$status  = false;

		if ( isset( $response['body']['errors'] ) && isset( $response['body']['errors'][0] ) && isset( $response['body']['errors'][0]['message'] ) ) {
			$message = $response['body']['errors'][0]['message'];
		} elseif ( isset( $response['body']['message'] ) ) {
			$message = $response['body']['message'];
		} elseif ( isset( $response['body']['error_message'] ) ) {
			$status  = false;
			$message = $response['body']['error_message'];
		} elseif ( isset( $response['bwfan_response'] ) && ! empty( $response['bwfan_response'] ) ) {
			$message = $response['bwfan_response'];
		}


		wp_send_json( array(
			'status' => $status,
			'msg'    => $message,
		) );
	}

	/**
	 * Ajax
	 * Recipe dependency check
	 *
	 */
	public static function handle_bwfan_recipe_dependency() {
		$resp = array(
			'status' => false,
			'msg'    => __( 'Error', 'wp-marketing-automations' ),
		);

		$recipe_slug = isset( $_POST['slug'] ) && ! empty( $_POST['slug'] ) ? sanitize_text_field( $_POST['slug'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification
		if ( empty( $recipe_slug ) ) {
			$resp['msg'] = __( 'Technical error!', 'wp-marketing-automations' );
			wp_send_json( $resp );
		}

		/** get instance of slug */
		$recipe_instance = $recipe_slug::get_instance();

		/** checking dependency */
		$check_dependency = $recipe_instance->check_dependency();
		if ( true !== $check_dependency && is_array( $check_dependency ) ) {
			$resp['error_data'] = array_filter( $check_dependency );
		} else {
			$resp['status'] = true;
			$resp['msg']    = __( 'Dependencies check successful', 'autonami-automation' );
		}

		wp_send_json( $resp );
	}

	/**
	 * Ajax
	 * Recipe import
	 *
	 */
	public static function handle_bwfan_recipe_import() {
		$resp = array(
			'status' => false,
			'msg'    => __( 'Something went wrong while importing Recipe. Please contact support.', 'wp-marketing-automations' ),
		);

		$recipe_slug = isset( $_POST['slug'] ) && ! empty( $_POST['slug'] ) ? sanitize_text_field( $_POST['slug'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification
		if ( empty( $recipe_slug ) ) {
			wp_send_json( $resp );
		}

		/** get instance of slug */
		$recipe_instance = $recipe_slug::get_instance();
		$recipe_json     = array_filter( $recipe_instance->data['json'] );

		/** checking json files */
		if ( empty( $recipe_json ) ) {
			wp_send_json( $resp );
		}

		/** Creating automation from recipe */
		$resp = $recipe_instance->create_automation( $recipe_json );

		wp_send_json( $resp );
	}

	/*
	 *  ajax to export single automation json file on click
	 */
	public static function handle_single_automation_export() {
		$automation_id = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification
		if ( empty( $automation_id ) ) {
			return;
		}

		echo esc_url_raw( admin_url() . 'admin.php?page=autonami-automations&action=export&data=1&automation=' . $automation_id );

		wp_die();
	}


	/*
	 *  ajax to duplicate single automation on click
	 */
	public static function handle_duplicate_single_automation() {
		$automation_id = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification

		if ( empty( $automation_id ) ) {
			return;
		}

		$automation_id = BWFAN_Core()->automations->duplicate( $automation_id );

		if ( empty( $automation_id ) ) {
			wp_send_json( [ 'status' => 0 ] );
		}

		wp_send_json( [ 'status' => 1, 'url' => admin_url( 'admin.php?page=autonami-automations' ) ] );
	}

	/**
	 *  export all automation to the json file
	 */
	public static function handle_export_all_automation() {
		echo esc_url_raw( admin_url() . 'admin.php?page=autonami-automations&action=export&data=1&automation=all' );

		wp_die();
	}

	/**
	 *  import automation by submit json file
	 */
	public static function handle_import_automations_json_file() {
		$import_file = isset( $_FILES['file'] ) && ! empty( $_FILES['file'] ) ? $_FILES['file'] : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		//check if the import file exists
		if ( empty( $import_file['name'] ) ) {
			return 2;
		}
		$import_file_data = json_decode( file_get_contents( $import_file['tmp_name'] ), true );

		// check whether the json file contain data or not

		if ( empty( $import_file_data ) && ! is_array( $import_file_data ) ) {
			return 2;
		}

		BWFAN_Core()->automations->import( $import_file_data );

		echo 1;
		die();
	}

	public static function handle_woofunnels_working() {
		BWFAN_Common::check_nonce();
		$url               = rest_url( '/woofunnels/v1/worker' ) . '?' . time();
		$args              = [ 'method' => 'GET', 'sslverify' => false, ];
		$woofunnels_worker = wp_remote_post( $url, $args );
		$timing            = isset( $_POST['time'] ) ? $_POST['time'] : '';

		if ( $woofunnels_worker instanceof WP_Error ) {
			wp_send_json( array( 'status' => false, 'msg' => $woofunnels_worker->get_error_message() ) );
		}

		if ( isset( $woofunnels_worker['response'] ) && 200 === absint( $woofunnels_worker['response']['code'] ) ) {
			$body = ! empty( $woofunnels_worker['body'] ) ? json_decode( $woofunnels_worker['body'], true ) : '';
			$time = isset( $body['time'] ) ? strtotime( $body['time'] ) : time();
			if ( ! empty( $timing ) && $time > $timing ) {
				wp_send_json( array( 'status' => true, 'time' => $time, 'cached' => false ) );
			}
			wp_send_json( array( 'status' => true, 'time' => $time, 'cached' => true ) );
		}

		$message = 'Not working';
		wp_send_json( array( 'status' => false, 'msg' => $message ) );
	}

	public static function handle_autonami_working() {
		BWFAN_Common::check_nonce();
		$url             = rest_url( 'autonami/v1/worker' ) . '?' . time();
		$body_data       = array(
			'worker'     => true,
			'unique_key' => get_option( 'bwfan_u_key', false ),
		);
		$timing          = isset( $_POST['time'] ) ? $_POST['time'] : '';
		$args1           = bwf_get_remote_rest_args( $body_data );
		$autonami_worker = wp_remote_post( $url, $args1 );

		if ( $autonami_worker instanceof WP_Error ) {
			wp_send_json( array( 'status' => false, 'msg' => $autonami_worker->get_error_message() ) );
		}

		if ( isset( $autonami_worker['response'] ) && 200 === absint( $autonami_worker['response']['code'] ) ) {
			$body = ! empty( $woofunnels_worker['body'] ) ? json_decode( $woofunnels_worker['body'], true ) : '';
			$time = isset( $body['time'] ) ? strtotime( $body['time'] ) : time();
			if ( ! empty( $timing ) && $time > $timing ) {
				wp_send_json( array( 'status' => true, 'time' => $time, 'cached' => false ) );
			}
			wp_send_json( array( 'status' => true, 'time' => $time, 'cached' => true ) );
		}

		$message = 'Not working';
		wp_send_json( array( 'status' => false, 'msg' => $message ) );
	}

	public static function handle_event_working() {
		BWFAN_Common::check_nonce();
		$url                   = rest_url( 'autonami/v1/events' );
		$body_data             = array(
			'worker'     => true,
			'unique_key' => get_option( 'bwfan_u_key', false ),
		);
		$args2                 = bwf_get_remote_rest_args( $body_data );
		$autonami_event_worker = wp_remote_post( $url, $args2 );
		$timing                = isset( $_POST['time'] ) ? $_POST['time'] : '';
		if ( $autonami_event_worker instanceof WP_Error ) {
			wp_send_json( array( 'status' => false, 'msg' => $autonami_event_worker->get_error_message() ) );
		}

		if ( isset( $autonami_event_worker['response'] ) && 200 === absint( $autonami_event_worker['response']['code'] ) ) {
			$body = ! empty( $autonami_event_worker['body'] ) ? json_decode( $autonami_event_worker['body'], true ) : '';
			$time = isset( $body['time'] ) ? $body['time'] : time();
			if ( ! empty( $timing ) && $time > $timing ) {
				wp_send_json( array( 'status' => true, 'time' => $time, 'cached' => false ) );
			}
			wp_send_json( array( 'status' => true, 'time' => $time, 'cached' => true ) );
		}

		$message = 'Not working';
		wp_send_json( array( 'status' => false, 'msg' => $message ) );
	}

	/**
	 *  update the db option to previous one
	 */
	public static function handle_regenerate_tables() {

		delete_option( 'bwfan_ver_1_0' );
		update_option( 'bwfan_pro_db', '1.2.2' );
		wp_send_json( array( 'status' => true ) );
	}

}

BWFAN_AJAX_Controller::init();
