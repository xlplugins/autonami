<?php

class BWFAN_Rule_Order_Total extends BWFAN_Rule_Base {
	public $supports = array( 'cart', 'order' );

	public function __construct() {
		parent::__construct( 'order_total' );
//		$this->description = 'test description';
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function is_match( $rule_data ) {
		$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$price = (float) $order->get_total();
		$value = (float) $rule_data['condition'];

		switch ( $rule_data['operator'] ) {
			case '==':
				$result = $price === $value;
				break;
			case '!=':
				$result = $price !== $value;
				break;
			case '>':
				$result = $price > $value;
				break;
			case '<':
				$result = $price < $value;
				break;
			case '>=':
				$result = $price >= $value;
				break;
			case '<=':
				$result = $price <= $value;
				break;
			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

	public function ui_view() {
		?>
        Order total
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %>
        <%= condition %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'wp-marketing-automations' ),
			'!=' => __( 'is not equal to', 'wp-marketing-automations' ),
			'>'  => __( 'is greater than', 'wp-marketing-automations' ),
			'<'  => __( 'is less than', 'wp-marketing-automations' ),
			'>=' => __( 'is greater or equal to', 'wp-marketing-automations' ),
			'<=' => __( 'is less or equal to', 'wp-marketing-automations' ),
		);

		return $operators;
	}

}

class BWFAN_Rule_Product_Stock extends BWFAN_Rule_Base {
	public $supports = array( 'cart', 'order' );

	public function __construct() {
		parent::__construct( 'product_stock' );
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function is_match( $rule_data ) {
		$order     = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$cart_item = BWFAN_Core()->rules->getRulesData( 'wc_items' );
		if ( empty( $cart_item ) ) {
			return $this->return_is_match( false, $rule_data );
		}

		$product = BWFAN_Woocommerce_Compatibility::get_product_from_item( $order, $cart_item );
		if ( ! $product instanceof WC_Product ) {
			return $this->return_is_match( false, $rule_data );
		}

		$price = $product->get_stock_quantity();
		$value = (int) $rule_data['condition'];

		switch ( $rule_data['operator'] ) {
			case '==':
				$result = $price === $value;
				break;
			case '!=':
				$result = $price !== $value;
				break;
			case '>':
				$result = $price > $value;
				break;
			case '<':
				$result = $price < $value;
				break;
			case '>=':
				$result = $price >= $value;
				break;
			case '<=':
				$result = $price <= $value;
				break;
			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

	public function ui_view() {
		esc_html_e( 'Item Stock', 'wp-marketing-automations' );
		?>

        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %>
        <%= condition %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'wp-marketing-automations' ),
			'!=' => __( 'is not equal to', 'wp-marketing-automations' ),
			'>'  => __( 'is greater than', 'wp-marketing-automations' ),
			'<'  => __( 'is less than', 'wp-marketing-automations' ),
			'>=' => __( 'is greater or equal to', 'wp-marketing-automations' ),
			'<=' => __( 'is less or equal to', 'wp-marketing-automations' ),
		);

		return $operators;
	}

}

class BWFAN_Rule_Product_Item extends BWFAN_Rule_Products {
	public $supports = array( 'cart', 'order' );

	public function __construct() {
		parent::__construct( 'product_item' );
	}

	public function get_products() {
		$order     = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$cart_item = BWFAN_Core()->rules->getRulesData( 'wc_items' );
		$found_ids = [];

		if ( ! empty( $cart_item ) ) {
			return $this->get_product_ids( $found_ids, $order, $cart_item );
		}

		foreach ( $order->get_items() as $item ) {
			$found_ids = $this->get_product_ids( $found_ids, $order, $item );
		}

		return $found_ids;
	}

	public function get_product_ids( $found_ids, $order, $cart_item ) {
		$product = BWFAN_Woocommerce_Compatibility::get_product_from_item( $order, $cart_item );
		if ( ! $product instanceof WC_Product ) {
			return $found_ids;
		}

		$product_id   = $product->get_id();
		$product_id   = ( $product->get_parent_id() ) ? $product->get_parent_id() : $product_id;
		$variation_id = $cart_item->get_variation_id();

		if ( ! empty( $variation_id ) ) {
			array_push( $found_ids, $variation_id );
			array_push( $found_ids, $product_id );
		} else {
			array_push( $found_ids, $product_id );
		}

		return $found_ids;
	}

