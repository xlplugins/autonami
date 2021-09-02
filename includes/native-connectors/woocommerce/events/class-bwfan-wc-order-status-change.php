<?php

final class BWFAN_WC_Order_Status_Change extends BWFAN_Event {
	private static $instance = null;
	public $order_id = null;
	public $from_status = null;
	public $to_status = null;
	public $order = null;

	private function __construct() {
		$this->optgroup_label         = esc_html__( 'Orders', 'wp-marketing-automations' );
		$this->event_name             = esc_html__( 'Order Status Changed', 'wp-marketing-automations' );
		$this->event_desc             = esc_html__( 'This event runs after an order status is changed.', 'wp-marketing-automations' );
		$this->event_merge_tag_groups = array( 'wc_customer', 'wc_order' );
		$this->event_rule_groups      = array( 'wc_order', 'wc_customer', 'automation', 'wc_order_state' );
		$this->priority               = 15.2;
		$this->support_lang           = true;
	}

	public function load_hooks() {
		add_action( 'woocommerce_order_status_changed', array( $this, 'order_status_changed' ), 11, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Localize data for html fields for the current event.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$wc_order_statuses    = $this->get_view_data();
			$to_wc_order_statuses = $this->get_view_data();
			if ( isset( $to_wc_order_statuses['wc-pending'] ) ) {
				unset( $to_wc_order_statuses['wc-pending'] );
			}
			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'from_options', $wc_order_statuses );
			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'to_options', $to_wc_order_statuses );
		}
	}

	public function get_view_data() {
		$all_status = wc_get_order_statuses();

		return $all_status;
	}

	/**
	 * Show the html fields for the current event.
	 */
	public function get_view( $db_eventmeta_saved_value ) {
		?>
        <script type="text/html" id="tmpl-event-<?php esc_attr_e( $this->get_slug() ); ?>">
            <#
            is_validated = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'validate_event')) ? 'checked' : '';
            selected_from_status = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'from')) ? data.eventSavedData.from : '';
            selected_to_status = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'to')) ? data.eventSavedData.to : '';
            #>
            <div class="bwfan_mt15"></div>
            <div class="bwfan-col-sm-6 bwfan-pl-0">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'From Status', 'wp-marketing-automations' ); ?></label>
                <select required id="" class="bwfan-input-wrapper" name="event_meta[from]">
                    <option value="wc-any"><?php esc_html_e( 'Any', 'wp-marketing-automations' ); ?></option>
                    <#
                    if(_.has(data.eventFieldsOptions, 'from_options') && _.isObject(data.eventFieldsOptions.from_options) ) {
                    _.each( data.eventFieldsOptions.from_options, function( value, key ){
                    selected = (key == selected_from_status) ? 'selected' : '';
                    #>
                    <option value="{{key}}" {{selected}}>{{value}}</option>
                    <# })
                    }
                    #>
                </select>
            </div>
            <div class="bwfan-col-sm-6 bwfan-pr-0">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'To Status', 'wp-marketing-automations' ); ?></label>
                <select required id="" class="bwfan-input-wrapper" name="event_meta[to]">
                    <option value="wc-any"><?php esc_html_e( 'Any', 'wp-marketing-automations' ); ?></option>
                    <#
                    if(_.has(data.eventFieldsOptions, 'to_options') && _.isObject(data.eventFieldsOptions.to_options) ) {
                    _.each( data.eventFieldsOptions.to_options, function( value, key ){
                    selected = (key == selected_to_status) ? 'selected' : '';
                    #>
                    <option value="{{key}}" {{selected}}>{{value}}</option>
                    <# })
                    }
                    #>
                </select>
            </div>
			<?php
			$this->get_validation_html( $this->get_slug(), 'Validate order status before executing task', 'Validate' );
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
	 * @param $value
	 */
	public function pre_executable_actions( $value ) {
		$order       = wc_get_order( $this->order_id );
		$this->order = $order;
		BWFAN_Core()->rules->setRulesData( $this->order, 'wc_order' );
		BWFAN_Core()->rules->setRulesData( $this->event_automation_id, 'automation_id' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->order->get_billing_email(), $this->order->get_user_id() ), 'bwf_customer' );
	}

	public function handle_single_automation_run( $value1, $automation_id ) {
		$is_register_task = false;
		$to_status        = $this->to_status;
		$from_status      = $this->from_status;
		$event_meta       = $value1['event_meta'];
		$from             = str_replace( 'wc-', '', $event_meta['from'] );
		$to               = str_replace( 'wc-', '', $event_meta['to'] );

		if ( 'any' === $from && 'any' === $to ) {
			$is_register_task = true;
		} elseif ( 'any' === $from && $to_status === $to ) {
			$is_register_task = true;
		} elseif ( $from_status === $from && 'any' === $to ) {
			$is_register_task = true;
		} elseif ( $from_status === $from && $to_status === $to ) {
			$is_register_task = true;
		}

		if ( $is_register_task ) {
			$all_statuses        = wc_get_order_statuses();
			$value1['from']      = $all_statuses[ 'wc-' . $from_status ];
			$value1['from_slug'] = 'wc-' . $from_status;
			$value1['to']        = $all_statuses[ 'wc-' . $to_status ];
			$value1['to_slug']   = 'wc-' . $to_status;

			return parent::handle_single_automation_run( $value1, $automation_id );
		}

		return '';
	}

	public function order_status_changed( $order_id, $from_status, $to_status ) {
		if ( BWFAN_Common::bwf_check_to_skip_child_order( $order_id ) ) {
		    return;
		}
		BWFAN_Core()->public->load_active_automations( $this->get_slug() );

		$this->process( $order_id, $from_status, $to_status );
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $order_id
	 * @param $from_status
	 * @param $to_status
	 */
	public function process( $order_id, $from_status, $to_status ) {
		$data                = $this->get_default_data();
		$data['order_id']    = $order_id;
		$data['from_status'] = $from_status;
		$data['to_status']   = $to_status;

		$this->send_async_call( $data );
	}

	/**
	 * Returns the current event settings set in the automation at the time of task creation.
	 *
	 * @param $value
	 *
	 * @return array
	 */
	public function get_automation_event_data( $value ) {
		$event_meta = $value['event_meta'];
		$event_data = [
			'event_source'   => $value['source'],
			'event_slug'     => $value['event'],
			'validate_event' => ( isset( $value['event_meta']['validate_event'] ) ) ? 1 : 0,
			'from_status'    => $event_meta['from'],
			'to_status'      => $event_meta['to'],
			'from'           => $value['from'],
			'from_slug'      => $value['from_slug'],
			'to'             => $value['to'],
			'to_slug'        => $value['to_slug'],
		];

		return $event_data;
	}

	/**
	 * Registers the tasks for current event.
	 *
	 * @param $automation_id
	 * @param $actions : after processing events data
	 * @param $event_data
	 */
	public function register_tasks( $automation_id, $actions, $event_data ) {
		if ( ! is_array( $actions ) ) {
			return;
		}

		$data_to_send = $this->get_event_data( $event_data );
		$this->create_tasks( $automation_id, $actions, $event_data, $data_to_send );
	}

	public function get_event_data( $event_data = array() ) {
		$data_to_send                       = [];
		$data_to_send['global']['order_id'] = $this->order_id;
		$data_to_send['global']['from']     = isset( $event_data['from'] ) ? $event_data['from'] : '';
		$data_to_send['global']['to']       = isset( $event_data['to'] ) ? $event_data['to'] : '';

		$this->order                     = $this->order instanceof WC_Order ? $this->order : wc_get_order( $this->order_id );
		$data_to_send['global']['email'] = $this->order instanceof WC_Order ? $this->order->get_billing_email() : '';
		$data_to_send['global']['phone'] = $this->order instanceof WC_Order ? $this->order->get_billing_phone() : '';

		return $data_to_send;
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
                <strong><?php esc_html_e( 'Order:', 'wp-marketing-automations' ); ?> </strong>
                <a target="_blank" href="<?php echo get_edit_post_link( $global_data['order_id'] ); //phpcs:ignore WordPress.Security.EscapeOutput
				?>"><?php echo '#' . esc_html( $global_data['order_id'] . ' ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?></a>
            </li>
		<?php } ?>
        <li>
            <strong><?php esc_html_e( 'Email:', 'wp-marketing-automations' ); ?> </strong>
			<?php esc_html_e( $global_data['email'] ); ?>
        </li>
        <li>
            <strong><?php esc_html_e( 'From Status:', 'wp-marketing-automations' ); ?> </strong>
			<?php esc_html_e( $global_data['from'] ); ?>
        </li>
        <li>
            <strong><?php esc_html_e( 'To Status:', 'wp-marketing-automations' ); ?> </strong>
			<?php esc_html_e( $global_data['to'] ); ?>
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
		$result        = [];
		$task_event    = $task_details['event_data']['event_slug'];
		$automation_id = $task_details['processed_data']['automation_id'];

		$automation_details                         = BWFAN_Model_Automations::get_automation_with_data( $automation_id );
		$current_automation_event                   = $automation_details['event'];
		$current_automation_event_meta              = $automation_details['meta']['event_meta'];
		$current_automation_event_validation_status = ( isset( $current_automation_event_meta['validate_event'] ) ) ? $current_automation_event_meta['validate_event'] : 0;
		$current_automation_status_to               = $current_automation_event_meta['to'];

		if ( 'wc-any' === $current_automation_event_meta['from'] && 'wc-any' === $current_automation_event_meta['to'] ) {
			$result = $this->get_automation_event_success();

			return $result;
		}

		/** Using current automation 'order to' state rather than saved one in the task */

		/** Current automation has no checking */
		if ( 0 === $current_automation_event_validation_status ) {
			$result = $this->get_automation_event_validation();

			return $result;
		}

		/** Current automation event does not match with the event of task when the task was made */
		if ( $task_event !== $current_automation_event ) {
			$result = $this->get_automation_event_status();

			return $result;
		}

		$order_id          = $task_details['processed_data']['order_id'];
		$order             = wc_get_order( $order_id );
		$task_order_status = BWFAN_Woocommerce_Compatibility::get_order_status( $order );

		if ( $task_order_status === $current_automation_status_to ) {
			$result = $this->get_automation_event_success();

			return $result;
		}

		$result['status']  = 4;
		$result['message'] = __( 'Order status in automation has been changed', 'wp-marketing-automations' );

		return $result;
	}

	public function validate_event_data_before_executing_task( $data ) {
		return $this->validate_order( $data );
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

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$order_id          = BWFAN_Common::$events_async_data['order_id'];
		$from_status       = BWFAN_Common::$events_async_data['from_status'];
		$to_status         = BWFAN_Common::$events_async_data['to_status'];
		$this->order_id    = $order_id;
		$this->from_status = $from_status;
		$this->to_status   = $to_status;

		return $this->run_automations();
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_woocommerce_active() ) {
	return 'BWFAN_WC_Order_Status_Change';
}
