<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BWFAN_WC_Cart_AB_Email_Series_Cart_Total extends BWFAN_Recipes {
	private static $instance = null;

	public function __construct() {
		$settings                             = $this->get_settings();
		$this->data['name']                   = __( 'Cart abandonment email series based on cart total', 'wp-marketing-automations' );
		$this->data['description']            = __( 'Email sequences based on 3 cart total conditions. If the cart total is more than $100 create a discount coupon of 20% and send a sequence of email, if the cart total is between $50 and $100 create a discount coupon of 15% off and send a sequence of email, else send an email without a discount coupon.', 'wp-marketing-automations' );
		$this->data['data-dependencies']      = array(
			array(
				'operator'      => '=',
				'current_value' => empty( $settings['bwfan_ab_enable'] ) ? '' : '1',
				'check_value'   => '1',
				'message'       => __( 'Cart tracking is not enabled.', 'wp-marketing-automations' ),
			),
		);
		$this->data['plugin-dependencies']    = array( 'woocommerce' );
		$this->data['connector-dependencies'] = array();
		$this->data['json']                   = array( 'cart_abandonment_email_based_on_order_total' );
		$this->data['connector-filter']       = array();
		$this->data['plugin-filter']          = array( 'wc' );
		$this->data['integration-filter']     = array( 'wc' );
		$this->data['channel-filter']         = array( 'email' );
		$this->data['goal-filter']            = array();
		$this->data['type-filter']            = array( 'abandoned_cart', 'browse_abandonment' );
		$this->data['tags']                   = array( 'new' => 'New' );
		$this->data['priority']               = 120;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

/**
 * Register this merge tag to a group.
 */
BWFAN_Recipe_Loader::register( 'BWFAN_WC_Cart_AB_Email_Series_Cart_Total' );