	public function ui_view() {
		esc_html_e( 'Items ', 'wp-marketing-automations' );
		?>
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %> <% var chosen = []; %>
        <% _.each(condition, function( value, key ){ %>
        <%
        if(_.has(uiData, value)) {
        chosen.push(uiData[value]);
        }
        %>

        <% }); %>
        <%= chosen.join("/ ") %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'any'  => __( 'matches any of', 'wp-marketing-automations' ),
			'all'  => __( 'matches exactly', 'wp-marketing-automations' ),
			'none' => __( 'matches none of', 'wp-marketing-automations' ),
		);

		return $operators;
	}

}

class BWFAN_Rule_Order_Taxonomy extends BWFAN_Rule_Term_Taxonomy {

	public $taxonomy_name = '';

	public function __construct() {
		parent::__construct( 'product_category' );
	}

	public function get_term_ids() {
		$order     = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$cart_item = BWFAN_Core()->rules->getRulesData( 'wc_items' );
		$all_terms = array();

		if ( ! empty( $cart_item ) ) {
			return $this->get_product_terms( $all_terms, $order, $cart_item );
		}

		foreach ( $order->get_items() as $item ) {
			$all_terms = $this->get_product_terms( $all_terms, $order, $item );
		}

		return $all_terms;
	}

	public function ui_view() {
		esc_html_e( 'Item\'s category ', 'wp-marketing-automations' );
		?>
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %><% var chosen = []; %>
        <% _.each(condition, function( value, key ){ %>
        <% chosen.push(uiData[value]); %>

        <% }); %>
        <%= chosen.join("/ ") %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'any'  => __( 'matches any of', 'wp-marketing-automations' ),
			'none' => __( 'matches none of', 'wp-marketing-automations' ),
		);

		return $operators;
	}

}

class BWFAN_Rule_Product_Category extends BWFAN_Rule_Term_Taxonomy {

	public $taxonomy_name = 'product_cat';

	public function __construct() {
		parent::__construct( 'product_category' );
	}

	public function get_term_ids() {
		$order     = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$cart_item = BWFAN_Core()->rules->getRulesData( 'wc_items' );
		$all_terms = array();

		if ( ! empty( $cart_item ) ) {
			return $this->get_product_terms( $all_terms, $order, $cart_item );
		}

		foreach ( $order->get_items() as $item ) {
			$all_terms = $this->get_product_terms( $all_terms, $order, $item );
		}

		return $all_terms;
	}

	public function ui_view() {
		esc_html_e( 'Item\'s category ', 'wp-marketing-automations' );
		?>
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %><% var chosen = []; %>
        <% _.each(condition, function( value, key ){ %>
        <% chosen.push(uiData[value]); %>

        <% }); %>
        <%= chosen.join("/ ") %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'any'  => __( 'matches any of', 'wp-marketing-automations' ),
			'none' => __( 'matches none of', 'wp-marketing-automations' ),
		);

		return $operators;
	}

}

class BWFAN_Rule_Product_Tags extends BWFAN_Rule_Term_Taxonomy {

	public $taxonomy_name = 'product_tag';

	public function __construct() {
		parent::__construct( 'product_tags' );
	}

	public function get_term_ids() {
		$order     = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$cart_item = BWFAN_Core()->rules->getRulesData( 'wc_items' );
		$all_terms = array();
		if ( ! empty( $cart_item ) ) {
			return $this->get_product_terms( $all_terms, $order, $cart_item );
		}

		foreach ( $order->get_items() as $item ) {
			$all_terms = $this->get_product_terms( $all_terms, $order, $item );
		}

		return $all_terms;
	}

	public function ui_view() {
		esc_html_e( 'Item\'s tags ', 'wp-marketing-automations' );
		?>
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %><% var chosen = []; %>
        <% _.each(condition, function( value, key ){ %>
        <% chosen.push(uiData[value]); %>

        <% }); %>
        <%= chosen.join("/ ") %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'any'  => __( 'matches any of', 'wp-marketing-automations' ),
			'none' => __( 'matches none of', 'wp-marketing-automations' ),
		);

		return $operators;
	}


}

class BWFAN_Rule_Product_Item_Type extends BWFAN_Rule_Base {
	public $supports = array( 'cart', 'order' );

