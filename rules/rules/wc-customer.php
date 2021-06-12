<?php

class BWFAN_Rule_Is_First_Order extends BWFAN_Rule_Base {

	public $supports = array( 'order' );

	public function __construct() {
		parent::__construct( 'is_first_order' );
	}

	public function get_possible_rule_operators() {
		return null;
	}

	public function get_possible_rule_values() {
		return array(
			'yes' => __( 'Yes', 'wp-marketing-automations' ),
			'no'  => __( 'No', 'wp-marketing-automations' ),
		);
	}

	public function get_condition_input_type() {
		return 'Select';
	}

	public function is_match( $rule_data ) {
		$is_first      = false;
		$billing_email = BWFAN_Core()->rules->getRulesData( 'email' );
		if ( empty( $billing_email ) ) {
			$order         = BWFAN_Core()->rules->getRulesData( 'wc_order' );
			$billing_email = BWFAN_WooCommerce_Compatibility::get_order_data( $order, '_billing_email' );
		}

		$orders = wc_get_orders( array(
			'customer' => $billing_email,
			'limit'    => 2,
			'return'   => 'ids',
		) );

		if ( count( $orders ) === 1 ) {
			$is_first = true;
		}

		return ( 'yes' === $rule_data['condition'] ) ? $is_first : ! $is_first;
	}

	public function ui_view() {
		esc_html_e( 'Customer', 'wp-marketing-automations' );
		?>
        <% if (condition == "yes") { %> <% } %>
        <% if (condition == "no") { %> not a <% } %>
		<?php
		esc_html_e( 'first order', 'wp-marketing-automations' );
	}
}

class BWFAN_Rule_Is_Guest extends BWFAN_Rule_Base {
	public $supports = array( 'order' );

	public function __construct() {
		parent::__construct( 'is_guest' );
	}

	public function get_possible_rule_operators() {
		return null;
	}

	public function get_possible_rule_values() {
		return array(
			'yes' => __( 'Yes', 'wp-marketing-automations' ),
			'no'  => __( 'No', 'wp-marketing-automations' ),
		);
	}

	public function is_match( $rule_data ) {
		$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );

		if ( ! empty( $order ) ) {
			$result = ( $order->get_user_id() === 0 );

			return ( 'yes' === $rule_data['condition'] ) ? $result : ! $result;
		}

		$email = BWFAN_Core()->rules->get_environment_var( 'email' );
		if ( ! empty( $email ) ) {
			$result = true;
			$user   = get_user_by( 'user_email', $email );
			if ( $user instanceof WP_User ) {
				$result = false;
			}

			return ( 'yes' === $rule_data['condition'] ) ? $result : ! $result;
		}

		/** Checking user logged in value only if order or email value not passed */
		return ! is_user_logged_in();
	}

	public function ui_view() {
		esc_html_e( 'Customer', 'wp-marketing-automations' );
		?>
        <% if (condition == "yes") { %> is <% } %>
        <% if (condition == "no") { %> is not <% } %>
		<?php
		esc_html_e( 'a guest', 'wp-marketing-automations' );
	}
}

class BWFAN_Rule_Customer_User extends BWFAN_Dynamic_Option_Base {
	public $supports = array( 'order' );

	public function __construct() {
		parent::__construct( 'customer_user' );
	}

	public function get_search_type_name() {
		return 'wp_users';
	}

	public function get_condition_values_nice_names( $values ) {
		$return = [];
		if ( count( $values ) > 0 ) {
			foreach ( $values as $user ) {
				$userdata        = get_userdata( $user );
				$return[ $user ] = $userdata->display_name;
			}
		}

		return $return;
	}

	public function get_search_results( $term ) {
		$array       = array();
		$users       = new WP_User_Query( array(
			'search'         => '*' . esc_attr( $term ) . '*',
			'search_columns' => array(
				'user_login',
				'user_nicename',
				'user_email',
				'user_url',
			),
		) );
		$users_found = $users->get_results();

		foreach ( $users_found as $user ) {
			array_push( $array, array(
				'id'   => $user->ID,
				'text' => $user->data->display_name,
			) );
		}
		wp_send_json( array(
			'results' => $array,
		) );
	}

	public function is_match( $rule_data ) {
		$id = BWFAN_Core()->rules->getRulesData( 'user_id' );
		if ( empty( $id ) ) {
			$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
			$id    = $order->get_user_id();
		}

		$rule_data['condition'] = array_map( 'intval', $rule_data['condition'] );
		$result                 = in_array( $id, $rule_data['condition'], true );
		$result                 = ( 'in' === $rule_data['operator'] ) ? $result : ! $result;

		return $this->return_is_match( $result, $rule_data );
	}

	public function ui_view() {
		esc_html_e( 'Customer', 'wp-marketing-automations' );
		?>
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>
        <%= ops[operator] %>
        <% var chosen = []; %>
        <% _.each(condition, function( value, key ){ %>
        <% chosen.push(uiData[value]); %>
        <% }); %>
        <%= chosen.join("/ ") %>
		<?php
	}

	public function get_possible_rule_operators() {
		return array(
			'in'    => __( 'is', 'wp-marketing-automations' ),
			'notin' => __( 'is not', 'wp-marketing-automations' ),
		);
	}
}

class BWFAN_Rule_Customer_Role extends BWFAN_Rule_Base {

	public $supports = array( 'order' );

	public function __construct() {
		parent::__construct( 'customer_role' );
	}

	public function get_possible_rule_values() {
		$result         = array();
		$editable_roles = get_editable_roles();

		if ( $editable_roles ) {
			foreach ( $editable_roles as $role => $details ) {
				$name = translate_user_role( $details['name'] );

				$result[ $role ] = $name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data ) {
		$id = BWFAN_Core()->rules->getRulesData( 'user_id' );
		if ( empty( $id ) ) {
			$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
			$id    = $order instanceof WC_Order ? $order->get_user_id() : $id;
		}

		if ( empty( $id ) ) {
			$email       = BWFAN_Core()->rules->getRulesData( 'email' );
			$contact_db  = WooFunnels_DB_Operations::get_instance();
			$contact_obj = $contact_db->get_contact_by_email( $email );

			if ( isset( $contact_obj->wpid ) && absint( $contact_obj->wpid ) > 0 ) {
				$id = absint( $contact_obj->wpid );
			}
		}

		$result = false;

		if ( $rule_data['condition'] && is_array( $rule_data['condition'] ) ) {
			$user = get_user_by( 'id', $id );

			foreach ( $rule_data['condition'] as $role ) {
				if ( in_array( $role, $user->roles ) ) {
					$result = true;
					break;
				}
			}
		}

		if ( 'in' === $rule_data['operator'] ) {
			return $result;
		} else {
			return ! $result;
		}

	}

	public function ui_view() {
		esc_html_e( 'Customer role', 'wp-marketing-automations' );
		?>
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>
        <%= ops[operator] %>
        <% var chosen = []; %>
        <% _.each(condition, function( value, key ){ %>
        <% chosen.push(uiData[value]); %>
        <% }); %>
        <%= chosen.join("/ ") %>
		<?php
	}

	public function get_possible_rule_operators() {
		return array(
			'in'    => __( 'is', 'wp-marketing-automations' ),
			'notin' => __( 'is not', 'wp-marketing-automations' ),
		);
	}
}
