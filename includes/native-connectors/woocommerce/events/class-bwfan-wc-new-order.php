<?php

final class BWFAN_WC_New_Order extends BWFAN_Event {
	private static $instance = null;

	// Environment variables for current event
	public $order_id = null;
	/** @var WC_Order $order */
	public $order = null;
	public $to_status = null;

	private function __construct() {
		$this->is_syncable            = true;
		$this->optgroup_label         = esc_html__( 'Orders', 'wp-marketing-automations' );
		$this->event_name             = esc_html__( 'Order Created', 'wp-marketing-automations' );
		$this->event_desc             = esc_html__( 'This event runs after a new WooCommerce order is created. Can only run once on selected WC order statuses.', 'wp-marketing-automations' );
		$this->event_merge_tag_groups = array( 'wc_customer', 'wc_order' );
		$this->event_rule_groups      = array( 'wc_order', 'wc_customer', 'automation', 'aerocheckout' );
		$this->priority               = 15;
		$this->support_lang           = true;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		// actual event trigger hooks
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'new_order' ), 11, 3 );
		add_action( 'woocommerce_rest_insert_shop_order_object', array( $this, 'new_order_by_rest' ), 10, 3 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'order_status_changed' ), 11, 3 );

		// this action runs when sync process is going on for the current event. shouldn't be dynamic
		add_action( 'bwfan_process_old_records_for_wc_new_order', array( $this, 'sync_old_automation_records' ), 10, 4 );

		// this action localizes the data which will be used in script template for making the UI of the event
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );

		add_filter( 'bwfan_wc_event_order_status_' . $this->get_slug(), array( $this, 'modify_order_statuses' ), 10, 1 );

		// this filter tells if the logs should be made during sync process for the current event
		add_filter( 'bwfan_before_making_logs', array( $this, 'check_if_bulk_process_executing' ), 10, 1 );

		add_action( 'bwfan_sync_call_delete_tasks', array( $this, 'terminate_automations_tasks' ), 10, 2 );
	}

	/**
	 * Localize data for html fields for the current event.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$integration_data = $this->get_view_data();

			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'order_status_options', $integration_data );
		}
	}

	public function get_view_data() {
		$all_status = wc_get_order_statuses();
		if ( isset( $all_status['wc-cancelled'] ) ) {
			unset( $all_status['wc-cancelled'] );
		}
		if ( isset( $all_status['wc-failed'] ) ) {
			unset( $all_status['wc-failed'] );
		}
		if ( isset( $all_status['wc-refunded'] ) ) {
			unset( $all_status['wc-refunded'] );
		}
		if ( isset( $all_status['wc-wfocu-pri-order'] ) ) {
			unset( $all_status['wc-wfocu-pri-order'] );
		}
		asort( $all_status, SORT_REGULAR );

		$all_status = apply_filters( 'bwfan_wc_event_order_status_' . $this->get_slug(), $all_status );
		$all_status = apply_filters( 'bwfan_wc_event_order_status', $all_status );

		return $all_status;
	}

	/**
	 * Show the html fields for the current event.
	 */
	public function get_view( $db_eventmeta_saved_value ) {
		?>
        <script type="text/html" id="tmpl-event-<?php echo esc_attr( $this->get_slug() ); ?>">
            <div class="bwfan-col-sm-12 bwfan-p-0 bwfan-mt-15">
                <#
                selected_statuses = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'order_status')) ? data.eventSavedData.order_status : '';
                is_validated = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'validate_event')) ? 'checked' : '';
                terminate_on_order = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'terminate_on_order')) ? 'checked' : '';
                #>
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Select Order Statuses', 'wp-marketing-automations' ); ?></label>
                <#
                if(_.has(data.eventFieldsOptions, 'order_status_options') && _.isObject(data.eventFieldsOptions.order_status_options) ) {
                _.each( data.eventFieldsOptions.order_status_options, function( value, key ){
                checked = '';
                if(selected_statuses!='' && _.contains(selected_statuses, key)){
                checked = 'checked';
                }
                #>
                <div class="bwfan-checkboxes">
                    <input type="checkbox" name="event_meta[order_status][]" id="bwfan-{{key}}" value="{{key}}" class="bwfan-checkbox" data-warning="<?php echo esc_html__( 'Please select atleast 1 order status', 'wp-marketing-automations' ); ?>" {{checked}}/>
                    <label for="bwfan-{{key}}" class="bwfan-checkbox-label">{{value}}</label>
                </div>
                <# })
                }
                #>
                <div class="clearfix bwfan_field_desc bwfan-pt-0">
                    This automation would run on new orders with selected statuses.
                </div>
            </div>
            <#
            if(1 == bwfanParams.pro_active){
            #>
            <div class="bwfan-col-sm-12 bwfan-p-0 bwfan-mt-15">
                <label class="bwfan-label-title">End Automation</label>
                <div>
                    <input type="checkbox" name="event_meta[terminate_on_order]" id="bwfan_end_automation" value="1" {{terminate_on_order}}/>
                    <label for="bwfan_end_automation" class="bwfan-checkbox-label"><?php esc_html_e( 'End automation if customer places the order during the automation' ) ?></label>
                </div>

            </div>
            <#
            }
            #>
			<?php
			$this->get_validation_html( $this->get_slug(), 'Validate Order status before executing task', 'Validate' );
			?>
        </script>
		<?php
	}

	public function get_email_event() {
		if ( $this->order instanceof WC_Order ) {
			return $this->order->get_billing_email();
		}

		if ( ! empty( absint( $this->order_id ) ) ) {
			$order = wc_get_order( absint( $this->order_id ) );

			return false === $order ? $order->get_billing_email() : false;
		}

		return false;
	}

	public function get_user_id_event() {
		if ( $this->order instanceof WC_Order ) {
			return $this->order->get_user_id();
		}

		if ( ! empty( absint( $this->order_id ) ) ) {
			$order = wc_get_order( absint( $this->order_id ) );

			return false === $order ? $order->get_user_id() : false;
		}

		return false;
	}

	/**
	 * Set up rules data
	 *
	 * @param $automation_data
	 */
	public function pre_executable_actions( $automation_data ) {
		BWFAN_Core()->rules->setRulesData( $this->event_automation_id, 'automation_id' );
		BWFAN_Core()->rules->setRulesData( $this->order, 'wc_order' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->order->get_billing_email(), $this->order->get_user_id() ), 'bwf_customer' );
	}

	/**
	 * @param $order WC_Order
	 * @param $request
	 * @param $order_created bool
	 */
	public function new_order_by_rest( $order, $request, $order_created ) {
		if ( ! $order_created ) {
			return;
		}
		$order_id = $order->get_id();
		$this->new_order( $order_id, [], $order );
	}

	/**
	 * @param $order_id
	 * @param $posted_data
	 * @param $order WC_Order
	 */
	public function new_order( $order_id, $posted_data = [], $order ) {
		$automations_for_current_event = $this->get_current_event_automations();

		if ( ! is_array( $automations_for_current_event ) || count( $automations_for_current_event ) === 0 ) {
			BWFAN_Core()->logger->log( 'No active automations for order ID - ' . $order_id . ', Event - ' . $this->get_slug() . ' and function name ' . __FUNCTION__, $this->log_type );

			return;
		}

		update_post_meta( $order_id, '_bwfan_' . $this->get_slug(), $automations_for_current_event );
	}

	/**
	 * Returns the current event settings set in the automation at the time of task creation.
	 *
	 * @param $value
	 *
	 * @return array
	 */
	public function get_automation_event_data( $value ) {
		$event_data = [
			'event_source'   => $value['source'],
			'event_slug'     => $value['event'],
			'validate_event' => ( isset( $value['event_meta']['validate_event'] ) ) ? 1 : 0,
			'order_status'   => $value['event_meta']['order_status']
		];

		return $event_data;
	}

	/**
	 * Registers the tasks for current event.
	 *
	 * @param $automation_id
	 * @param $integration_data
	 * @param $event_data
	 */
	public function register_tasks( $automation_id, $integration_data, $event_data ) {
		if ( ! is_array( $integration_data ) ) {
			return;
		}

		$data_to_send = $this->get_event_data();
		$this->create_tasks( $automation_id, $integration_data, $event_data, $data_to_send );
	}

	public function get_event_data() {
		$data_to_send                       = [];
		$data_to_send['global']['order_id'] = $this->order_id;
		$this->order                        = $this->order instanceof WC_Order ? $this->order : wc_get_order( $this->order_id );
		$data_to_send['global']['email']    = $this->order instanceof WC_Order ? $this->order->get_billing_email() : '';
		$data_to_send['global']['phone']    = $this->order instanceof WC_Order ? $this->order->get_billing_phone() : '';

		return $data_to_send;
	}

	public function terminate_automations_tasks( $email, $phone ) {
		if ( empty( $email ) && empty( $phone ) ) {
			return;
		}

		$automations = BWFAN_Core()->automations->get_active_automations();
		$event_slug  = $this->get_slug();

		$selected_automations = array();
		foreach ( $automations as $automation_id => $automation ) {
			if ( $event_slug === $automation['event'] && isset( $automation['meta']['event_meta']['terminate_on_order'] ) && $automation['meta']['event_meta']['terminate_on_order'] ) {
				$selected_automations[] = $automation_id;
			}
		}

		$schedule_tasks = [];

		if ( ! empty( $email ) ) {
			$schedule_tasks_email = BWFAN_Common::get_schedule_task_by_email( $selected_automations, $email );
			$schedule_tasks       = array_merge( $schedule_tasks, $schedule_tasks_email );
		}

		if ( ! empty( $phone ) ) {
			$schedule_tasks_phone = BWFAN_Common::get_schedule_task_by_phone( $selected_automations, $phone );
			$schedule_tasks       = array_merge( $schedule_tasks, $schedule_tasks_phone );
		}

		$schedule_tasks = array_filter( $schedule_tasks );

		if ( 0 === count( $schedule_tasks ) ) {
			return;
		}

		$schedule_tasks = array_unique( $schedule_tasks );

		foreach ( $schedule_tasks as $automation_id => $tasks ) {
			if ( empty( $tasks ) ) {
				continue;
			}
			$delete_tasks = array();
			foreach ( $tasks as $task ) {
				$delete_tasks[] = $task['ID'];
			}

			BWFAN_Core()->tasks->delete_tasks( $delete_tasks );
		}
	}

	public function order_status_changed( $order_id, $from_status, $to_status ) {
		$automations_for_current_event = $this->get_current_event_automations();
		$to_status                     = 'wc-' . $to_status;

		if ( ! is_array( $automations_for_current_event ) || count( $automations_for_current_event ) === 0 ) {
			return;
		}

		// Check if tasks for this order should be made or not
		$order_meta = BWFAN_Woocommerce_Compatibility::get_order_meta( $order_id, '_bwfan_' . $this->get_slug() );
		if ( empty( $order_meta ) ) {
			return;
		}

		$this->to_status = $to_status;
		$this->process( $order_id );
		$this->to_status = null;
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $order_id
	 */
	public function process( $order_id ) {
		$data             = $this->get_default_data();
		$data['order_id'] = $order_id;

		if ( ! is_null( $this->to_status ) ) {
			$data['to_status'] = $this->to_status;
		}

		$this->send_async_call( $data );
	}

	/**
	 * Make the view data for the current event which will be shown in task listing screen.
	 *
	 * @param $global_data
	 *
	 * @return false|string
	 */
	public function get_task_view( $global_data ) {
		ob_start();
		$order = wc_get_order( $global_data['order_id'] );
		if ( $order instanceof WC_Order ) {
			?>
            <li>
                <strong><?php echo esc_html__( 'Order:', 'wp-marketing-automations' ); ?> </strong>
                <a target="_blank" href="<?php echo get_edit_post_link( $global_data['order_id'] ); //phpcs:ignore WordPress.Security.EscapeOutput
				?>"><?php echo '#' . esc_attr( $global_data['order_id'] . ' ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?></a>
            </li>
		<?php } ?>
        <li>
            <strong><?php echo esc_html__( 'Email:', 'wp-marketing-automations' ); ?> </strong>
			<?php echo esc_html( $global_data['email'] ); ?>
        </li>
		<?php
		return ob_get_clean();
	}

	/**
	 * This function decides if the task has to be executed or not.
	 * The event has validate checkbox in its meta fields.
	 *
	 * @param $task_details
	 *
	 * @return array|mixed
	 */
	public function validate_event( $task_details ) {
		$result                                     = [];
		$task_event                                 = $task_details['event_data']['event_slug'];
		$automation_id                              = $task_details['processed_data']['automation_id'];
		$automation_details                         = BWFAN_Model_Automations::get( $automation_id );
		$current_automation_event                   = $automation_details['event'];
		$current_automation_event_meta              = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'event_meta' );
		$current_automation_event_validation_status = ( isset( $current_automation_event_meta['validate_event'] ) ) ? $current_automation_event_meta['validate_event'] : 0;
		$current_automation_order_statuses          = $current_automation_event_meta['order_status'];

		// Current automation has no checking
		if ( 0 === $current_automation_event_validation_status ) {
			$result = $this->get_automation_event_validation();

			return $result;
		}

		// Current automation event does not match with the event of task when the task was made
		if ( $task_event !== $current_automation_event ) {
			$result = $this->get_automation_event_status();

			return $result;
		}

		$order_id          = $task_details['processed_data']['order_id'];
		$order             = wc_get_order( $order_id );
		$task_order_status = BWFAN_Woocommerce_Compatibility::get_order_status( $order );

		if ( in_array( $task_order_status, $current_automation_order_statuses, true ) ) {
			$result = $this->get_automation_event_success();

			return $result;
		}

		$result['status']  = 4;
		$result['message'] = esc_html__( 'Order status in automation has been changed', 'wp-marketing-automations' );

		return $result;
	}

	public function validate_event_data_before_executing_task( $data ) {
		return $this->validate_order( $data );
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$order_id = BWFAN_Common::$events_async_data['order_id'];
		if ( isset( BWFAN_Common::$events_async_data['to_status'] ) ) {
			$this->to_status = BWFAN_Common::$events_async_data['to_status'];
		}
		$this->order_id                         = $order_id;
		$order                                  = wc_get_order( $order_id );
		$this->automations_for_current_event_db = BWFAN_Woocommerce_Compatibility::get_order_data( $order, '_bwfan_' . $this->get_slug() );
		delete_post_meta( $order_id, '_bwfan_' . $this->get_slug() );
		$this->order = $order;

		return $this->run_automations();
	}

	public function handle_single_automation_run( $automation_data, $automation_id ) {
		// no need to run for those automations which are not present in order meta
		if ( is_array( $this->automations_for_current_event_db ) && ! in_array( $automation_id, $this->automations_for_current_event_db, true ) && empty( $this->user_selected_actions ) ) {
			return false;
		}

		/** If current status or order is same as the order status selected by user in automation */
		if ( isset( $automation_data['event_meta']['order_status'] ) && is_array( $automation_data['event_meta']['order_status'] ) && ( in_array( $this->to_status, $automation_data['event_meta']['order_status'], true ) ) ) {
			return parent::handle_single_automation_run( $automation_data, $automation_id );
		}
		/** History sync handling */
		if ( ! empty( $this->user_selected_actions ) ) {
			return parent::handle_single_automation_run( $automation_data, $automation_id );
		}

		$meta_automations = get_post_meta( $this->order_id, '_bwfan_' . $this->get_slug(), true );
		if ( ! is_array( $meta_automations ) ) {
			$meta_automations   = [];
			$meta_automations[] = $automation_id;
		} else {
			$meta_automations[] = $automation_id;
		}

		$meta_automations = array_unique( $meta_automations );
		update_post_meta( $this->order_id, '_bwfan_' . $this->get_slug(), $meta_automations ); // Update order meta so that we can check if task for this order should be made or not on order status change hook

		return false;
	}

	public function modify_order_statuses( $statuses ) {
		unset( $statuses['wc-pending'] );

		return $statuses;
	}

	/**
	 * Get old wc orders.
	 *
	 * @param $automation_meta
	 *
	 * @return stdClass|WC_Order[]
	 */
	public function get_event_records( $automation_meta ) {
		$event_meta     = $automation_meta['event_meta'];
		$event_statuses = $event_meta['order_status'];
		$query_args     = array(
			'post_type'      => 'shop_order',
			'orderby'        => 'date',
			'order'          => 'asc',
			'posts_per_page' => - 1,
			'post_status'    => $event_statuses,
			'return'         => 'ids',
		);

		if ( ! is_null( $this->display_count ) ) {
			$query_args['posts_per_page'] = $this->display_count;
		}
		if ( ! is_null( $this->page ) ) {
			$query_args['page'] = $this->page;
		}
		if ( ! is_null( $this->offset ) ) {
			$query_args['offset'] = $this->offset;
		}

		$query_args['date_query'] = array(
			array(
				'after'     => array(
					'year'  => $this->from_year,
					'month' => $this->from_month,
					'day'   => $this->from_day,
				),
				'before'    => array(
					'year'  => $this->to_year,
					'month' => $this->to_month,
					'day'   => $this->to_day,
				),
				'inclusive' => true,
			),
		);

		$orders = wc_get_orders( $query_args );

		return $orders;
	}

	/**
	 * Run automations on all the old records of the current event.
	 * This function is used in sync process.
	 *
	 * @param $orders
	 */
	public function process_event_records( $orders ) {
		if ( empty( $orders ) ) {
			return;
		}

		foreach ( $orders as $order_id ) {
			$this->sync_start_time ++;
			// make the tasks from here
			$this->order_id = $order_id;
			$this->order    = wc_get_order( $order_id );
			$this->run_automations();

			$this->offset ++;
			$this->processed ++;

			$data = array(
				'offset'    => $this->offset,
				'processed' => $this->processed,
			);
			$this->update_sync_record( $this->sync_id, $data );
		}
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
		$order_date = BWFAN_Woocommerce_Compatibility::get_order_date( $this->order );
		$actions    = $this->calculate_actions_time( $actions, $order_date );

		return $actions;
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_woocommerce_active() ) {
	return 'BWFAN_WC_New_Order';
}