	public function __construct() {
		parent::__construct( 'product_item_type' );
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data ) {
		$type      = $rule_data['operator'];
		$all_types = array();
		$result    = false;
		$order     = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$cart_item = BWFAN_Core()->rules->getRulesData( 'wc_items' );

		if ( ! empty( $cart_item ) ) {
			return $this->get_product_types( $all_types, $order, $cart_item );
		}

		foreach ( $order->get_items() as $item ) {
			$all_types = $this->get_product_types( $all_types, $order, $item );
		}

		if ( empty( $all_types ) ) {
			$result = ( 'none' === $type ) ? true : false;

			return $this->return_is_match( $result, $rule_data );
		}

		switch ( $type ) {
			case 'any':
				if ( is_array( $rule_data['condition'] ) && is_array( $all_types ) ) {
					$result = count( array_intersect( $rule_data['condition'], $all_types ) ) >= 1;
				}
				break;
			case 'none':
				if ( is_array( $rule_data['condition'] ) && is_array( $all_types ) ) {
					$result = count( array_intersect( $rule_data['condition'], $all_types ) ) === 0;
				}
				break;

			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

	public function get_product_types( $all_types, $order, $cart_item ) {
		$product = BWFAN_WooCommerce_Compatibility::get_product_from_item( $order, $cart_item );
		if ( ! $product instanceof WC_Product ) {
			return $all_types;
		}

		$product_id    = $product->get_id();
		$product_id    = ( $product->get_parent_id() ) ? $product->get_parent_id() : $product_id;
		$product_types = wp_get_post_terms( $product_id, 'product_type', array(
			'fields' => 'ids',
		) );
		$all_types     = array_merge( $all_types, $product_types );
		$all_types     = array_filter( $all_types );

		return $all_types;
	}

	public function ui_view() {
		?>
        Item's type
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
		$operators = array(
			'any'  => __( 'matches any of', 'wp-marketing-automations' ),
			'none' => __( 'matches none of', 'wp-marketing-automations' ),
		);

		return $operators;
	}

	public function get_ui_preview_data() {
		return $this->get_possible_rule_values();
	}

	public function get_possible_rule_values() {
		$terms = get_terms( 'product_type', array(
			'hide_empty' => false,
		) );

		$result = [];
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( 'grouped' === $term->name ) {
					continue;
				}
				$result[ $term->term_id ] = $term->name;
			}
		}

		return $result;
	}

}

class BWFAN_Rule_Product_Item_Count extends BWFAN_Rule_Base {

	public function __construct() {
		parent::__construct( 'product_item_count' );
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function is_match( $rule_data ) {
		/**
		 * @var WC_order $order
		 */

		$cart_item = BWFAN_Core()->rules->getRulesData( 'wc_items' );
		$quantity  = ( isset( $cart_item['quantity'] ) ) ? $cart_item['quantity'] : 0;
		$count     = intval( $quantity );
		$value     = absint( $rule_data['condition'] );

		switch ( $rule_data['operator'] ) {
			case '==':
				$result = $count === $value;
				break;
			case '!=':
				$result = $count !== $value;
				break;
			case '>':
				$result = $count > $value;
				break;
			case '<':
				$result = $count < $value;
				break;
			case '>=':
				$result = $count >= $value;
				break;
			case '<=':
				$result = $count <= $value;
				break;
			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

	public function ui_view() {
		?>
        Item's count
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %>
        <%= condition %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'wp-marketing-automations' ),
			'!=' => __( 'is not equal to', 'wp-marketing-automations' ),
			'>'  => __( 'is greater than', 'wp-marketing-automations' ),
			'<'  => __( 'is less than', 'wp-marketing-automations' ),
			'>=' => __( 'is greater or equal to', 'wp-marketing-automations' ),
			'<=' => __( 'is less or equal to', 'wp-marketing-automations' ),
		);

		return $operators;
	}

}

class BWFAN_Rule_Product_Item_Price extends BWFAN_Rule_Base {

