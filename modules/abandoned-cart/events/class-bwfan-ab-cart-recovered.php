<?php

final class BWFAN_AB_Cart_Recovered extends BWFAN_Event {
	private static $instance = null;
	public $order_id = null;
	/** @var WC_Order $order */
	public $order = null;

	public function __construct( $source_slug ) {
		$this->source_type            = $source_slug;
		$this->optgroup_label         = __( 'Cart', 'wp-marketing-automations' );
		$this->event_name             = __( 'Cart Recovered', 'wp-marketing-automations' );
		$this->event_desc             = __( 'This automation would trigger when the user abandoned cart is recovered.', 'wp-marketing-automations' );
		$this->event_merge_tag_groups = array( 'wc_customer', 'wc_order' );
		$this->event_rule_groups      = array( 'wc_order', 'wc_customer', 'aerocheckout' );
		$this->support_lang           = true;
		$this->priority               = 5.1;
		$this->customer_email_tag     = '{{admin_email}}';
	}

	public function load_hooks() {
		add_action( 'abandoned_cart_recovered', array( $this, 'process' ), 10, 2 );
	}

	public static function get_instance( $source_slug ) {
		if ( null === self::$instance ) {
			self::$instance = new self( $source_slug );
		}

		return self::$instance;
	}

	public function pre_executable_actions( $value ) {
		BWFAN_Core()->rules->setRulesData( $this->order, 'wc_order' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->order->get_billing_email(), $this->order->get_user_id() ), 'bwf_customer' );
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $order_id
	 */
	public function process( $cart_details, $order_id ) {
		$data             = $this->get_default_data();
		$data['order_id'] = $order_id;

		$this->send_async_call( $data );
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$order_id       = BWFAN_Common::$events_async_data['order_id'];
		$this->order_id = $order_id;
		$order          = wc_get_order( $order_id );
		$this->order    = $order;

		return $this->run_automations();
	}

	/**
	 * Registers the tasks for current event.
	 *
	 * @param $automation_id
	 * @param $integration_data
	 * @param $event_data
	 */
	public function register_tasks( $automation_id, $integration_data, $event_data ) {
		$data_to_send = $this->get_event_data();

		$this->create_tasks( $automation_id, $integration_data, $event_data, $data_to_send );
	}

	public function get_event_data() {
		$data_to_send                       = [];
		$data_to_send['global']['order_id'] = $this->order_id;
		$order                              = $this->order;
		$data_to_send['global']['email']    = is_object( $order ) ? BWFAN_Woocommerce_Compatibility::get_billing_email( $order ) : '';

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
                <strong><?php echo esc_html__( 'Recovered Order:', 'wp-marketing-automations' ); ?> </strong>
                <a target="_blank" href="<?php echo get_edit_post_link( $global_data['order_id'] ); //phpcs:ignore WordPress.Security.EscapeOutput
				?>"><?php echo '#' . esc_attr( $global_data['order_id'] . ' ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?></a>
            </li>
		<?php } ?>
        <li>
            <strong><?php echo esc_html__( 'Email:', 'wp-marketing-automations' ); ?> </strong>
            <span><?php echo esc_html( $global_data['email'] ); ?></span>
        </li>
		<?php
		return ob_get_clean();
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
}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_woocommerce_active() ) {
	return 'BWFAN_AB_Cart_Recovered';
}
