<?php

abstract class BWFAN_Event {
	/** @var string Source that event belongs to */
	protected $source_type = 'wp';

	/** @var string Event Optgroup label */
	protected $optgroup_label = 'WordPress';

	protected $priority = 200;

	/** @var string Event nice name */
	protected $event_name = '';

	/** @var string Event description */
	protected $event_desc = '';

	protected $excluded_actions = [];
	protected $included_actions = [];

	protected $event_saved_data = [];

	/** @var string Event slug used in array making to fetch the event object */
	protected $localize_data = [];
	public $support_lang = false;
	protected $is_time_independent = false;
	protected $is_syncable = false;
	protected $validation_passed = false;
	protected $event_actions = [];
	protected $track_automation_run = true;

	/** Customer supported */
	protected $customer_email_tag = '{{customer_email}}';

	/** @var array merge tag groups array supported by the event */
	protected $event_merge_tag_groups = [];

	/** @var array rule groups array supported by the event */
	protected $event_rule_groups = [];
	protected $sync_start_time = 0;
	protected $log_type = 'event_triggered';
	protected $user_selected_actions = [];
	protected $automations_for_current_event_db = [];
	protected $event_automation_id = null;
	protected $error_message = '';
	protected $automations_arr = [];

	/** History sync properties */
	protected $sync_automation_id = null;
	protected $sync_source = null;
	protected $sync_event = null;
	protected $sync_id = null;
	protected $from_year = null;
	protected $from_month = null;
	protected $from_day = null;
	protected $to_year = null;
	protected $to_month = null;
	protected $to_day = null;
	protected $display_count = null;
	protected $page = null;
	protected $offset = null;
	protected $processed = null;

	public $message_validate_event = null;

	public function validate_event( $task_details ) {
		$result            = [];
		$result['status']  = 1;
		$result['message'] = '';

		return $result;
	}

	public function validate_event_data_before_executing_task( $data ) {
		return true;
	}

	public function load_hooks() {
		//
	}

	public function get_automation_event_validation() {
		return array(
			'status'  => 1,
			'message' => '',
		);
	}

	public function get_automation_event_status() {
		return array(
			'status'  => 4,
			'message' => __( 'Event has been changed in the automation', 'wp-marketing-automations' ),
		);
	}

	public function get_automation_event_success() {
		return array(
			'status'  => 1,
			'message' => '',
		);
	}

	/**
	 * show the validate checkbox in event meta fields
	 * contains text related to wc order only.
	 * Is overridable in the child event class
	 *
	 * @param $unique_slug
	 * @param $section_label
	 * @param $field_label
	 */
	public function get_validation_html( $unique_slug, $section_label, $field_label ) {
		?>
		<div class="bwfan-col-sm-12 bwfan-pl-0 bwfan_mt15">
			<label for="" class="bwfan-label-title"><?php esc_html_e( $section_label ); ?></label>
			<input type="checkbox" name="event_meta[validate_event]" id="bwfan-validate_event" value="1" class="validate_event_1 <?php esc_html_e( $unique_slug ); ?>-validate_event" {{is_validated}}/>
			<label for="bwfan-validate_event" class="bwfan-checkbox-label"><?php esc_html_e( $field_label ); ?></label>
			<div class="clearfix bwfan_field_desc"><?php echo wp_kses_post( 'This setting is useful to <u>verify time-delayed Actions</u>. For instance, you can create a follow-up Action that runs after 30 days of placing an order. That Action won\'t trigger if the above selected Order Statuses are not matched to the order.', 'wp-marketing-automations' ); ?></label>
		</div>
		<?php
	}

	/**
	 * A controller function to run automation every time an appropriate event occurs
	 * usually called by the event class just after the event hook to load all automations and run.
	 * @return array|bool
	 */
	public function run_automations() {
		BWFAN_Core()->public->load_active_automations( $this->get_slug() );

		if ( ! is_array( $this->automations_arr ) || count( $this->automations_arr ) === 0 ) {
			if ( $this->sync_start_time > 0 ) {
				/** Sync process */
				BWFAN_Core()->logger->log( 'Sync #' . $this->sync_id . '. No active automations found for Event ' . $this->get_slug(), 'sync' );

				return false;
			}
			BWFAN_Core()->logger->log( 'Async callback: No active automations found. Event - ' . $this->get_slug(), $this->log_type );

			return false;
		}

		/** Extra checking for certain event like form events */
		$this->automations_arr = $this->validate_event_data_before_creating_task($this->automations_arr);

		if ( ! is_array( $this->automations_arr ) || count( $this->automations_arr ) === 0 ) {
		    return false;
		}

		$automation_actions = [];

		foreach ( $this->automations_arr as $automation_id => $automation_data ) {
			if ( $this->get_slug() !== $automation_data['event'] || 0 !== intval( $automation_data['requires_update'] ) ) {
				continue;
			}
			$ran_actions = $this->handle_single_automation_run( $automation_data, $automation_id );

			$automation_actions[ $automation_id ] = $ran_actions;
		}

		return $automation_actions;
	}

	public function validate_event_data_before_creating_task($automations_arr){
	    return $automations_arr;
	}

	public function get_slug() {
		return str_replace( array( 'bwfan_' ), '', sanitize_title( get_class( $this ) ) );
	}