	public function __construct() {
		parent::__construct( 'product_item_price' );
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function is_match( $rule_data ) {
		/**
		 * @var WC_Order_Item
		 */
		$item  = BWFAN_Core()->rules->getRulesData( 'wc_items' );
		$count = (float) $item->get_total();
		$value = (float) $rule_data['condition'];

		switch ( $rule_data['operator'] ) {
			case '==':
				$result = $count === $value;
				break;
			case '!=':
				$result = $count !== $value;
				break;
			case '>':
				$result = $count > $value;
				break;
			case '<':
				$result = $count < $value;
				break;
			case '>=':
				$result = $count >= $value;
				break;
			case '<=':
				$result = $count <= $value;
				break;
			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

	public function ui_view() {
		?>
        Item's price
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %>
        <%= condition %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'wp-marketing-automations' ),
			'!=' => __( 'is not equal to', 'wp-marketing-automations' ),
			'>'  => __( 'is greater than', 'wp-marketing-automations' ),
			'<'  => __( 'is less than', 'wp-marketing-automations' ),
			'>=' => __( 'is greater or equal to', 'wp-marketing-automations' ),
			'<=' => __( 'is less or equal to', 'wp-marketing-automations' ),
		);

		return $operators;
	}

}

class BWFAN_Rule_Order_Item extends BWFAN_Rule_Products {
	public $supports = array( 'cart', 'order' );

	public function __construct() {
		parent::__construct( 'order_item' );

	}

	public function get_products() {
		$order     = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$found_ids = [];

		if ( $order->get_items() && is_array( $order->get_items() ) && count( $order->get_items() ) ) {
			foreach ( $order->get_items() as $cart_item ) {

				$product = BWFAN_Woocommerce_Compatibility::get_product_from_item( $order, $cart_item );
				if ( ! $product instanceof WC_Product ) {
					continue;
				}

				$product_id   = $product->get_id();
				$product_id   = ( $product->get_parent_id() ) ? $product->get_parent_id() : $product_id;
				$variation_id = $cart_item->get_variation_id();

				if ( ! empty( $variation_id ) ) {
					array_push( $found_ids, $variation_id );
					array_push( $found_ids, $product_id );
				} else {
					array_push( $found_ids, $product_id );
				}
			}
		}

		return $found_ids;
	}

	public function ui_view() {
		esc_html_e( 'Order\'s items ', 'wp-marketing-automations' );
		?>
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %> <% var chosen = []; %>
        <% _.each(condition, function( value, key ){ %>
        <%
        if(_.has(uiData, value)) {
        chosen.push(uiData[value]);
        }
        %>

        <% }); %>
        <%= chosen.join("/ ") %>
		<?php
	}

}

class BWFAN_Rule_Order_Category extends BWFAN_Rule_Term_Taxonomy {

	public $taxonomy_name = 'product_cat';

	public function __construct() {
		parent::__construct( 'order_category' );
	}

	public function get_term_ids() {
		$order     = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$all_terms = array();

		if ( is_array( $order->get_items() ) && count( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $cart_item ) {
				$product = BWFAN_WooCommerce_Compatibility::get_product_from_item( $order, $cart_item );
				if ( ! $product instanceof WC_Product ) {
					continue;
				}

				$product_id = $product->get_id();
				$product_id = ( $product->get_parent_id() ) ? $product->get_parent_id() : $product_id;
				$terms      = wp_get_object_terms( $product_id, $this->taxonomy_name, array(
					'fields' => 'ids',
				) );
				$all_terms  = array_merge( $all_terms, $terms );
			}
		}
	}

	public function ui_view() {
		esc_html_e( 'Order\'s items category ', 'wp-marketing-automations' );
		?>
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %><% var chosen = []; %>
        <% _.each(condition, function( value, key ){ %>
        <% chosen.push(uiData[value]); %>

        <% }); %>
        <%= chosen.join("/ ") %>
		<?php
	}

}

class BWFAN_Rule_Order_Tags extends BWFAN_Rule_Term_Taxonomy {

	public $taxonomy_name = 'product_tag';

	public function __construct() {
		parent::__construct( 'order_tags' );
	}

	public function get_term_ids() {
		$order     = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$all_terms = array();

		if ( is_array( $order->get_items() ) && count( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $cart_item ) {
				$product = BWFAN_WooCommerce_Compatibility::get_product_from_item( $order, $cart_item );
				if ( ! $product instanceof WC_Product ) {
					continue;
				}

				$product_id = $product->get_id();
				$product_id = ( $product->get_parent_id() ) ? $product->get_parent_id() : $product_id;
				$terms      = wp_get_object_terms( $product_id, $this->taxonomy_name, array(
					'fields' => 'ids',
				) );
				$all_terms  = array_merge( $all_terms, $terms );
			}
		}

		return $all_terms;
	}


}

class BWFAN_Rule_Order_Item_Type extends BWFAN_Rule_Base {
	public $supports = array( 'cart', 'order' );

