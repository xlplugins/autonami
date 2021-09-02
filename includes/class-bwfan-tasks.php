<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BWFAN_Tasks {
	public static $exec_tasks = [];
	private static $ins = null;
	public $ajax_msg = '';
	public $ajax_status = true;
	public $ajax_redirect = false;
	public $show_filtered_tasks_count = false;
	public $filtered_tasks_count = 0;
	public $log_type = 'task_triggered';
	private $task_id = null;
	private $automation_id = null;

	public function __construct() {
		add_action( 'bwfan_execute_task', array( $this, 'bwfan_ac_execute_task' ), 10, 1 );
		add_filter( 'bwfan_pre_insert_task', array( $this, 'modify_task_details' ), 10, 3 );
		add_filter( 'bwfan_pre_insert_task_db', array( $this, 'modify_task_details_for_syncing' ), 10, 4 );
		add_action( 'bwfan_rate_limit_reached', array( $this, 'restrict_tasks' ), 10, 2 );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Execute all queued tasks of an automation
	 *
	 * @param $automation_id
	 */
	public function execute_pending_tasks( $automation_id ) {
		$tasks = BWFAN_Model_Tasks::get_tasks( array( $automation_id ) );
		if ( is_array( $tasks ) && count( $tasks ) > 0 ) {
			$task_ids = [];
			foreach ( $tasks as $task_details ) {
				$task_ids[] = $task_details['ID'];
			}
			$this->rescheduled_tasks( true, $task_ids );
		}
	}

	/**
	 * Reschedule tasks to run at user specified time when user bulk execute tasks
	 *
	 * @param $now
	 * @param $task_ids
	 */
	public function rescheduled_tasks( $now, $task_ids ) {
		if ( $now ) {
			global $wpdb;
			$tasks_count          = count( $task_ids );
			$prepare_placeholders = array_fill( 0, $tasks_count, '%s' );
			$prepare_placeholders = implode( ', ', $prepare_placeholders );
			$sql_query            = "UPDATE {table_name} SET e_date=%d WHERE ID IN ($prepare_placeholders)";
			$sql_query            = $wpdb->prepare( $sql_query, array_merge( array( time() ), $task_ids ) ); // WPCS: unprepared SQL OK
			BWFAN_Model_Tasks::query( $sql_query );
		}
	}

	/**
	 * Execute a single task which is triggered by action scheduler
	 *
	 * @param $task_id
	 *
	 */
	public function bwfan_ac_execute_task( $task_id ) {
		try {
			/** Check if Autonami is in sandbox mode */
			$global_settings = BWFAN_Common::get_global_settings();
			if ( 1 === intval( $global_settings['bwfan_sandbox_mode'] ) || ( defined( 'BWFAN_SANDBOX_MODE' ) && true === BWFAN_SANDBOX_MODE ) ) {
				return;
			}

			/** Fetch single task details */
			$task_details = BWFAN_Model_Tasks::get_task_with_data( $task_id );
			if ( ! is_array( $task_details ) || 0 === count( $task_details ) || empty( $task_details['meta'] ) ) {
				$msg = 'Task deleted from table. Task ID - ' . $task_id;
				BWFAN_Core()->logger->log( $msg, $this->log_type );

				return;
			}

			if ( ! isset( $task_details['meta']['integration_data'] ) || ! is_array( $task_details['meta']['integration_data'] ) || empty( $task_details['meta']['integration_data'] ) ) {
				$task_action_data = array(
					'task_id'          => $task_details['ID'],
					'integration_slug' => $task_details['integration_slug'],
					'action_slug'      => $task_details['integration_action'],
					'task_status'      => $task_details['status'],
					'task_attempt'     => $task_details['attempts'],
					'status'           => 0,
				);
				$this->move_single_task_to_log( $task_id, $task_details['automation_id'], $task_action_data );

				$this->ajax_status = false;
				$this->ajax_msg    = __( 'Data unavailable. Contact Support.', 'wp-marketing-automations' );

				return;
			}

			$this->automation_id = $task_details['automation_id'];
			$this->task_id       = $task_id;

			/** Validate required event data before task execution */
			/** @var BWFAN_Event $event_instance */
			$event_instance = BWFAN_Core()->sources->get_event( $task_details['meta']['integration_data']['event_data']['event_slug'] );
			$validate       = $event_instance->validate_event_data_before_executing_task( $task_details['meta']['integration_data']['global'] );

			if ( false === $validate ) {
				BWFAN_Core()->logger->log( 'Event data validation failed. Deleting Task... Task ID - ' . $task_id . ', Automation ID- ' . $task_details['automation_id'], $this->log_type );

				/** move task to log and marked as failed as required data is missing **/

				$message = __( 'Required data missing, event validation failed.', 'wp-marketing-automations' );
				if ( ! empty( $event_instance->message_validate_event ) ) {
					$message = $event_instance->message_validate_event;

					$event_instance->message_validate_event = null;
				}

				$task_to_delete = [
					$task_id => [
						'message'       => $message,
						'automation_id' => $task_details['automation_id'],
						'details'       => [
							'status'           => 4,
							'integration_slug' => $task_details['integration_slug'],
							'action_slug'      => $task_details['integration_action'],
						],
					],
				];

				$this->move_task_to_log( $task_to_delete );

				return null;
			}

			$action_slug      = $task_details['integration_action'];
			$action_class_ins = BWFAN_Core()->integration->get_action( $action_slug );

			/** Run hooks before executing task for Action */
			$action_class_ins->before_executing_task();

			/** Validating attempts if over then move to logs */
			$attempt_count = ( intval( $task_details['attempts'] ) > 0 ) ? intval( $task_details['attempts'] ) : 0;
			$attempt_limit = $this->get_task_retry_data();
			if ( count( $attempt_limit ) <= $attempt_count ) {
				/** shouldn't run, move to logs */
				$task_action_data = array(
					'task_id'          => $task_details['ID'],
					'integration_slug' => $task_details['integration_slug'],
					'action_slug'      => $task_details['integration_action'],
					'task_status'      => $task_details['status'],
					'task_attempt'     => $task_details['attempts'],
					'status'           => 0,
				);
				$this->move_single_task_to_log( $task_id, $task_details['automation_id'], $task_action_data );

				$this->ajax_status = false;
				$this->ajax_msg    = __( 'Attempts limit reached', 'wp-marketing-automations' );

				/** Remove generic hooks after executing task for Action */
				$action_class_ins->after_executing_task();

				return;
			}

			/**
			 * Add automation and track id in Abandoned restore link
			 */
			add_filter( 'bwfan_abandoned_cart_restore_link', array( $this, 'add_automation_id_track_link_in_restore_url' ) );

			// Prepare and get the actual data for the action which is stored as a task in db table.
			$task_action_data = BWFAN_Core()->tasks->prepare_data_for_executable_tasks( $task_details );
			if ( is_array( $task_action_data ) && count( $task_action_data ) > 0 ) {
				$this->execute_tasks( $task_action_data, $task_details );
			} else {
				$this->ajax_msg    = __( 'Connector settings has been deleted for this task', 'wp-marketing-automations' );
				$this->ajax_status = false;
				BWFAN_Core()->logger->log( 'Connector settings has been deleted for the task. Task ID - ' . $task_id . ', Automation ID- ' . $task_details['automation_id'], $this->log_type );
			}
		} catch ( Error $e ) {
			throw new Exception( "Error occurred with message {$e->getMessage()} for task id {$task_id}", 1 );
		}
	}

	/**
	 * Delete tasks and tasks meta from DB.
	 *
	 * @param array $task_ids
	 * @param array $automation_ids
	 */
	public function delete_tasks( $task_ids = array(), $automation_ids = array() ) {
		global $wpdb;
		if ( is_array( $automation_ids ) && count( $automation_ids ) > 0 ) {
			$automation_count     = count( $automation_ids );
			$prepare_placeholders = array_fill( 0, $automation_count, '%s' );
			$prepare_placeholders = implode( ', ', $prepare_placeholders );
			$sql_query            = "Select ID FROM {table_name} WHERE automation_id IN ($prepare_placeholders)";
			$sql_query            = $wpdb->prepare( $sql_query, $automation_ids ); // WPCS: unprepared SQL OK
			$tasks                = BWFAN_Model_Tasks::get_results( $sql_query );

			if ( is_array( $tasks ) && count( $tasks ) > 0 ) {
				$task_ids = array();
				foreach ( $tasks as $task ) {
					$task_ids[] = $task['ID'];
				}
			}
		}

		if ( is_array( $task_ids ) && count( $task_ids ) > 0 ) {
			/** Delete Tasks */
			$automation_count     = count( $task_ids );
			$prepare_placeholders = array_fill( 0, $automation_count, '%s' );
			$prepare_placeholders = implode( ', ', $prepare_placeholders );
			$sql_query            = "Delete FROM {table_name} WHERE ID IN ($prepare_placeholders)";
			$sql_query            = $wpdb->prepare( $sql_query, $task_ids ); // WPCS: unprepared SQL OK

			BWFAN_Model_Tasks::query( $sql_query );

			/** Delete Tasks Meta */
			$sql_query = "Delete FROM {table_name} WHERE bwfan_task_id IN ($prepare_placeholders)";
			$sql_query = $wpdb->prepare( $sql_query, $task_ids ); // WPCS: unprepared SQL OK

			BWFAN_Model_Taskmeta::query( $sql_query );
		}
	}

	/**
	 * Get task re-attempt limit data
	 * @return mixed|void
	 */
	public function get_task_retry_data() {
		return apply_filters( 'bwfan_change_tasks_retry_limit', array(
			HOUR_IN_SECONDS, // 1 hr
			6 * HOUR_IN_SECONDS, // 6 hrs
			18 * HOUR_IN_SECONDS, // 18 hrs
		) );
	}

	/**
	 * Prepare the data from executable tasks and execute tasks
	 *
	 * @param $task
	 *
	 * @return array
	 */
	public function prepare_data_for_executable_tasks( $task ) {
		WFCO_Common::get_connectors_data();
		$global_settings                   = WFCO_Common::$connectors_saved_data;
		$task_id                           = $task['ID'];
		$automation_id                     = $task['automation_id'];
		$integration_slug                  = $task['integration_slug'];
		$action_name                       = $task['integration_action'];
		$integration_data                  = $task['meta']['integration_data'];
		$integration_data['automation_id'] = $automation_id;
		$event_slug                        = $integration_data['event_data']['event_slug'];

		/**
		 * @var $event_instance BWFAN_Event;
		 * @var $integration_slug BWFAN_integration;
		 * @var $action_instance BWFAN_Action;
		 */
		$integration_instance = BWFAN_Core()->integration->get_integration( $integration_slug );
		$action_instance      = BWFAN_Core()->integration->get_action( $action_name );
		$event_instance       = BWFAN_Core()->sources->get_event( $event_slug );

		/** Reset 'data set' during task action execution */
		$action_instance->reset_data();
		BWFAN_Merge_Tag_Loader::reset_data();

		if ( is_null( $integration_instance ) || is_null( $action_instance ) || is_null( $event_instance ) ) {
			/**
			 * Integration, Action, Event instanced not found
			 * Hence delete the task
			 */
			$task_to_delete = [
				$task_id => [
					'message'       => __( 'Task Event, Action or Integration not found', 'wp-marketing-automations' ),
					'automation_id' => $automation_id,
					'details'       => [
						'status'           => 4,
						'integration_slug' => $integration_slug,
						'action_slug'      => $action_name,
					],
				],
			];
			$this->move_task_to_log( $task_to_delete );
			BWFAN_Core()->logger->log( 'action or event not found - , Task ID - ' . $task['ID'], $this->log_type );

			return null;
		}

		$need_connector = $integration_instance->need_connector();
		if ( $need_connector ) {
			$option_key = $integration_instance->get_connector_slug();
			if ( '' !== $option_key && isset( $global_settings[ $option_key ] ) ) {
				$integration_instance->set_settings( $global_settings[ $option_key ] );
			}
		}

		/** Set merge tag data */
		$action_instance->set_data_for_merge_tags( $integration_data );
		$action_instance->automation_id = $automation_id;
		$action_instance->parse_unsubscribe_link();

		/** Set language for decode */
		if ( true === $event_instance->support_lang ) {
			$language = BWFAN_Merge_Tag_Loader::get_data( 'user_language' );
			if ( empty( $language ) ) {
				$language = array(
					'user_language' => $event_instance->get_language_from_event( $integration_data ),
				);
				BWFAN_Merge_Tag_Loader::set_data( $language );
			}
		}

		$data_to_set                  = $action_instance->make_data( $integration_instance, $integration_data );
		$data_to_set                  = array_merge( $integration_data['global'], $data_to_set );
		$data_to_set['automation_id'] = $automation_id;
		$processed_actions_data       = array(
			'task_id'          => $task_id,
			'integration_slug' => $integration_slug,
			'action_slug'      => $action_name,
			'task_status'      => $task['status'],
			'task_attempt'     => $task['attempts'],
			'processed_data'   => $data_to_set,
		);
		if ( isset( $integration_data['event_data'] ) ) {
			$processed_actions_data['event_data'] = $integration_data['event_data'];
		}

		return $processed_actions_data;
	}

	/**
	 * Move a task to log table wasn't executed because of integration or source or event deleted.
	 *
	 * @param $task_to_delete
	 */
	public function move_task_to_log( $task_to_delete ) {
		if ( ! is_array( $task_to_delete ) || count( $task_to_delete ) === 0 ) {
			return;
		}

		foreach ( $task_to_delete as $task_id => $task_data ) {
			$task_meta = BWFAN_Model_Taskmeta::get_task_meta( $task_id );
			if ( ! is_array( $task_meta ) || empty( $task_meta ) ) {
				continue;
			}

			$automation_id            = $task_data['automation_id'];
			$action_details           = $task_data['details'];
			$action_details['status'] = 0; // 0 = Failed status. This status tells that connector or source or event was removed from task.
			$task_log_message         = [];

			$task_log_message[ current_time( 'timestamp', 1 ) ] = $task_data['message'];

			$log_id = BWFAN_Core()->logs->insert_log( $automation_id, $action_details );
			if ( isset( $task_meta['task_message'] ) ) {
				unset( $task_meta['task_message'] );
			}

			foreach ( $task_meta as $key => $value ) {
				BWFAN_Core()->logs->insert_logmeta( $log_id, $key, $value );
			}

			BWFAN_Core()->logs->insert_logmeta( $log_id, 'task_id', $task_id );
			BWFAN_Core()->logs->insert_logmeta( $log_id, 'task_message', $task_log_message );
			$this->delete_tasks( array( $task_id ) );
		}
	}

	/**
	 * Delete the given task and insert it into Log table
	 *
	 * @param $task_id
	 * @param $automation_id
	 * @param $action_details
	 *
	 * @return mixed
	 */
	public function move_single_task_to_log( $task_id, $automation_id, $action_details ) {
		$task_meta = BWFAN_Model_Taskmeta::get_task_meta( $task_id );
		if ( ! is_array( $task_meta ) || empty( $task_meta ) ) {
			return 0;
		}

		$log_id = BWFAN_Core()->logs->insert_log( $automation_id, $action_details );

		foreach ( $task_meta as $key => $value ) {
			BWFAN_Core()->logs->insert_logmeta( $log_id, $key, $value );
		}

		BWFAN_Core()->logs->insert_logmeta( $log_id, 'task_id', $task_id );
		$this->delete_tasks( array( $task_id ) );

		return $log_id;
	}

	/**
	 * Execute the tasks
	 *
	 * @param $processed_actions_data
	 */
	public function execute_tasks( $processed_actions_data, $task_details ) {
		$task_log_message = [];
		$task_id          = $task_details['ID'];
		$tasks_meta       = $task_details['meta'];
		$action_slug      = $processed_actions_data['action_slug'];
		$automation_id    = $processed_actions_data['processed_data']['automation_id'];

		BWFAN_Common::$exec_task_id = $task_id;

		if ( isset( $tasks_meta['task_message'] ) && ! empty( $tasks_meta['task_message'] ) ) {
			$task_log_message = $tasks_meta['task_message'];
		}

		/**
		 * @var $action_class_ins BWFAN_Action
		 */
		$action_class_ins = BWFAN_Core()->integration->get_action( $action_slug );
		$execute_action   = true;
		if ( is_null( $action_class_ins ) ) {
			$execute_action = false;
		} elseif ( $action_class_ins->connector ) {
			$saved_connectors = WFCO_Common::$connectors_saved_data;
			if ( ! array_key_exists( $action_class_ins->connector, $saved_connectors ) ) {
				$execute_action = false;
			}
		}

		if ( true === $execute_action ) {
			$action_result = $this->execute_single_task( $processed_actions_data );
		} else {
			$action_result = [
				'status'  => 4,
				'message' => __( 'Connector is disconnected or action is missing.', 'wp-marketing-automations' ),
			];
		}

		/** make sure action_result is array */
		if ( ! is_array( $action_result ) ) {
			$action_result = array();
		}
		/** If no message return, then assign blank */
		$action_result['message'] = ( isset( $action_result['message'] ) ? $action_result['message'] : ( isset( $action_result['bwfan_custom_message'] ) ? $action_result['bwfan_custom_message'] : '' ) );
		$this->ajax_msg           = $action_result['message'];

		/**
		 * Status
		 * 0 or Blank - Pending
		 * 1 - Paused (not required here)
		 * 3 - Executed successfully (move to logs with success state)
		 * 4 - Permanent Failure (move to logs with failure state)
		 */
		if ( ! isset( $action_result['status'] ) || empty( $action_result['status'] ) ) {
			/** Look if re-attempt possible */
			$attempt_count = (int) $processed_actions_data['task_attempt'];
			$attempt_limit = $this->get_task_retry_data();
			if ( count( $attempt_limit ) <= $attempt_count ) {
				/** No attempts left, move to logs */
				$processed_actions_data['status'] = 0;

				$this->update_task_logs( $task_id, $task_log_message, $action_result['message'] );
				$this->move_single_task_to_log( $task_id, $automation_id, $processed_actions_data );

				$this->ajax_status = false;

				/** Remove generic hooks after executing task for Action */
				$action_class_ins->after_executing_task();

				return;
			}

			/** More attempts possible */
			$new_exec_time = ( isset( $attempt_limit[ $attempt_count ] ) && (int) $attempt_limit[ $attempt_count ] > 0 ) ? (int) $attempt_limit[ $attempt_count ] : DAY_IN_SECONDS;
			$data          = array(
				'e_date'   => time() + $new_exec_time,
				'attempts' => ( $attempt_count + 1 ),
			);
			$where         = array(
				'ID' => $task_id,
			);
			BWFAN_Model_Tasks::update( $data, $where );
			$this->update_task_logs( $task_id, $task_log_message, $action_result['message'] );
			$this->ajax_status = false;

		} elseif ( 3 === $action_result['status'] ) {
			/** Executed successfully */
			$processed_actions_data['status'] = 1;

			$log_message = __( 'Task Executed Successfully.', 'wp-marketing-automations' );
			if ( ! empty( $action_result['message'] ) ) {
				$log_message .= __( ' Message: ', 'wp-marketing-automations' ) . $action_result['message'];
			}

			$this->update_task_logs( $task_id, $task_log_message, $log_message );
			$this->move_single_task_to_log( $task_id, $automation_id, $processed_actions_data );
			$this->ajax_msg = $log_message;
		} else {
			/** Permanent Failure */

			do_action( 'bwfan_task_failed_permanently', $processed_actions_data, $task_details );

			$processed_actions_data['status'] = 0;

			$this->update_task_logs( $task_id, $task_log_message, $action_result['message'] );
			$this->move_single_task_to_log( $task_id, $automation_id, $processed_actions_data );

			$this->ajax_status   = false;
			$this->ajax_redirect = true;
		}

		/** Remove generic hooks after executing task for Action */
		$action_class_ins->after_executing_task();

	}

	/**
	 * Execute Single task.
	 *
	 * @param $all_integrations_actions
	 * @param $action_details
	 *
	 * @return mixed
	 */
	public function execute_single_task( $action_details ) {
		$action_slug = $action_details['action_slug'];
		$event_slug  = $action_details['event_data']['event_slug'];
		/**
		 * @var $event_instance BWFAN_Event
		 */
		$event_instance = BWFAN_Core()->sources->get_event( $event_slug );
		/** Validate if task is eligible for execution: Check from event meta */
		$should_task_execute = $event_instance->validate_event( $action_details );
		/** Execute task if validation passed */
		if ( 1 === $should_task_execute['status'] ) {
			/**
			 * @var $action_instance BWFAN_Action
			 */
			$action_instance = BWFAN_Core()->integration->get_action( $action_slug );
			$action_result   = $action_instance->execute_action( $action_details );

			return $action_result;
		}

		/** Task failed the event validation */
		$action_result['status']  = 4;
		$action_result['message'] = $should_task_execute['message'];

		return $action_result;
	}

	public function update_task_logs( $task_id, $current_logs, $new_log = '' ) {
		if ( empty( $task_id ) || empty( $new_log ) ) {
			return;
		}

		$current_logs[ current_time( 'timestamp', 1 ) ] = $new_log;

		if ( count( $current_logs ) > 1 ) {
			$this->update_taskmeta( $task_id, 'task_message', $current_logs );

			return;
		}
		$this->insert_taskmeta( $task_id, 'task_message', $current_logs );

	}

	/**
	 * Update the task meta by id and key
	 *
	 * @param $task_id
	 * @param $key
	 * @param $value
	 */
	public function update_taskmeta( $task_id, $key, $value ) {
		$meta_data                  = array();
		$meta_data['bwfan_task_id'] = $task_id;
		$meta_data['meta_value']    = maybe_serialize( $value );
		$where                      = array(
			'bwfan_task_id' => $task_id,
			'meta_key'      => $key,
		);
		BWFAN_Model_Taskmeta::update( $meta_data, $where );
	}

	/**
	 * Insert new task meta
	 *
	 * @param $task_id
	 * @param $key
	 * @param $value
	 */
	public function insert_taskmeta( $task_id, $key, $value ) {
		$meta_data                  = array();
		$meta_data['bwfan_task_id'] = $task_id;
		$meta_data['meta_key']      = $key;
		$meta_data['meta_value']    = maybe_serialize( $value );
		BWFAN_Model_Taskmeta::insert( $meta_data );
	}

	public function add_automation_id_track_link_in_restore_url( $restore_url ) {
		global $wpdb;
		$restore_url = add_query_arg( array(
			'automation-id' => $this->automation_id,
		), $restore_url );

		$sql_query = 'Select meta_value FROM {table_name} WHERE bwfan_task_id = %d AND meta_key = %s';
		$sql_query = $wpdb->prepare( $sql_query, $this->task_id, 't_track_id' ); // WPCS: unprepared SQL OK
		$gids      = BWFAN_Model_Taskmeta::get_results( $sql_query );

		if ( ! empty( $gids ) && is_array( $gids ) ) {
			foreach ( $gids as $gid ) {
				$restore_url = add_query_arg( array(
					'track-id' => $gid['meta_value'],
				), $restore_url );

				break;
			}
		}

		return $restore_url;
	}

	/**
	 * Insert a single task in table
	 *
	 * @param $automation_id
	 * @param $task_data
	 *
	 * @return int
	 */
	public function insert_task( $automation_id, $task_data, $event_object ) {
		/**
		 * @todo This can be inserted on external server to release load
		 */
		$new_task_data           = array();
		$new_task_data['e_date'] = current_time( 'timestamp', 1 );

		if ( isset( $task_data['time']['delay_type'] ) && 'after_delay' === $task_data['time']['delay_type'] ) {
			$actual_timestamp = strtotime( current_time( 'mysql', 1 ) . '+' . (int) $task_data['time']['time_number'] . ' ' . $task_data['time']['time_type'] );

			if ( isset( $task_data['time']['scheduled_days_check'] ) && 1 === intval( $task_data['time']['scheduled_days_check'] ) && isset( $task_data['time']['scheduled_days'] ) && is_array( $task_data['time']['scheduled_days'] ) && count( $task_data['time']['scheduled_days'] ) > 0 ) {
				$days_selected    = $task_data['time']['scheduled_days'];
				$actual_timestamp = BWFAN_Common::get_nearest_date( $actual_timestamp, $days_selected );
			}

			if ( isset( $task_data['time']['scheduled_time_check'] ) && 1 === $task_data['time']['scheduled_time_check'] ) {
				$seconds          = BWFAN_Common::get_seconds_from_time_format( $task_data['time']['scheduled_time'] );
				$actual_timestamp = intval( $seconds ) + intval( $actual_timestamp );
			}

			$new_task_data['e_date'] = $actual_timestamp;
		}

		if ( isset( $task_data['time']['delay_type'] ) && 'fixed' === $task_data['time']['delay_type'] && isset( $task_data['time']['fixed_date'] ) && ! empty( $task_data['time']['fixed_date'] ) ) {

			$fixed_date           = $task_data['time']['fixed_date'];
			$fixed_date_timestamp = strtotime( $fixed_date );
			$fixed_time           = $task_data['time']['fixed_time'];
			$fixed_time_seconds   = BWFAN_Common::get_seconds_from_time_format( $fixed_time );
			$actual_timestamp     = intval( $fixed_date_timestamp ) + intval( $fixed_time_seconds );

			$date1 = date_i18n( 'Y-m-d H:i:s' );
			$date2 = date_i18n( 'Y-m-d H:i:s', false, true );

			$datetime1 = new DateTime( date( 'Y-m-d H:i:s', strtotime( $date1 ) ) );
			$datetime2 = new DateTime( date( 'Y-m-d H:i:s', strtotime( $date2 ) ) );

			$interval = $datetime1->diff( $datetime2 );

			$plus_minus         = ( $datetime1->getTimestamp() > $datetime2->getTimestamp() ) ? '+' : '-';
			$days               = $interval->format( $plus_minus . '%d' );
			$hours_difference   = $interval->format( $plus_minus . '%h' );
			$minutes_difference = $interval->format( $plus_minus . '%i' );

			$difference              = ( $days * 24 * 60 * 60 ) + ( $hours_difference * 60 * 60 ) + ( $minutes_difference * 60 );
			$actual_timestamp        = $actual_timestamp - $difference;
			$new_task_data['e_date'] = $actual_timestamp;
		}

		$new_task_data['c_date']             = current_time( 'mysql', 1 );
		$new_task_data['status']             = 0;
		$new_task_data['integration_slug']   = $task_data['integration_slug'];
		$new_task_data['integration_action'] = $task_data['action_slug'];
		$new_task_data['automation_id']      = $automation_id;
		$new_task_data['priority']           = ( isset( $task_data['action_priority'] ) && ! empty( $task_data['action_priority'] ) ) ? $task_data['action_priority'] : 10;

		// Task data can be modified before getting into db
		$new_task_data = apply_filters( 'bwfan_pre_insert_task_db', $new_task_data, $task_data, $automation_id, $event_object );

		BWFAN_Model_Tasks::insert( $new_task_data );
		$task_id = BWFAN_Model_Tasks::insert_id();

		return $task_id;
	}

	/**
	 *  Return all the eligible tasks for execution. Limit is 50.
	 *
	 * @param null $no_limit
	 *
	 * @return array
	 */
	public function get_tasks() {
		global $wpdb;

		$active_automations = BWFAN_Core()->automations->get_active_automations();
		$query              = $wpdb->prepare( 'Select ID, integration_slug, integration_action, automation_id, status, e_date from {table_name} WHERE status != %d AND status !=%d ORDER BY e_date ASC LIMIT %d', 1, 3, 50 );
		$active_tasks       = BWFAN_Model_Tasks::get_results( $query );
		$result             = $this->make_data_for_tasks( $active_automations, $active_tasks );

		return $result;
	}

	public function make_data_for_tasks( $active_automations, $active_tasks, $type = 'task' ) {
		if ( ! is_array( $active_tasks ) || count( $active_tasks ) === 0 ) {
			return [];
		}

		$result = [];
		foreach ( $active_tasks as $tasks ) {
			$task_id       = $tasks['ID'];
			$automation_id = $tasks['automation_id'];
			if ( ! isset( $active_automations[ $automation_id ] ) ) {
				continue;
			}
			$tasks['title'] = $active_automations[ $automation_id ]['meta']['title'];
			if ( 'task' === $type ) {
				$tasks['meta'] = BWFAN_Model_Taskmeta::get_task_meta( $task_id );
			}
			if ( 'log' === $type ) {
				$tasks['meta'] = BWFAN_Model_Logmeta::get_log_meta( $task_id );
			}
			$result[ $task_id ] = $tasks;

			unset( $tasks );
		}

		return $result;
	}

	/**
	 * Return all tasks
	 * @return array
	 */
	public function get_all_tasks( $no_limit = null ) {
		global $wpdb;
		$per_page = 20;
		$offset   = 0;

		if ( class_exists( 'BWFAN_Tasks_Table' ) ) {
			$per_page = BWFAN_Tasks_Table::$per_page;
			$offset   = ( BWFAN_Tasks_Table::$current_page - 1 ) * $per_page;
		}

		BWFAN_Core()->automations->return_all = true;
		$active_automations                   = BWFAN_Core()->automations->get_all_automations( true );

		if ( ! is_array( $active_automations ) || count( $active_automations ) === 0 ) {
			return [];
		}

		/* Filter option - Automation handling */
		$automation_id = ( isset( $_GET['filter_aid'] ) && ! empty( $_GET['filter_aid'] ) ) ? sanitize_text_field( $_GET['filter_aid'] ) : null; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification

		/* Filter option - Action handling */
		$task_action = ( isset( $_GET['filter_action'] ) && ! empty( $_GET['filter_action'] ) ) ? sanitize_text_field( $_GET['filter_action'] ) : null; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification

		/* Filter option - Status handling */
		$task_status = ( isset( $_GET['status'] ) && '' !== $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 't_0'; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		if ( strpos( $task_status, '_' ) !== false ) {
			$task_status = explode( '_', $task_status );
			$task_status = intval( $task_status[1] );
		} else {
			$task_status = 0;
		}

		$task_table       = $wpdb->prefix . 'bwfan_tasks';
		$automation_table = $wpdb->prefix . 'bwfan_automations';
		$query_select     = "SELECT $task_table.ID, $task_table.integration_slug, $task_table.integration_action, $task_table.automation_id, $task_table.status, $task_table.e_date";
		$query_from       = "FROM " . $task_table . " JOIN " . $automation_table . " ";

		if ( $task_status === 1 ) {
			$query_where = "WHERE 1=1 and " . $task_table . ".automation_id" . " = " . $automation_table . ".ID and " . $automation_table . ".status=2";
		} else {
			$query_where = "WHERE 1=1 and " . $task_table . ".automation_id" . " = " . $automation_table . ".ID and " . $automation_table . ".status=1";
		}

		$query_order = "ORDER BY $task_table.e_date ASC, $task_table.ID ASC";
		$query_limit = '';
		$params      = [];

		/** Filter option - Search */
		$search = false;
		if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$search                          = true;
			$query_select                    = "SELECT $task_table.ID, $task_table.integration_slug, $task_table.integration_action, $task_table.automation_id, $task_table.status, $task_table.e_date ";
			$query_from                      .= "LEFT JOIN " . $wpdb->prefix . "bwfan_taskmeta as m";
			$query_from                      .= " ON $task_table.ID = m.bwfan_task_id";
			$query_where                     .= " AND m.meta_value LIKE %s";
			$params[]                        = "%" . sanitize_text_field( $_GET['s'] ) . "%"; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$query_order                     = " ORDER BY $task_table.e_date ASC, $task_table.ID ASC";
			$this->show_filtered_tasks_count = true;
		}

		if ( ! is_null( $automation_id ) ) {
			$query_where                     .= ( true === $search ) ? " AND $task_table.automation_id = %d" : " AND " . $task_table . ".automation_id = %d";
			$params[]                        = $automation_id;
			$this->show_filtered_tasks_count = true;
		}
		if ( ! is_null( $task_action ) ) {
			$query_where                     .= ( true === $search ) ? " AND $task_table.integration_action = %s" : " AND " . $task_table . ".integration_action = %s";
			$params[]                        = $task_action;
			$this->show_filtered_tasks_count = true;
		}
		if ( ! is_null( $task_status ) && 0 === absint( $task_status ) ) {
			$query_where                     .= ( true === $search ) ? " AND $task_table.status = %d" : " AND " . $task_table . ".status = %d";
			$params[]                        = $task_status;
			$this->show_filtered_tasks_count = true;
		}

		$query_limit  .= 'LIMIT %d OFFSET %d';
		$params[]     = $per_page;
		$params[]     = $offset;
		$new_query    = $wpdb->prepare( "{$query_select} {$query_from} {$query_where} {$query_order} {$query_limit}", $params ); //phpcs:ignore WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders
		$active_tasks = $wpdb->get_results( $new_query, ARRAY_A ); // WPCS: unprepared SQL OK
		if ( $this->show_filtered_tasks_count ) {
			$tasks_count  = 0;
			$query_select = "SELECT COUNT($task_table.ID) as `count`";
			if ( true === $search ) {
				$query_select = "SELECT COUNT($task_table.ID) as `count`";
			}

			array_pop( $params );
			array_pop( $params );
			$new_query   = $wpdb->prepare( "{$query_select} {$query_from} {$query_where} {$query_order}", $params ); //phpcs:ignore WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders
			$count_tasks = $wpdb->get_results( $new_query, ARRAY_A ); // WPCS: unprepared SQL OK
			if ( is_array( $count_tasks ) && count( $count_tasks ) > 0 ) {
				$tasks_count = $count_tasks[0]['count'];
			}
			$this->filtered_tasks_count = $tasks_count;
		}

		$result = $this->make_data_for_tasks( $active_automations, $active_tasks );

		return $result;
	}

	public function remove_tasks_on_connector_disconnection( $integration_slug ) {
		$tasks = $this->get_tasks_by_key( 'integration_slug', $integration_slug );
		if ( ! is_array( $tasks ) || 0 === count( $tasks ) ) {
			return;
		}

		// Get all task ids related to integration
		$tasks_to_delete = [];
		foreach ( $tasks as $task_details ) {
			switch ( $task_details['integration_slug'] ) {
				case $integration_slug:
					$tasks_to_delete[] = $task_details['ID'];
					break;
			}
		}

		// Delete all tasks
		if ( is_array( $tasks_to_delete ) && count( $tasks_to_delete ) > 0 ) {
			$this->delete_tasks( $tasks_to_delete );
		}
	}

	public function get_tasks_by_key( $col_key, $col_value ) {
		global $wpdb;
		$query = $wpdb->prepare( 'Select ID, integration_slug, integration_action, automation_id, status, e_date from {table_name} WHERE {col_name} = %s ORDER BY e_date ASC', $col_value );
		$query = str_replace( '{col_name}', $col_key, $query );
		$tasks = BWFAN_Model_Tasks::get_results( $query );

		return $tasks;
	}

	/**
	 * Return tasks count whose automation's are active
	 */
	public function get_tasks_count() {
		global $wpdb;
		$automation_table = $wpdb->prefix . 'bwfan_automations';
		$query            = "SELECT count(t.ID) as tasks_count FROM {table_name} as t
							 INNER JOIN $automation_table as a on t.automation_id = a.ID
							 WHERE a.status = %d";
		$query            = $wpdb->prepare( $query, 1 ); // WPCS: unprepared SQL OK
		$tasks_count      = BWFAN_Model_Tasks::get_results( $query );
		if ( ! empty( $tasks_count ) && isset( $tasks_count[0] ) && isset( $tasks_count[0]['tasks_count'] ) ) {
			$tasks_count = $tasks_count[0]['tasks_count'];
		} else {
			$tasks_count = 0;
		}

		return $tasks_count;
	}

	public function modify_task_details( $task_details, $automation_id, $event_object ) {
		$selected_events = $event_object->get_user_selected_actions();
		if ( ! is_null( $selected_events ) ) {
			$task_details['priority'] = 5;
		}

		return $task_details;
	}

	public function modify_task_details_for_syncing( $task_details, $actual_task_details, $automation_id, $event_object ) {
		$selected_events = $event_object->get_user_selected_actions();
		if ( ! is_null( $selected_events ) ) {
			return $task_details;
		}

		if ( 'immediately' === $actual_task_details['time']['delay_type'] && 0 !== $event_object->get_sync_start_time() ) {
			$task_details['e_date'] = $event_object->get_sync_start_time();
		}

		return $task_details;
	}

	public function fetch_tasks_count( $automation_id = 0, $status ) {
		global $wpdb;

		$task_table        = $wpdb->prefix . 'bwfan_tasks';
		$automation_table  = $wpdb->prefix . 'bwfan_automations';
		$query_select      = "SELECT count($task_table.ID) as tasks_count";
		$query_from        = "FROM " . $task_table . " JOIN " . $automation_table . " ";
		$params            = [];
		$automation_status = 1;
		if ( $status === 1 ) {
			$automation_status = 2;
			$query_where       = "WHERE " . $task_table . ".automation_id" . " = " . $automation_table . ".ID and " . $automation_table . ".status=%d";
			$params[]          = $automation_status;
		} else {
			$query_where = "WHERE " . $task_table . ".automation_id" . " = " . $automation_table . ".ID and " . $automation_table . ".status=%d";
			$params[]    = $automation_status;
		}

		if ( isset( $_GET['filter_aid'] ) && ! empty( $_GET['filter_aid'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$automation_id = sanitize_text_field( $_GET['filter_aid'] ); // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		if ( isset( $_GET['automations'] ) && ! empty( $_GET['automations'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$automation_id = sanitize_text_field( $_GET['automations'] ); // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		if ( ! is_null( $automation_id ) && ! empty( $automation_id ) ) {
			$query_where                     .= " AND $task_table.automation_id = %d";
			$params[]                        = $automation_id;
			$this->show_filtered_tasks_count = true;
		}

		$new_query1 = $wpdb->prepare( "{$query_select} {$query_from} {$query_where} ", $params ); //phpcs:ignore WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders

		$tasks_count = BWFAN_Model_Tasks::get_results( $new_query1 );
		$tasks_count = $tasks_count[0]['tasks_count'];

		return $tasks_count;
	}

	/**
	 * Halt every action of current connector because it has reached the rate limit.
	 * Hooked to 'bwfan_rate_limit_reached'.
	 *
	 * @param $integration_slug
	 * @param $timeout_in_seconds
	 */
	public function restrict_tasks( $integration_slug, $timeout_in_seconds ) {
		global $wpdb;
		$query = $wpdb->prepare( 'Update {table_name} SET status = %d WHERE integration_slug = %s', 2, $integration_slug );
		BWFAN_Model_Tasks::query( $query );

		$restricted_integrations = get_option( 'bwfan_rl_exceeded', array() );

		$restricted_integrations[ $integration_slug ] = time() + intval( $timeout_in_seconds );
		update_option( 'bwfan_rl_exceeded', $restricted_integrations );
	}

	/**
	 * Delete all the tasks of the automation by tasks indexes which is present in task meta.
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
		$sql_query            = "Select bwfan_task_id FROM {table_name} WHERE meta_key = %s AND meta_value IN ($prepare_placeholders)";
		$sql_query            = $wpdb->prepare( $sql_query, array_merge( array( $meta_key ), $t_to_delete ) ); // WPCS: unprepared SQL OK
		$task_ids             = BWFAN_Model_Taskmeta::get_results( $sql_query );

		if ( ! is_array( $task_ids ) || 0 === count( $task_ids ) ) {
			return;
		}
		$task_ids = array_column( $task_ids, 'bwfan_task_id' );

		// Now get all tasks by automation_id
		$task_ids_count       = count( $task_ids );
		$prepare_placeholders = array_fill( 0, $task_ids_count, '%s' );
		$prepare_placeholders = implode( ', ', $prepare_placeholders );
		$sql_query            = "Select ID FROM {table_name} WHERE automation_id = %d AND ID IN ($prepare_placeholders)";
		$sql_query            = $wpdb->prepare( $sql_query, array_merge( array( intval( $automation_id ) ), $task_ids ) ); // WPCS: unprepared SQL OK
		$task_ids             = BWFAN_Model_Tasks::get_results( $sql_query );

		if ( ! is_array( $task_ids ) || 0 === count( $task_ids ) ) {
			return;
		}

		$task_ids = array_column( $task_ids, 'ID' );
		$this->delete_tasks( $task_ids );
	}

	/** get all the task details for api */
	public function get_history( $task_status = 't_0', $automation_id = '', $action_slug = '', $search = '', $offset = 0, $limit = 25 ) {
		global $wpdb;
		$tasks_count                          = 0;
		BWFAN_Core()->automations->return_all = true;
		$active_automations                   = BWFAN_Core()->automations->get_all_automations( true );

		if ( ! is_array( $active_automations ) || count( $active_automations ) === 0 ) {
			return [];
		}

		if ( strpos( $task_status, '_' ) !== false ) {
			$task_status = explode( '_', $task_status );
			$task_status = intval( $task_status[1] );
		} else {
			$task_status = 0;
		}

		$task_table       = $wpdb->prefix . 'bwfan_tasks';
		$automation_table = $wpdb->prefix . 'bwfan_automations';
		$query_select     = "SELECT $task_table.ID, $task_table.integration_slug, $task_table.integration_action, $task_table.automation_id, $task_table.status, $task_table.e_date";
		$query_from       = "FROM " . $task_table . " JOIN " . $automation_table . " ";

		if ( $task_status === 1 ) {
			$query_where = "WHERE 1=1 and " . $task_table . ".automation_id" . " = " . $automation_table . ".ID and " . $automation_table . ".status=2";
		} else {
			$query_where = "WHERE 1=1 and " . $task_table . ".automation_id" . " = " . $automation_table . ".ID and " . $automation_table . ".status=1";
		}

		$query_order = "ORDER BY $task_table.e_date ASC, $task_table.ID ASC";
		$query_limit = '';
		$params      = [];

		/** Filter option - Search */

		if ( ! empty( $search ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$query_select                    = "SELECT $task_table.ID, $task_table.integration_slug, $task_table.integration_action, $task_table.automation_id, $task_table.status, $task_table.e_date ";
			$query_from                      .= "LEFT JOIN " . $wpdb->prefix . "bwfan_taskmeta as m";
			$query_from                      .= " ON $task_table.ID = m.bwfan_task_id";
			$query_where                     .= " AND m.meta_value LIKE %s";
			$params[]                        = "%" . $search . "%"; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$query_order                     = " ORDER BY $task_table.e_date ASC, $task_table.ID ASC";
			$this->show_filtered_tasks_count = true;
		}

		if ( ! empty( $automation_id ) ) {
			$query_where                     .= ( ! empty( $search ) ) ? " AND $task_table.automation_id = %d" : " AND " . $task_table . ".automation_id = %d";
			$params[]                        = $automation_id;
			$this->show_filtered_tasks_count = true;
		}
		if ( ! empty( $action_slug ) ) {
			$query_where                     .= ( ! empty( $search ) ) ? " AND $task_table.integration_action = %s" : " AND " . $task_table . ".integration_action = %s";
			$params[]                        = $action_slug;
			$this->show_filtered_tasks_count = true;
		}
		if ( ! is_null( $task_status ) && 0 === absint( $task_status ) ) {
			$query_where                     .= ( ! empty( $search ) ) ? " AND $task_table.status = %d" : " AND " . $task_table . ".status = %d";
			$params[]                        = $task_status;
			$this->show_filtered_tasks_count = true;
		}

		$query_limit  .= 'LIMIT %d OFFSET %d';
		$params[]     = $limit;
		$params[]     = $offset;
		$new_query    = $wpdb->prepare( "{$query_select} {$query_from} {$query_where} {$query_order} {$query_limit}", $params ); //phpcs:ignore WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders
		$active_tasks = $wpdb->get_results( $new_query, ARRAY_A ); // WPCS: unprepared SQL OK
		if ( $this->show_filtered_tasks_count ) {

			$query_select = "SELECT COUNT($task_table.ID) as `count`";
			if ( true === $search ) {
				$query_select = "SELECT COUNT($task_table.ID) as `count`";
			}

			array_pop( $params );
			array_pop( $params );
			$new_query = $wpdb->prepare( "{$query_select} {$query_from} {$query_where} {$query_order}", $params ); //phpcs:ignore WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders

			$count_tasks = $wpdb->get_results( $new_query, ARRAY_A ); // WPCS: unprepared SQL OK
			if ( is_array( $count_tasks ) && count( $count_tasks ) > 0 ) {
				$tasks_count = $count_tasks[0]['count'];
			}
			$this->filtered_tasks_count = $tasks_count;
		}

		$result = $this->make_data_for_tasks( $active_automations, $active_tasks );
		$result = $this->make_data_for_tasks_table( $result );

		return $result;
	}

	/** make data for tasks table
	 *
	 * @param $rows
	 *
	 * @return array
	 */
	public function make_data_for_tasks_table( $rows ) {
		global $wpdb;

		if ( ! is_array( $rows ) || count( $rows ) === 0 ) {
			return array();
		}

		$found_posts = array();
		$dependency  = array(
			'dependency_table' => $wpdb->prefix . 'bwfan_automations',
			'col_name'         => 'status',
			'col_value'        => '1',
			'dependency_col'   => 'ID',
			'dependent_col'    => 'automation_id',
		);

		// Fetch the tasks of only 1 automation
		if ( ! is_null( $this->automation_id ) ) {

			$dependency['automation_id']    = $this->automation_id;
			$dependency['automation_table'] = $wpdb->prefix . 'bwfan_automations';
			$dependency['automation_col']   = 'ID';
		}

		$found_posts['found_posts'] = BWFAN_Model_Tasks::count_rows( $dependency );

		// If tasks are filtered, then show the count of filtered data
		if ( BWFAN_Core()->tasks->show_filtered_tasks_count ) {
			$found_posts['found_posts'] = BWFAN_Core()->tasks->filtered_tasks_count;
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
				'task_corrupted'          => false
			);
			/**
			 * @var $action_instance BWFAN_Action
			 */
			$action_instance = BWFAN_Core()->integration->get_action( $action_slug );
			if ( ! is_null( $action_instance ) ) {
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
			if ( ! is_null( $integration_instance ) ) {
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

BWFAN_Core::register( 'tasks', 'BWFAN_Tasks' );