	/**
	 * Handle execution of each automation to get all the executable tasks for the automation.
	 * Also responsible to run pre executable action function to instruct events to setup required data before execution.
	 *
	 * @param $automation_data
	 * @param $automation_id
	 *
	 * @return bool|int
	 */
	public function handle_single_automation_run( $automation_data, $automation_id ) {
		$this->event_automation_id = $automation_id;

		/** Setup the rules data */
		$this->pre_executable_actions( $automation_data );

		/** get all the actions which have passed the rules */
		$actions = $this->get_executable_actions( $automation_data );

		if ( ! isset( $actions['actions'] ) || ! is_array( $actions['actions'] ) || count( $actions['actions'] ) === 0 ) {
			if ( $this->sync_start_time > 0 ) {
				/** Sync process */
				BWFAN_Core()->logger->log( 'Sync #' . $this->sync_id . '. No task eligible for Automation ID - ' . $automation_id . '. Event - ' . $this->get_slug(), 'sync' );

				return false;
			}
			BWFAN_Core()->logger->log( 'No task eligible for Automation ID - ' . $automation_id . '. Event - ' . $this->get_slug(), $this->log_type );

			return false;
		}

		$event_data = $this->get_automation_event_data( $automation_data );

		/** This only occurs when sync process is going on */
		if ( ! empty( $this->user_selected_actions ) ) {
			$final_actions = $this->filter_executable_actions( $actions['actions'] );

			try {
				$final_actions['actions'] = $this->recalculate_actions_time( $final_actions['actions'] );
			} catch ( Exception $exception ) {
				BWFAN_Core()->logger->log( 'Register task function not overrided by child class ->' . get_class( $this ), $this->log_type );
			}

			$actions['actions'] = $final_actions['actions'];
		}
		BWFAN_Core()->logger->log( 'capture_async_data' . __CLASS__ . ' ' . print_r( $event_data, true ), 'event_triggered' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions

		try {
			/** Register all those tasks which passed through rules or which are direct actions. The following function is present in every event class. */
			$this->register_tasks( $automation_id, $actions['actions'], $event_data );
		} catch ( Exception $exception ) {
			BWFAN_Core()->logger->log( 'Register task function not overrided by child class' . get_class( $this ), $this->log_type );
		}

		return count( $actions['actions'] );
	}

	/**
	 * Make rules data for every event.
	 *
	 * @param $automation_data
	 */
	public function pre_executable_actions( $automation_data ) {

	}

	/**
	 * Get the actions which are actually going to run.
	 *
	 * @param $automation_meta
	 *
	 * @return array
	 */
	public function get_executable_actions( $automation_meta ) {
		$this->event_actions = [];
		$ui_data             = $automation_meta['uiData'];

		foreach ( $ui_data as $details ) {
			$return_data = $this->get_actions_data( $details, $automation_meta );
			if ( is_array( $return_data ) && count( $return_data ) > 0 ) {
				$this->event_actions[] = $return_data;
			}
			if ( $this->validation_passed ) {
				$this->validation_passed = false;
				break;
			}
		}

		$executable_actions = $this->combine_actions();

		return $executable_actions;
	}

	/**
	 * Get those actions which satisfies the rules.
	 *
	 * @param $details
	 * @param $automation_meta
	 *
	 * @return array
	 */
	public function get_actions_data( $details, $automation_meta ) {
		$return_data = [];

		switch ( $details['id'] ) {
			case 'condition':
				$group_id         = $details['group_id'];
				$group_conditions = $automation_meta['condition'][ $group_id ];
				$is_passed        = BWFAN_Core()->rules->match_groups( $group_conditions );

				if ( $is_passed ) {
					$return_data['actions']  = $automation_meta['actions'][ $group_id ];
					$return_data['group_id'] = $group_id;
					$this->validation_passed = true;
				}
				break;
			case 'action':
				$group_id                = $details['group_id'];
				$return_data['actions']  = $automation_meta['actions'][ $group_id ];
				$return_data['group_id'] = $group_id;
				break;
			default:
				break;
		}

		return $return_data;
	}

	/**
	 * Combines all the actions of all groups whose tasks will be made.
	 * @return array
	 */
	public function combine_actions() {
		if ( ! is_array( $this->event_actions ) || 0 === count( $this->event_actions ) ) {
			return $this->event_actions;
		}

		$result      = [];
		$all_actions = [];
		foreach ( $this->event_actions as $details ) {
			$actions  = $details['actions'];
			$group_id = $details['group_id'];
			if ( ! is_array( $actions ) || empty( $actions ) ) {
				continue;
			}
			foreach ( $actions as $key1 => $action_detail ) {
				if( empty( $action_detail['action_slug'] ) || empty( $action_detail['integration_slug'] ) ) {
					continue;
				}
				$action_detail['group_id']  = $group_id;
				$action_detail['action_id'] = $key1;
				$all_actions[]              = $action_detail;
			}
		}

		$this->event_actions = [];
		$result['actions']   = $all_actions;

		return $result;
	}

	/**
	 * Returns the current event settings set in the automation at the time of task creation.
	 *
	 * @param $value
	 *
	 * @return array
	 */
	public function get_automation_event_data( $value ) {
		return [
			'event_source'   => $value['source'],
			'event_slug'     => $value['event'],
			'validate_event' => ( isset( $value['event_meta']['validate_event'] ) ) ? 1 : 0,
		];
	}

	public function filter_executable_actions( $actions ) {
		$final_actions         = [];
		$user_selected_actions = $this->user_selected_actions;

		foreach ( $user_selected_actions as $group_actions ) {

			foreach ( $group_actions as $action_details ) {
				$action_slug = $action_details['action_slug'];

				foreach ( $actions as $act_ind => $act_det ) {
					$act_sl     = $act_det['action_slug'];
					$unique_key = $act_sl . '_' . $act_ind;

					if ( $action_slug === $act_sl ) {
						if ( ! isset( $final_actions[ $unique_key ] ) ) {
							$final_actions[ $unique_key ] = $act_det;
						}
					}
				}
			}
		}

		sort( $final_actions );

		return array(
			'actions' => $final_actions,
		);
	}

	/**
	 * @param $actions
	 *
	 * Recalculate action's execution time with respect to order date.
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function recalculate_actions_time( $actions ) {
		throw new ErrorException( 'This function `' . __FUNCTION__ . '` Must be override in child class' );
	}

	public function register_tasks( $automation_id, $actions, $event_data ) {
		throw new ErrorException( 'This function `' . __FUNCTION__ . '` Must be override in child class' );
	}

	/**
	 * Show the sync ui for those events which allow processing of old records
	 */
	public function get_sync_view() {
		$automation_id          = BWFAN_Core()->automations->automation_id;
		$automation_details     = BWFAN_Model_Automations::get_automation_with_data( $automation_id );
		$unique_actions         = BWFAN_Core()->automations->get_unique_automation_actions( $automation_details['meta']['actions'] );
		$unique_actions_indexes = BWFAN_Core()->automations->get_automation_actions_indexes( $automation_details['meta']['actions'] );
		?>
		<fieldset class="bwfan-mb-15">
			<form action="" method="post" class="bwfan_sync_old_data">
				<div class="bwfan_sync_step1">
					<div class="bwfan_step_content">
						<div class="form-group field-radios">
							<label for="disable-coupon-field"><?php esc_html_e( 'Select From Date', 'wp-marketing-automations' ); ?></label>
							<div class="field-wrap">
								<div class="radio-list">
									<input type="text" class="datepicker" name="from" required id="from_date"/>
								</div>
							</div>
						</div>
						<div class="form-group field-radios">
							<label for="disable-coupon-field"><?php esc_html_e( 'Select To Date', 'wp-marketing-automations' ); ?></label>
							<div class="field-wrap">
								<div class="radio-list">
									<input type="text" class="datepicker" name="to" required id="to_date"/>
								</div>
							</div>
						</div>
						<div class="form-group field-radios">
							<label for="disable-coupon-field"><?php esc_html_e( 'Select Actions', 'wp-marketing-automations' ); ?></label>
							<div class="field-wrap">
								<div class="radio-list">
									<?php
									foreach ( $unique_actions as $action_slug => $integration_slug ) {

										$action      = BWFAN_Core()->integration->get_action( $action_slug );
										$integration = BWFAN_Core()->integration->get_integration( $integration_slug );
										$value       = implode( ',', $unique_actions_indexes[ $action_slug ] );
										?>
										<div class="bwfan-checkboxes">
											<input type="checkbox" value="<?php esc_html_e( $value ); ?>" name="selected_actions[]" id="bwfan-<?php esc_html_e( $action_slug ); ?>" class="bwfan-checkbox bwfan-select-sync-actions" data-warning="<?php esc_html_e( 'Please select atleast 1 action', 'wp-marketing-automations' ); ?>"/>
											<label for="bwfan-<?php esc_html_e( $action_slug ); ?>" class="bwfan-checkbox-label"><?php esc_html_e( $integration->get_name() ); ?>
												: <?php esc_html_e( $action->get_name() ); ?></label>
										</div>
										<?php
									}
									?>
								</div>
							</div>
						</div>
						<div class="form-group field-radios">
							<label for="disable-coupon-field"></label>
							<div class="field-wrap">
								<input type="submit" class="bwfan-display-none" value="<?php esc_html_e( 'Sync', 'wp-marketing-automations' ); ?>" data-step="1" data-next="2"/>
								<a href="javascript:void(0);" class="bwfan_btn_blue bwfan_save_btn_style bwfan_sync_old_data_anchor"><?php esc_html_e( 'Sync', 'wp-marketing-automations' ); ?></a>
							</div>
						</div>
					</div>
				</div>
				<div class="bwfan_sync_step2 bwfan-display-none">
					<div class="bwfan_step_content"></div>
				</div>

				<input type="hidden" name="action" value="bwf_sync_automation"/>
				<input type="hidden" name="automation_id" value="<?php esc_html_e( $automation_id ); ?>"/>
				<input type="hidden" name="page" value="autonami-automations"/>
				<input type="hidden" name="_wpnonce" value="<?php esc_html_e( wp_create_nonce( 'bwfan-action-admin' ) ); ?>"/>
				<input type="hidden" name="source" value="<?php esc_html_e( $automation_details['source'] ); ?>"/>
				<input type="hidden" name="event" value="<?php esc_html_e( $automation_details['event'] ); ?>"/>
			</form>
			<div class="bwfan_sync_steplast">
				<div class="bwfan_step_content"></div>
			</div>
		</fieldset>
		<?php
	}

	/**
	 * Process the old records for an event
	 * Can be modified on child level
	 */
	public function process_sync() {
		/** Checking if not syncable */
		if ( false === $this->is_syncable ) {
			return;
		}
		global $wpdb;
		//phpcs:disable WordPress.Security.NonceVerification
		$automation_id      = isset( $_POST['automation_id'] ) ? intval( sanitize_text_field( $_POST['automation_id'] ) ) : 0; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$query              = $wpdb->prepare( 'Select ID from {table_name} WHERE a_id = %d AND status = %d', $automation_id, 1 );
		$active_automations = BWFAN_Model_Syncrecords::get_results( $query );

		if ( is_array( $active_automations ) && count( $active_automations ) > 0 ) {
			$message     = esc_html__( 'Sync process for this automation is already running! Please try again later.', 'wp-marketing-automations' );
			$resp        = array(
				'status'       => true,
				'show_content' => false,
			);
			$resp['msg'] = $message;
			wp_send_json( $resp );
		}

		/** starting date */
		$this->from_year  = 2011;
		$this->from_month = 1;
		$this->from_day   = 1;
		$this->to_year    = date( 'Y' );
		$this->to_month   = date( 'n' );
		$this->to_day     = date( 'j' );

		$from = '';
		$to   = '';
		if ( isset( $_POST['from'] ) && isset( $_POST['to'] ) ) {
			$from = sanitize_text_field( $_POST['from'] );
			$to   = sanitize_text_field( $_POST['to'] );
		}

		/** if selected custom date range */
		if ( ! empty( $from ) && ! empty( $to ) ) {
			$this->from_year  = date( 'Y', strtotime( $from ) );
			$this->from_month = date( 'n', strtotime( $from ) );
			$this->from_day   = date( 'j', strtotime( $from ) );
			$this->to_year    = date( 'Y', strtotime( $to ) );
			$this->to_month   = date( 'n', strtotime( $to ) );
			$this->to_day     = date( 'j', strtotime( $to ) );
		}

		$automation_meta = BWFAN_Model_Automationmeta::get_automation_meta( $automation_id );
		$records         = $this->get_event_records( $automation_meta );
		$count           = count( $records );

		$step = isset( $_POST['step'] ) ? intval( sanitize_text_field( $_POST['step'] ) ) : 0;
		if ( 1 === $step ) {
			/** User opt confirmation state */
			$resp = array(
				'status'       => true,
				'show_content' => true,
			);

			if ( 0 === $count ) {
				$message              = esc_html__( 'No Data found', 'wp-marketing-automations' );
				$resp['show_content'] = false;
				$resp['msg']          = $message;
				wp_send_json( $resp );
			}

			$message     = esc_html__( ' records found', 'wp-marketing-automations' );
			$message     = count( $records ) . $message;
			$message     .= esc_html__( '. Do you want to sync the records ?', 'wp-marketing-automations' );
			$resp['msg'] = $message;

			wp_send_json( $resp );
		} elseif ( 2 === $step ) {
			/** User opted yes, inserting automation entry */
			$data                            = [];
			$sync_data                       = [];
			$automation_data                 = BWFAN_Model_Automations::get_automation_with_data( $automation_id );
			$sync_data['automation_actions'] = BWFAN_Common::get_automation_selected_action_slugs( $automation_data, isset( $_POST['selected_actions'] ) ? $_POST['selected_actions'] : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$sync_data['automation_name']    = $automation_data['meta']['title'];
			$sync_data['automation_source']  = $automation_data['source'];
			$sync_data['automation_event']   = $automation_data['event'];
			$data['automation_id']           = $automation_id;
			$data['source']                  = isset( $_POST['source'] ) ? sanitize_text_field( $_POST['source'] ) : '';
			$data['event']                   = isset( $_POST['event'] ) ? sanitize_text_field( $_POST['event'] ) : '';
			$sync_data['date_from']          = $from;
			$sync_data['date_to']            = $to;

			/** Actions data handling for multiple records */
			$sync_data['actions'] = isset( $_POST['selected_actions'] ) ? $_POST['selected_actions'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput,WordPress.Security.NonceVerification
			if ( ! empty( $sync_data['actions'] ) ) {
				$modified_actions = [];
				foreach ( $_POST['selected_actions'] as $act ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput,WordPress.Security.NonceVerification
					if ( empty( $act ) ) {
						continue;
					}
					if ( false !== strpos( $act, ',' ) ) {
						$modified_actions = array_merge( $modified_actions, explode( ',', $act ) );
					} else {
						$modified_actions = array_merge( $modified_actions, [ $act ] );
					}
				}
				if ( count( $modified_actions ) > 0 ) {
					$sync_data['actions'] = $modified_actions;
				}
			}

			$sync_id         = $this->insert_sync_record( $automation_id, 0, 0, $count, $sync_data );
			$data['sync_id'] = $sync_id;

			if ( ! bwf_has_action_scheduled( 'bwfan_process_old_records_for_' . $this->get_slug(), $data ) ) {
				bwf_schedule_recurring_action( time(), 1, 'bwfan_process_old_records_for_' . $this->get_slug(), $data );
			}

			$resp = array(
				'status'       => true,
				'show_content' => true,
				'msg'          => esc_html__( 'Process Scheduled', 'wp-marketing-automations' ),
			);

			wp_send_json( $resp );
		}
		//phpcs:enable WordPress.Security.NonceVerification
	}

	/**
	 * Must be modified on child level
	 *
	 * @param $automation_meta
	 *
	 * @return array
	 */
	public function get_event_records( $automation_meta ) {
		return [];
	}

	/**
	 * Insert a single sync record in table.
	 *
	 * @param $automation_id
	 * @param $offset
	 * @param $processed
	 * @param $total
	 * @param $data
	 *
	 * @return int
	 */
	public function insert_sync_record( $automation_id, $offset, $processed, $total, $data ) {
		$new_data = array(
			'sync_date' => strtotime( current_time( 'mysql', 1 ) ),
			'a_id'      => $automation_id,
			'total'     => $total,
			'processed' => $processed,
			'offset'    => $offset,
			'status'    => 1,
			'sync_data' => wp_json_encode( $data ),
		);

		BWFAN_Model_Syncrecords::insert( $new_data );
		$sync_id = BWFAN_Model_Syncrecords::insert_id();

		return $sync_id;
	}

	/**
	 * Get old records of an event and make tasks for those records.
	 * Can be modified on child level
	 *
	 * @param $automation_id
	 * @param $source
	 * @param $event
	 * @param $sync_id
	 */
	public function sync_old_automation_records( $automation_id, $source, $event, $sync_id ) {
		BWFAN_Core()->logger->log( 'Sync #' . $sync_id . ' initiated, Source - ' . $source . ', Event - ' . $event . ', Automation ID - ' . $automation_id, 'sync' );
		$this->sync_automation_id = $automation_id;
		$this->sync_source        = $source;
		$this->sync_event         = $event;
		$this->sync_id            = $sync_id;
		$sync_process_details     = BWFAN_Model_Syncrecords::get( $sync_id );

		if ( ! is_array( $sync_process_details ) || 0 === count( $sync_process_details ) ) {
			BWFAN_Core()->logger->log( 'Sync #' . $sync_id . ' some data missing or not properly scheduled', 'sync' );

			return;
		}

		$is_completed = $sync_process_details['status'];
		if ( 2 === intval( $is_completed ) ) {
			$this->remove_recurring_action( $automation_id, $source, $event, $sync_id );
			BWFAN_Core()->logger->log( 'Sync #' . $sync_id . ' status completed', 'sync' );

			return;
		}

		$sync_data           = json_decode( $sync_process_details['sync_data'], true );
		$date_from           = $sync_data['date_from'];
		$date_to             = $sync_data['date_to'];
		$selected_actions    = $sync_data['actions'];
		$automation_meta     = BWFAN_Model_Automationmeta::get_automation_meta( $automation_id );
		$this->display_count = apply_filters( 'bwfan_past_syncing_batch_size', 50 );
		$this->page          = 1;
		$this->offset        = $sync_process_details['offset'];
		$this->processed     = $sync_process_details['processed'];

		if ( empty( $this->offset ) ) {
			$this->offset = 0;
		}

		BWFAN_Core()->logger->log( 'Sync #' . $sync_id . ' offset is ' . $this->offset, 'sync' );

		$this->from_year  = date( 'Y', strtotime( $date_from ) );
		$this->from_month = date( 'n', strtotime( $date_from ) );
		$this->from_day   = date( 'j', strtotime( $date_from ) );
		$this->to_year    = date( 'Y', strtotime( $date_to ) );
		$this->to_month   = date( 'n', strtotime( $date_to ) );
		$this->to_day     = date( 'j', strtotime( $date_to ) );
		$records          = $this->get_event_records( $automation_meta );

		if ( ! is_array( $records ) || 0 === count( $records ) ) {

			/** unschedule the recurring wp event. */
			$this->stop_current_sync_process( 2 );
			BWFAN_Core()->logger->log( 'Sync #' . $sync_id . '. No records found', 'sync' );

			return;
		}

		BWFAN_Core()->logger->log( 'Sync #' . $sync_id . '. Found records (object ids) ' . print_r( $records, true ), 'sync' ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions

		$this->sync_start_time = time();
		$db_timestamp          = intval( get_option( 'bwfan_synced_start_time' ), time() );
		if ( $this->sync_start_time < $db_timestamp ) {
			$this->sync_start_time = $db_timestamp;
		}

		BWFAN_Core()->automations->current_automation_id = $automation_id;

		BWFAN_Core()->public->load_active_automations( $this->get_slug() );
		$all_automations             = BWFAN_Core()->sources->get_event( $event )->get_automations_data();
		$automation_data             = $all_automations[ $automation_id ];
		$automation_data             = BWFAN_Common::filter_actions_conditions( $selected_actions, $automation_data );
		$this->user_selected_actions = $automation_data['actions'];

		$this->process_event_records( $records );

		update_option( 'bwfan_synced_start_time', $this->sync_start_time );
	}

	public function check_if_bulk_process_executing( $should_logs_made ) {
		if ( is_array( $this->user_selected_actions ) && count( $this->user_selected_actions ) > 0 ) {
			return false;
		}

		return $should_logs_made;
	}

	/**
	 * Helper method to remove recurring action
	 *
	 * @param $automation_id
	 * @param $source
	 * @param $event
	 * @param $sync_id
	 */
	public function remove_recurring_action( $automation_id, $source, $event, $sync_id ) {
		$data = array(
			'automation_id' => intval( $automation_id ),
			'source'        => $source,
			'event'         => $event,
			'sync_id'       => $sync_id,
		);
		bwf_unschedule_actions( 'bwfan_process_old_records_for_' . $this->get_slug(), $data );
		$data = array(
			'status' => 2,
		);
		$this->update_sync_record( $sync_id, $data );
	}

	/**
	 * Update sync records in the table
	 *
	 * @param $sync_id
	 * @param $data
	 */
	public function update_sync_record( $sync_id, $data ) {
		$where = array(
			'ID' => $sync_id,
		);
		BWFAN_Model_Syncrecords::update( $data, $where );
	}

	/**
	 * Update the migration status and remove the recurring wp event.
	 */
	public function stop_current_sync_process( $status ) {
		$automation_id = $this->sync_automation_id;
		$source        = $this->sync_source;
		$event         = $this->sync_event;
		$sync_id       = $this->sync_id;

		// unschedule the recurring wp event.
		$data = array(
			'automation_id' => intval( $automation_id ),
			'source'        => $source,
			'event'         => $event,
			'sync_id'       => $sync_id,
		);
		bwf_unschedule_actions( 'bwfan_process_old_records_for_' . $this->get_slug(), $data );

		$data = array(
			'status' => $status,
		);
		$this->update_sync_record( $sync_id, $data );
	}

	/**
	 * Create tasks of the actions.
	 *
	 * @param $automation_id
	 * @param $actions
	 * @param $event_data
	 * @param $data
	 */
	public function create_tasks( $automation_id, $actions, $event_data, $data ) {
		$global_settings = BWFAN_Common::get_global_settings();
		if ( 1 === intval( $global_settings['bwfan_sandbox_mode'] ) || ( defined( 'BWFAN_SANDBOX_MODE' ) && true === BWFAN_SANDBOX_MODE ) ) {
			return;
		}

		$a_track_id       = $this->automations_arr[ $automation_id ]['a_track_id'];
		$total_tasks_made = [];

		/** Set user id if available */
		if ( ! isset( $data['global']['user_id'] ) && isset( BWFAN_Common::$events_async_data['user_id'] ) ) {
			$data['global']['user_id'] = absint( BWFAN_Common::$events_async_data['user_id'] );
		}


		/** Set language if available */
		if ( ! isset( $data['global']['language'] ) && isset( BWFAN_Common::$events_async_data['language'] ) ) {
			$data['global']['language'] = BWFAN_Common::$events_async_data['language'];
		}

		/** Set user_id, if email is available */
		if ( ( ! isset( $data['global']['user_id'] ) || empty( $data['global']['user_id'] ) ) && is_email( $data['global']['email'] ) ) {
		    $user = get_user_by( 'email', $data['global']['email'] );
		    if( $user instanceof WP_User ) {
		        $data['global']['user_id'] = $user->ID;
		    }

		    /** @var get $contact for all event if there email id available*/
		    $user_id = isset($data['global']['user_id'])?$data['global']['user_id']:null;
		    $contact = bwf_get_contact( $user_id, $data['global']['email']  );
            if ( $contact instanceof WooFunnels_Contact && absint( $contact->get_id() ) > 0 ) {
                $data['global']['contact_id'] = $contact->get_id();
                $data['global']['cid'] = $contact->get_id();
            }
		}

		/** Set Phone if User ID is available */
		if( isset( $data['global']['user_id'] ) && ! empty( $data['global']['user_id'] ) && ( ! isset( $data['global']['phone'] ) || empty( $data['global']['phone'] ) ) ) {
            $phone = get_user_meta( $data['global']['user_id'], 'billing_phone', true );
            if( ! empty( $phone ) ) {
                $country = get_user_meta( $data['global']['user_id'], 'billing_country', true );
                if( ! empty( $country ) ) {
                    $phone = BWFAN_Phone_Numbers::add_country_code( $phone, $country );
                }
                $data['global']['phone'] = $phone;
            }
		}

		/** Set Phone if no User ID is set, but email is set */
		if ( is_email( $data['global']['email'] ) && ( ! isset( $data['global']['phone'] ) || empty( $data['global']['phone'] ) ) ) {
            $order = BWFAN_Common::get_latest_order_by_email( $data['global']['email'] );
            if ( $order instanceof WC_Order ) {
                $phone = $order->get_billing_phone();
                if ( ! empty( $phone ) ) {
                    $country = $order->get_billing_country();
                    if ( ! empty( $country ) ) {
                        $phone = BWFAN_Phone_Numbers::add_country_code( $phone, $country );
                    }
                    $data['global']['phone'] = $phone;
                }
            }
		}

		do_action( 'bwfan_before_creating_tasks', $automation_id, $actions, $event_data, $data );

		foreach ( $actions as $index => $action ) {
			$should_task_create = $this->should_task_create( $action, $data );

			if ( false === $should_task_create ) {
				continue;
			}
			// Task data can be modified before making a task
			$action  = apply_filters( 'bwfan_pre_insert_task', $action, $automation_id, $this );
			$task_id = BWFAN_Core()->tasks->insert_task( $automation_id, $action, $this );

			if ( isset( $action['data_meta'] ) ) {
				$data['data_meta'] = $action['data_meta'];
			}

			$data['event_data'] = $event_data;
			$data['data']       = ( isset( $action['data'] ) ) ? $action['data'] : array();
			$data['group_id']   = $action['group_id'];
			$data['action_id']  = $action['action_id'];
			$data               = apply_filters( 'bwfan_alter_taskdata', $data );
			$data               = apply_filters( 'bwfan_alter_taskdata_' . $this->get_slug(), $data );
			BWFAN_Core()->tasks->insert_taskmeta( $task_id, 'integration_data', $data );

			// Unique task tracking id
			$t_track_id = $a_track_id . '_' . $action['group_id'] . '_' . $action['action_id'];
			BWFAN_Core()->tasks->insert_taskmeta( $task_id, 't_track_id', $t_track_id );

			// Save sync id if the task is created via sync process
			if ( ! empty( $this->sync_id ) ) {
				BWFAN_Core()->tasks->insert_taskmeta( $task_id, 'sync_id', $this->sync_id );
			}

			// Actions can be performed after task in inserted into db
			do_action( 'bwfan_task_created', $index, $task_id );
			do_action( 'bwfan_task_created_' . $this->get_slug(), $index, $task_id );

			$total_tasks_made[] = $task_id;
		}

		do_action( 'bwfan_after_creating_tasks', $automation_id, $actions, $event_data, $data, $total_tasks_made );

		// Increase the automation run count and fire contact creation async call only when async process in not running
		if ( empty( $this->user_selected_actions ) ) {
			BWFAN_Core()->automations->update_automation_run_count( $automation_id );

			if ( $this->track_automation_run ) {
				// Send an async call for updating contact meta
				$this->send_async_contact_call( $automation_id );
			}
		}

		BWFAN_Core()->logger->log( 'Total tasks made = ' . count( $total_tasks_made ) . '. Event - ' . $this->get_slug() . ', Task IDs -' . print_r( $total_tasks_made, true ), $this->log_type ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}

	/**
	 * Check if task for an action should be created or not.
	 *
	 * @param $action_data
	 *
	 * @return bool
	 */
	public function should_task_create( $action_data, $data ) {
		if ( ! isset( $action_data['integration_slug'] ) ) {
			return false;
		}

		$action_instance   = BWFAN_Core()->integration->get_action( $action_data['action_slug'] );

		if ( ! $action_instance instanceof BWFAN_Action ) {
			return false;
		}

		$check_action_data = $action_instance->check_required_data( $data );

		if ( false === $check_action_data ) {
			return false;
		}

		$check_language_support = $this->check_language_support( $action_data, $data );
		if ( false === $check_language_support ) {
			return false;
		}

		return true;
	}

	public function check_language_support( $action_data, $data ) {
		/** checking for language plugin **/
		if(! function_exists( 'icl_get_languages' ) && !function_exists('pll_the_languages') && !bwfan_is_translatepress_active()){
			return true;
		}

		if ( false === $this->support_lang || ! isset( $action_data['language'] ) || ! isset( $action_data['language']['enable_lang'] ) || 1 !== absint( $action_data['language']['enable_lang'] ) && ! isset( $action_data['language']['lang'] ) || empty( $action_data['language']['lang'] ) ) {
			return true;
		}

		$selected_lang = $action_data['language']['lang'];
		$lang          = $this->get_language_from_event( $data );
		if ( $lang === $selected_lang ) {
			return true;
		}

		return false;
	}

	public function get_language_from_event( $data ) {
		$lang  = '';
		$order = isset( $data['global']['wc_order'] ) ? $data['global']['wc_order'] : '';
		if ( ! $order instanceof WC_Order && isset( $data['global']['order_id'] ) ) {
			$order = wc_get_order( $data['global']['order_id'] );
		}
	
		if ( $order instanceof WC_Order ) {

			if(isset($data['global']['language']) && !empty($data['global']['language'])){
				$lang = $data['global']['language'];
			}			
			
		} elseif ( isset( $data['global']['cart_abandoned_id'] ) ) {
			$cart_details  = BWFAN_Model_Abandonedcarts::get( $data['global']['cart_abandoned_id'] );
			$checkout_data = json_decode( $cart_details['checkout_data'], true );
			if ( isset( $checkout_data['lang'] ) ) {
				$lang = $checkout_data['lang'];
			}
		}

		return $lang;
	}

	/**
	 * Send async call for updating the contact automation details
	 *
	 * @param $automation_id
	 */
	public function send_async_contact_call( $automation_id ) {
		$email = $this->get_email_event();
		if ( false === $email ) {
			BWFAN_Core()->logger->log( $this->error_message . '. Automation ID - ' . $automation_id . '. Event - ' . $this->get_slug(), $this->log_type );

			return;
		}

		$user_id = $this->get_user_id_event();
		$url  = rest_url('/autonami/v1/update-contact-automation');
		$body_data = array(
				'automation_id' => $automation_id,
				'email'         => $email,
				'user_id'       => $user_id,
				'unique_key'    => get_option( 'bwfan_u_key', false ),
			);
		$args = bwf_get_remote_rest_args( $body_data );

		wp_remote_post( $url, $args );
	}

	/**
	 * If any event has email and it does not contain order object, then following method must be overridden by child event class.
	 * Return email
	 * @return bool
	 */
	public function get_email_event() {
		$order = $this->order;
		if ( $order instanceof WC_Order ) {
			return $order->get_billing_email();
		}

        $this->error_message = __( 'Not a valid WC order object', 'wp-marketing-automations' );
        $email               = false;

		return $email;
	}

	/**
	 * If any event has user id and it does not contain order object, then following method must be overridden by child event class.
	 * Return user id
	 * @return bool
	 */
	public function get_user_id_event() {
		$order = $this->order;
		if ( $order instanceof WC_Order ) {
			return $order->get_user_id();
		}

        $this->error_message = __( 'Not a valid WC order object', 'wp-marketing-automations' );
        $user_id             = false;

		return $user_id;
	}

	/**
	 * Calculate actions time based on record date.
	 *
	 * @param $actions
	 * @param $record_date DateTime
	 *
	 * @return mixed
	 * @throws Exception
	 */

	public function calculate_actions_time( $actions, $record_date ) {
		BWFAN_Common::convert_from_gmt( $record_date ); // convert to site time
		BWFAN_Common::convert_to_gmt( $record_date );

		$record_date_timestamp = $record_date->getTimestamp();
		$current_timestamp     = strtotime( current_time( 'mysql', 1 ) );
		$datetime1             = new DateTime( date( 'Y-m-d H:i:s', $record_date_timestamp ) );//start time
		$datetime2             = new DateTime( date( 'Y-m-d H:i:s', $current_timestamp ) );//end time
		$interval              = $datetime1->diff( $datetime2 );
		$days_difference       = intval( $interval->format( '%a' ) );
		$hours_difference      = intval( $interval->format( '%h' ) );
		$minutes_difference    = intval( $interval->format( '%i' ) );

		foreach ( $actions as $action_index => $action_details ) {
			$delay_type = $action_details['time']['delay_type'];

			if ( 'fixed' === $delay_type && isset( $action_details['time']['fixed_date'] ) && ! empty( $action_details['time']['fixed_date'] ) ) {
				$fixed_date            = $action_details['time']['fixed_date'];
				$fixed_date_timestamp  = strtotime( $fixed_date );
				$fixed_time            = $action_details['time']['fixed_time'];
				$fixed_time_seconds    = BWFAN_Common::get_seconds_from_time_format( $fixed_time );
				$task_actual_timestamp = intval( $fixed_date_timestamp ) + intval( $fixed_time_seconds );

				if ( $task_actual_timestamp < $current_timestamp ) {
					$actions[ $action_index ]['time']['delay_type'] = 'immediately';
				}
			}

			if ( 'after_delay' === $delay_type ) {
				$time_type              = $action_details['time']['time_type'];
				$time_number            = intval( $action_details['time']['time_number'] );
				$new_time_number        = $time_number;
				$time_increament_string = '';

				if ( 'days' === $time_type ) {
					if ( $time_number > $days_difference ) {
						$new_time_number = $time_number - $days_difference;
					}

					$time_increament_string = '+' . $new_time_number . ' days';
				} elseif ( 'hours' === $time_type ) {
					if ( $time_number > $hours_difference ) {
						$new_time_number = $time_number - $hours_difference;
					}

					$time_increament_string = '+' . $new_time_number . ' hours';
				} elseif ( 'minutes' === $time_type ) {
					if ( $time_number > $minutes_difference ) {
						$new_time_number = $time_number - $minutes_difference;
					}

					$time_increament_string = '+' . $new_time_number . ' minutes';
				}

				$timestamp = strtotime( $time_increament_string, $record_date_timestamp );
				if ( $timestamp > $current_timestamp ) {
					$actions[ $action_index ]['time']['time_number'] = $new_time_number;
				} else {
					$actions[ $action_index ]['time']['delay_type'] = 'immediately';
				}
			}
		}

		return $actions;
	}

	public function get_view( $db_eventmeta_saved_value ) {

	}

	public function get_default_data() {
		//Use in every event
		$source = $this->source_type;

		$data = array(
			'source' => $source,
			'event'  => $this->get_slug(),
		);

		return $data;
	}

	/**
	 * Send the event data to endpoint for processing
	 *
	 * @param $data
	 *
	 * @return array|void|WP_Error
	 */
	public function send_async_call( $data ) {
		$should_fire_call = $this->get_current_event_automations();

		/** In case of Form Submission, Proceed the send_async_call, to trigger the CRM's Form feed */
		$form_submit_events = BWFAN_Common::get_form_submit_events();
		$is_form_submission = is_array( $form_submit_events ) && in_array( get_class( $this ), $form_submit_events );

		if ( false === $should_fire_call && false === $is_form_submission ) {
			BWFAN_Core()->logger->log( 'No automations found for event ' . $this->get_slug() . ', Data - ' . print_r( $data, true ), $this->log_type ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions

			return;
		}

		if( true === $is_form_submission ) {
			$data[ 'is_form_submission' ] = 1;
		}

		$lifecycle_automation_id = BWFAN_Core()->automations->current_lifecycle_automation_id;
		if( ! empty( $lifecycle_automation_id ) ) {
			$data['aid'] = $lifecycle_automation_id;
		}

		$data['unique_key'] = get_option( 'bwfan_u_key', false );
		$url                = rest_url('/autonami/v1/events');
		$data = apply_filters('bwfan_send_async_call_data',$data);

		$args = bwf_get_remote_rest_args( $data );

		return wp_remote_post( $url, $args );
	}

	/**
	 * Check if any active automation for current event is present
	 *
	 * @return array|bool
	 */
	public function get_current_event_automations() {
		BWFAN_Core()->public->load_active_automations( $this->get_slug() );
		if ( ! is_array( $this->automations_arr ) || count( $this->automations_arr ) === 0 ) {
			return false;
		}

		$automation_ids = [];
		foreach ( $this->automations_arr as $automation_id => $value1 ) {
			if ( $this->get_slug() !== $value1['event'] || 0 !== intval( $value1['requires_update'] ) ) {
				continue;
			}
			$automation_ids[] = $automation_id;
		}

		/** If there are no automations for current event, then return false */
		if ( 0 === count( $automation_ids ) ) {
			return false;
		}

		return $automation_ids;
	}

	public function get_task_view( $global_data ) {
		return '';
	}

	/**
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$wc_order_id = BWFAN_Merge_Tag_Loader::get_data( 'wc_order_id' );
		if ( empty( $wc_order_id ) || $wc_order_id !== $task_meta['global']['order_id'] ) {
			$set_data = array(
				'wc_order_id' => $task_meta['global']['order_id'],
				'email'       => $task_meta['global']['email'],
				'wc_order'    => wc_get_order( $task_meta['global']['order_id'] ),
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
	}

	public function get_source() {
		return $this->source_type;
	}

	public function get_optgroup_label() {
		return $this->optgroup_label;
	}

	public function get_name() {
		return $this->event_name;
	}

	public function get_desc() {
		return $this->event_desc;
	}

	public function get_priority() {
		return $this->priority;
	}

	public function is_syncable() {
		return $this->is_syncable;
	}

	public function get_included_actions() {
		return $this->included_actions;
	}

	public function get_excluded_actions() {
		return $this->excluded_actions;
	}

	public function set_event_saved_data( $data ) {
		$this->event_saved_data = $data;
	}

	public function is_time_independent() {
		return $this->is_time_independent;
	}

	/**
	 * Return localize data of event for frontend UI
	 * @return array
	 */
	public function get_localize_data() {
		$this->localize_data = [
			'source_type'         => $this->source_type,
			'event_name'          => $this->event_name,
			'event_desc'          => $this->event_desc,
			'slug'                => $this->get_slug(),
			'is_time_independent' => $this->is_time_independent,
			'included_actions'    => $this->included_actions,
			'excluded_actions'    => $this->excluded_actions,
			'event_saved_data'    => $this->event_saved_data,
			'support_lang'        => $this->support_lang,
			'customer_email_tag'  => $this->customer_email_tag,
			'available'           => 'yes',
		];

		return apply_filters( 'bwfan_event_' . $this->get_slug() . '_localize_data', $this->localize_data, $this );
	}

	/**
	 * Return Available event rule group
	 * @return array
	 */
	public function get_rule_group() {
		return apply_filters( 'bwfan_event_' . $this->get_slug() . '_rules_group', $this->event_rule_groups, $this );
	}

	/**
	 * Return Available event Merge tag group
	 * @return array
	 */
	public function get_merge_tag_groups() {
		if ( true === $this->support_lang ) {
			$this->event_merge_tag_groups[] = 'user_language';
		}

		return apply_filters( 'bwfan_event_' . $this->get_slug() . '_merge_tag_group', $this->event_merge_tag_groups, $this );
	}

	public function get_sync_start_time() {
		return $this->sync_start_time;
	}

	/**
	 * Return user selected actions against this event
	 * @return null
	 */
	public function get_user_selected_actions() {
		return $this->user_selected_actions;
	}

	public function get_automations_data() {
		return $this->automations_arr;
	}

	public function set_automations_data( $data ) {
		if ( ! empty( $data ) ) {
			$this->automations_arr = $data;
		}
	}

	public function make_task_data( $automation_id, $automation_data ) {

	}

	public function set_source_type( $type ) {
		$this->source_type = $type;
	}

	public function capture_async_data() {
		throw new ErrorException( 'This function `' . __FUNCTION__ . '` Must be override in child class' );
	}

	/**
	 * to avoid unserialize of the current class
	 */
	public function __wakeup() {
		throw new ErrorException( 'BWFAN_Core can`t converted to string' );
	}

	/**
	 * to avoid serialize of the current class
	 */
	public function __sleep() {
		throw new ErrorException( 'BWFAN_Core can`t converted to string' );
	}

	protected function validate_order( $data ) {
		if ( ! isset( $data['order_id'] ) ) {
			return false;
		}

		$order = wc_get_order( $data['order_id'] );
		if ( $order instanceof WC_Order ) {
			return true;
		}

		return false;
	}

	protected function validate_subscription( $data ) {
		if ( ! isset( $data['wc_subscription_id'] ) || ! function_exists( 'wcs_get_subscription' ) ) {
			return false;
		}

		$subscription = wcs_get_subscription( $data['wc_subscription_id'] );
		if ( $subscription instanceof WC_Subscription ) {
			return true;
		}

		return false;
	}

	/**
	 * To avoid cloning of current class
	 */
	protected function __clone() {
	}

}
