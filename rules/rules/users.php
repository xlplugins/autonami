<?php

class BWFAN_Rule_Users_Role extends BWFAN_Rule_Base {

	public function __construct() {
		parent::__construct( 'users_role' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'wp-marketing-automations' ),
			'notin' => __( 'is not', 'wp-marketing-automations' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result         = array();
		$editable_roles = get_editable_roles();

		if ( $editable_roles ) {
			foreach ( $editable_roles as $role => $details ) {
				$name            = translate_user_role( $details['name'] );
				$result[ $role ] = $name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data ) {
		$user_id = BWFAN_Core()->rules->getRulesData( 'user_id' );
		$email   = BWFAN_Core()->rules->getRulesData( 'email' );

		$user = ! empty( $user_id ) ? get_user_by( 'id', $user_id ) : ( is_email( $email ) ? get_user_by( 'email', $email ) : '' );
		$user = ! $user instanceof WP_User ? BWFAN_Core()->rules->getRulesData( 'wp_user' ) : $user;

		if ( ! $user instanceof WP_User ) {
			return $this->return_is_match( false, $rule_data );
		}

		$result = false;
		$role   = [];
		if ( $rule_data['condition'] && is_array( $rule_data['condition'] ) ) {
			$role = array_intersect( (array) $user->roles, $rule_data['condition'] );
		}
		if ( ! empty( $role ) ) {
			$result = true;
		}

		$result = ( 'in' === $rule_data['operator'] ) ? $result : ! $result;

		return $this->return_is_match( $result, $rule_data );
	}

	public function sort_attribute_taxonomies( $taxa, $taxb ) {
		return strcmp( $taxa->attribute_name, $taxb->attribute_name );
	}

	public function ui_view() {
		esc_html_e( 'User Role', 'wp-marketing-automations' );
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

}

class BWFAN_Rule_Users_User extends BWFAN_Dynamic_Option_Base {

	public function __construct() {
		parent::__construct( 'users_user' );
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

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'wp-marketing-automations' ),
			'notin' => __( 'is not', 'wp-marketing-automations' ),
		);

		return $operators;
	}

	public function is_match( $rule_data ) {
		$user_id = BWFAN_Core()->rules->getRulesData( 'user_id' );

		if ( empty( $user_id ) ) {
			$email = BWFAN_Core()->rules->getRulesData( 'email' );
			$user  = ! is_email( $email ) ? get_user_by( 'email', $email ) : BWFAN_Core()->rules->getRulesData( 'wp_user' );

			if ( ! $user instanceof WP_User ) {
				return $this->return_is_match( false, $rule_data );
			}

			$user_id = $user->ID;
		}

		$rule_data['condition'] = array_map( 'intval', $rule_data['condition'] );
		$result                 = in_array( $user_id, $rule_data['condition'], true );
		$result                 = ( 'in' === $rule_data['operator'] ) ? $result : ! $result;

		return $this->return_is_match( $result, $rule_data );
	}

	public function ui_view() {
		esc_html_e( 'User', 'wp-marketing-automations' );
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

}