	public function __construct() {
		parent::__construct( 'order_item_type' );
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data ) {
		$type      = $rule_data['operator'];
		$all_types = array();
		$order     = BWFAN_Core()->rules->getRulesData( 'wc_order' );

		if ( $order->get_items() && count( $order->get_items() ) ) {
			foreach ( $order->get_items() as $cart_item ) {
				$product = BWFAN_WooCommerce_Compatibility::get_product_from_item( $order, $cart_item );
				if ( ! $product instanceof WC_Product ) {
					continue;
				}

				$product_id    = $product->get_id();
				$product_id    = ( $product->get_parent_id() ) ? $product->get_parent_id() : $product_id;
				$product_types = wp_get_post_terms( $product_id, 'product_type', array(
					'fields' => 'ids',
				) );
				$all_types     = array_merge( $all_types, $product_types );
			}
		}

		$all_types = array_filter( $all_types );

		if ( empty( $all_types ) ) {
			return $this->return_is_match( false, $rule_data );
		}

		switch ( $type ) {
			case 'all':
				if ( is_array( $rule_data['condition'] ) && is_array( $all_types ) ) {
					$result = count( array_intersect( $rule_data['condition'], $all_types ) ) === count( $rule_data['condition'] );
				}
				break;
			case 'any':
				if ( is_array( $rule_data['condition'] ) && is_array( $all_types ) ) {
					$result = count( array_intersect( $rule_data['condition'], $all_types ) ) >= 1;
				}
				break;

			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

	public function ui_view() {
		?>
        Order's items
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
		$operators = array(
			'any' => __( 'matches any of', 'wp-marketing-automations' ),
			'all' => __( 'matches all of ', 'wp-marketing-automations' ),
		);

		return $operators;
	}

	public function get_ui_preview_data() {
		return $this->get_possible_rule_values();
	}

	public function get_possible_rule_values() {
		$terms = get_terms( 'product_type', array(
			'hide_empty' => false,
		) );

		$result = [];
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( 'grouped' === $term->name ) {
					continue;
				}
				$result[ $term->term_id ] = $term->name;
			}
		}

		return $result;
	}

}

class BWFAN_Rule_Order_Item_Count extends BWFAN_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_item_count' );
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function is_match( $rule_data ) {
		/**
		 * @var WC_order $order
		 */
		$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$count = absint( $order->get_item_count() );
		$value = absint( $rule_data['condition'] );

		switch ( $rule_data['operator'] ) {
			case '==':
				$result = $count === $value;
				break;
			case '!=':
				$result = $count !== $value;
				break;
			case '>':
				$result = $count > $value;
				break;
			case '<':
				$result = $count < $value;
				break;
			case '>=':
				$result = $count >= $value;
				break;
			case '<=':
				$result = $count <= $value;
				break;
			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

	public function ui_view() {
		?>
        Order's item count
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %>
        <%= condition %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'wp-marketing-automations' ),
			'!=' => __( 'is not equal to', 'wp-marketing-automations' ),
			'>'  => __( 'is greater than', 'wp-marketing-automations' ),
			'<'  => __( 'is less than', 'wp-marketing-automations' ),
			'>=' => __( 'is greater or equal to', 'wp-marketing-automations' ),
			'<=' => __( 'is less or equal to', 'wp-marketing-automations' ),
		);

		return $operators;
	}

}

class BWFAN_Rule_Product_Item_Custom_Field extends BWFAN_Rule_Custom_Field {

	public function __construct() {
		parent::__construct( 'product_item_custom_field' );
	}

	public function get_possible_value( $key ) {
		$order     = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$cart_item = BWFAN_Core()->rules->getRulesData( 'wc_items' );

		if ( empty( $cart_item ) ) {
			return false;
		}

		if ( ! $cart_item instanceof WC_Order_Item ) {
			return false;
		}

		$value = wc_get_order_item_meta( $cart_item->get_id(), $key );

		return $value;
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'is'     => __( 'is', 'wp-marketing-automations' ),
			'is_not' => __( 'is not', 'wp-marketing-automations' ),
		);

		return $operators;
	}

	public function ui_view() {
		?>
        Item's Custom Field
        '<%= condition['key'] %>' <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>
        <%= ops[operator] %> '<%= condition['value'] %>'
		<?php
	}
}

class BWFAN_Rule_Order_Coupons extends BWFAN_Dynamic_Option_Base {

	public function __construct() {
		parent::__construct( 'order_coupons' );
	}

	public function get_condition_values_nice_names( $values ) {
		$return = [];
		if ( count( $values ) > 0 ) {
			foreach ( $values as $coupon_id ) {
				$return[ $coupon_id ] = get_the_title( $coupon_id );
			}
		}

		return $return;
	}

	public function get_search_type_name() {
		return 'coupon_rule';
	}

	public function get_search_results( $term ) {
		$array = array();
		if ( isset( $term ) && '' !== $term ) {
			$args = array(
				'post_type'     => 'shop_coupon',
				'post_per_page' => 2,
				'paged'         => 1,
				's'             => $term,
			);

			$posts = get_posts( $args );
			if ( $posts && is_array( $posts ) && count( $posts ) > 0 ) {
				foreach ( $posts as $post ) :
					setup_postdata( $post );
					$array[] = array(
						'id'   => (string) $post->ID,
						'text' => $post->post_title,
					);
				endforeach;
			}
		}

		wp_send_json( array(
			'results' => $array,
		) );
	}

	public function is_match( $rule_data ) {
		global $wpdb;
		$type  = $rule_data['operator'];
		$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		if ( version_compare( WC()->version, 3.7, '>=' ) ) {
			$used_coupons = $order->get_coupon_codes();
		} else {
			$used_coupons = $order->get_used_coupons();
		}

		if ( empty( $used_coupons ) ) {
			if ( 'all' === $type || 'any' === $type ) {
				$res = false;
			} else {
				$res = true;
			}

			return $this->return_is_match( $res, $rule_data );
		}

		$used_coupons_ids = [];
		foreach ( $used_coupons as $coupon ) {
			$used_coupons_ids[] = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' LIMIT 1;", $coupon ) );
		}
		switch ( $type ) {
			case 'all':
				if ( is_array( $rule_data['condition'] ) && is_array( $used_coupons_ids ) ) {
					$result = count( array_intersect( $rule_data['condition'], $used_coupons_ids ) ) === count( $rule_data['condition'] );
				}
				break;
			case 'any':
				if ( is_array( $rule_data['condition'] ) && is_array( $used_coupons_ids ) ) {
					$result = count( array_intersect( $rule_data['condition'], $used_coupons_ids ) ) >= 1;
				}
				break;

			case 'none':
				if ( is_array( $rule_data['condition'] ) && is_array( $used_coupons_ids ) ) {
					$result = count( array_intersect( $rule_data['condition'], $used_coupons_ids ) ) === 0;
				}
				break;

			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

	public function ui_view() {
		esc_html_e( 'Order\'s coupons ', 'wp-marketing-automations' );
		?>
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %> <% var chosen = []; %>
        <% _.each(condition, function( value, key ){ %>
        <% chosen.push(uiData[value]); %>

        <% }); %>
        <%= chosen.join("/ ") %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'any'  => __( 'matches any of', 'wp-marketing-automations' ),
			'all'  => __( 'matches all of ', 'wp-marketing-automations' ),
			'none' => __( 'matches none of', 'wp-marketing-automations' ),
		);

		return $operators;
	}


}

class BWFAN_Rule_Order_Payment_Gateway extends BWFAN_Rule_Base {
	public $supports = array( 'order' );

	public function __construct() {
		parent::__construct( 'order_payment_gateway' );
	}

	public function get_possible_rule_values() {
		$result = array();
		foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
			if ( 'yes' === $gateway->enabled ) {
				$result[ $gateway->id ] = $gateway->get_title();
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data ) {
		$type    = $rule_data['operator'];
		$order   = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$payment = BWFAN_WooCommerce_Compatibility::get_payment_gateway_from_order( $order );

		if ( empty( $payment ) ) {
			return $this->return_is_match( false, $rule_data );
		}

		switch ( $type ) {
			case 'is':
				$result = in_array( $payment, $rule_data['condition'], true );
				break;
			case 'is_not':
				$result = ! in_array( $payment, $rule_data['condition'], true );
				break;
			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

	public function ui_view() {
		esc_html_e( 'Order\'s payment gateway ', 'wp-marketing-automations' );
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
		$operators = array(
			'is'     => __( 'is', 'wp-marketing-automations' ),
			'is_not' => __( 'is not', 'wp-marketing-automations' ),
		);

		return $operators;
	}

}

class BWFAN_Rule_Order_Shipping_Country extends BWFAN_Rule_Country {

	public $supports = array( 'cart', 'order' );

	public function __construct() {
		parent::__construct( 'order_shipping_country' );
	}

	public function get_objects_country() {
		$order            = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$shipping_country = BWFAN_WooCommerce_Compatibility::get_shipping_country_from_order( $order );

		if ( empty( $shipping_country ) ) {
			return false;
		}

		$shipping_country = array( $shipping_country );

		return $shipping_country;
	}


}

class BWFAN_Rule_Order_Shipping_Method extends BWFAN_Rule_Base {
	public $supports = array( 'cart', 'order' );

	public function __construct() {
		parent::__construct( 'order_shipping_method' );
	}

	public function get_possible_rule_values() {
		$result = array();

		foreach ( WC()->shipping()->get_shipping_methods() as $method_id => $method ) {
			// get_method_title() added in WC 2.6
			$result[ $method_id ] = is_callable( array( $method, 'get_method_title' ) ) ? $method->get_method_title() : $method->get_title();
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data ) {
		$type    = $rule_data['operator'];
		$order   = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$methods = array();

		foreach ( $order->get_shipping_methods() as $method ) {
			// extract method slug only, discard instance id
			$split = strpos( $method['method_id'], ':' );
			if ( $split ) {
				$methods[] = substr( $method['method_id'], 0, $split );
			} else {
				$methods[] = $method['method_id'];
			}
		}

		switch ( $type ) {
			case 'any':
				if ( is_array( $rule_data['condition'] ) && is_array( $methods ) ) {
					$result = count( array_intersect( $rule_data['condition'], $methods ) ) >= 1;
				}
				break;
			case 'none':
				if ( is_array( $rule_data['condition'] ) && is_array( $methods ) ) {
					$result = count( array_intersect( $rule_data['condition'], $methods ) ) === 0;
				}
				break;

			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

	public function ui_view() {
		esc_html_e( 'Order\'s shipping method', 'wp-marketing-automations' );
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
		$operators = array(
			'any'  => __( 'matches any of', 'wp-marketing-automations' ),
			'none' => __( 'matches none of ', 'wp-marketing-automations' ),
		);

		return $operators;
	}

}

class BWFAN_Rule_Order_Billing_Country extends BWFAN_Rule_Country {
	public $supports = array( 'cart', 'order' );

	public function __construct() {
		parent::__construct( 'order_billing_country' );
	}

	public function get_objects_country() {
		$order           = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$billing_country = BWFAN_WooCommerce_Compatibility::get_billing_country_from_order( $order );

		if ( empty( $billing_country ) ) {
			return false;
		}

		$billing_country = array( $billing_country );

		return $billing_country;
	}

	public function ui_view() {
		esc_html_e( 'Order\'s Billing Country', 'wp-marketing-automations' );
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

class BWFAN_Rule_Order_Custom_Field extends BWFAN_Rule_Custom_Field {

	public function __construct() {
		parent::__construct( 'order_custom_field' );
	}

	public function get_possible_value( $key ) {
		$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$value = $order->get_meta( $key );

		return $value;
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'is'     => __( 'is', 'wp-marketing-automations' ),
			'is_not' => __( 'is not', 'wp-marketing-automations' ),
		);

		return $operators;
	}
}

class BWFAN_Rule_Order_Coupon_Text_Match extends BWFAN_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_coupon_text_match' );
	}

	public function conditions_view() {
		$condition_input_type = $this->get_condition_input_type();
		$values               = $this->get_possible_rule_values();
		$value_args           = array(
			'input'       => $condition_input_type,
			'name'        => 'bwfan_rule[<%= groupId %>][<%= ruleId %>][condition]',
			'choices'     => $values,
			'placeholder' => __( 'Enter Few Characters...', 'wp-marketing-automations' ),
		);

		bwfan_Input_Builder::create_input_field( $value_args );
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function is_match( $rule_data ) {
		$order = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		if ( version_compare( WC()->version, 3.7, '>=' ) ) {
			$used_coupons = $order->get_coupon_codes();
		} else {
			$used_coupons = $order->get_used_coupons();
		}

		return $this->return_is_match( BWFAN_Common::validate_string_multi( $used_coupons, $rule_data['operator'], $rule_data['condition'] ), $rule_data );
	}

	public function ui_view() {
		?>
        Order's coupon text
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %>
        <%= condition %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'contains'    => __( 'any contains', 'wp-marketing-automations' ),
			'is'          => __( 'any matches exactly', 'wp-marketing-automations' ),
			'starts_with' => __( 'starts with', 'wp-marketing-automations' ),
			'ends_with'   => __( 'ends with', 'wp-marketing-automations' ),
		);

		return $operators;
	}
}

class BWFAN_Rule_Order_Note_Text_Match extends BWFAN_Rule_Base {

	public function __construct() {
		parent::__construct( 'order_note_text_match' );
	}

	public function conditions_view() {
		$condition_input_type = $this->get_condition_input_type();
		$values               = $this->get_possible_rule_values();
		$value_args           = array(
			'input'       => $condition_input_type,
			'name'        => 'bwfan_rule[<%= groupId %>][<%= ruleId %>][condition]',
			'choices'     => $values,
			'placeholder' => __( 'Enter Few Characters...', 'wp-marketing-automations' ),
		);

		bwfan_Input_Builder::create_input_field( $value_args );
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function is_match( $rule_data ) {
		$order_note = BWFAN_Core()->rules->getRulesData( 'wc_order_note' );

		return $this->return_is_match( BWFAN_Common::validate_string( $order_note, $rule_data['operator'], $rule_data['condition'] ), $rule_data );
	}

	public function ui_view() {
		?>
        Order's notes text
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %>
        <%= condition %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'contains'    => __( 'any contains', 'wp-marketing-automations' ),
			'is'          => __( 'any matches exactly', 'wp-marketing-automations' ),
			'starts_with' => __( 'starts with', 'wp-marketing-automations' ),
			'ends_with'   => __( 'ends with', 'wp-marketing-automations' ),
		);

		return $operators;
	}
}

class BWFAN_Rule_Order_Status_Change extends BWFAN_Rule_Base {
	public $supports = array( 'order' );

	public function __construct() {
		parent::__construct( 'order_status_change' );
	}

	public function get_possible_rule_values() {
		return wc_get_order_statuses();
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data ) {
		$type         = $rule_data['operator'];
		$order        = BWFAN_Core()->rules->getRulesData( 'wc_order' );
		$order_status = 'wc-' . $order->get_status();

		switch ( $type ) {
			case 'is':
				$result = in_array( $order_status, $rule_data['condition'], true );
				break;
			case 'is_not':
				$result = ! in_array( $order_status, $rule_data['condition'], true );
				break;
			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

	public function ui_view() {
		esc_html_e( 'Order\'s status ', 'wp-marketing-automations' );
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
		$operators = array(
			'is'     => __( 'is', 'wp-marketing-automations' ),
			'is_not' => __( 'is not', 'wp-marketing-automations' ),
		);

		return $operators;
	}


}

class BWFAN_Rule_Comment_Count extends BWFAN_Rule_Base {

	public function __construct() {
		parent::__construct( 'comment_count' );
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function is_match( $rule_data ) {
		$comment_details      = BWFAN_Core()->rules->getRulesData( 'wc_comment' );
		$comment_rating_count = $comment_details['rating_number'];
		$count                = absint( $comment_rating_count );
		$value                = absint( $rule_data['condition'] );

		switch ( $rule_data['operator'] ) {
			case '==':
				$result = $count === $value;
				break;
			case '!=':
				$result = $count !== $value;
				break;
			case '>':
				$result = $count > $value;
				break;
			case '<':
				$result = $count < $value;
				break;
			case '>=':
				$result = $count >= $value;
				break;
			case '<=':
				$result = $count <= $value;
				break;
			default:
				$result = false;
				break;
		}

		return $this->return_is_match( $result, $rule_data );
	}

	public function ui_view() {
		esc_html_e( 'Review Rating count', 'wp-marketing-automations' );
		?>
        <% var ops = JSON.parse('<?php echo wp_json_encode( $this->get_possible_rule_operators() ); ?>'); %>

        <%= ops[operator] %>
        <%= condition %>
		<?php
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'wp-marketing-automations' ),
			'!=' => __( 'is not equal to', 'wp-marketing-automations' ),
			'>'  => __( 'is greater than', 'wp-marketing-automations' ),
			'<'  => __( 'is less than', 'wp-marketing-automations' ),
			'>=' => __( 'is greater or equal to', 'wp-marketing-automations' ),
			'<=' => __( 'is less or equal to', 'wp-marketing-automations' ),
		);

		return $operators;
	}

}