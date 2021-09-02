<?php
/**
 * Customer Class
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WooFunnels_Customer
 */
class WooFunnels_Customer {
	/**
	 * public db_operations $db_operations
	 */
	public $db_operations;

	/**
	 * public id $id
	 */
	public $id;

	/**
	 * public cid $cid
	 */
	public $cid;

	/**
	 * public contact $contact
	 */
	public $contact;


	/**
	 * @var $db_customer
	 */
	public $db_customer;

	/**
	 * Get the customer details for the contact object passed if this contact id exits otherwise create a new customer
	 * WooFunnels_Customer constructor.
	 *
	 * @param $cid
	 *
	 */
	public function __construct( $contact ) {
		$this->db_operations = WooFunnels_DB_Operations::get_instance();

		$this->cid = $contact->get_id();

		$this->contact = $contact;

		if ( empty( $this->cid ) || ( $this->cid < 1 ) ) {
			return;
		}

		$this->db_customer = $this->get_customer_by_cid( $this->cid );

		if ( isset( $this->db_customer->id ) && $this->db_customer->id > 0 ) {
			$this->id = $this->db_customer->id;
		}


	}

	/**
	 * Get customer by cid
	 *
	 * @param $wpid
	 *
	 * @return mixed
	 */
	public function get_customer_by_cid( $cid ) {
		return $this->db_operations->get_customer_by_cid( $cid );
	}

	/**
	 * Get customer created date
	 */
	public function get_creation_date() {
		$creation_date    = ( isset( $this->creation_date ) && ! empty( $this->creation_date ) ) ? $this->creation_date : '';
		$db_creation_date = ( isset( $this->db_customer->creation_date ) && ( $this->db_customer->creation_date > 0 ) ) ? $this->db_customer->creation_date : current_time( 'mysql' );

		return empty( $creation_date ) ? $db_creation_date : $creation_date;
	}

	/**
	 * Set customer last order date
	 *
	 * @param $date
	 */
	public function set_l_order_date( $date ) {
		$this->l_order_date = empty( $date ) ? $this->get_l_order_date() : $date;
	}

	/**
	 * Get customer last order date
	 */
	public function get_l_order_date() {
		$order_date = ( isset( $this->l_order_date ) && ! empty( $this->l_order_date ) ) ? $this->l_order_date : '';
		$db_data    = ( isset( $this->db_customer->l_order_date ) && ! empty( $this->db_customer->l_order_date ) ) ? $this->db_customer->l_order_date : '0000-00-00';

		return empty( $order_date ) ? $db_data : $order_date;
	}

	/**
	 * Set customer last order date
	 *
	 * @param $date
	 */
	public function set_f_order_date( $date ) {
		$this->f_order_date = empty( $date ) ? $this->get_f_order_date() : $date;
	}

	/**
	 * Get customer last order date
	 */
	public function get_f_order_date() {
		$order_date = ( isset( $this->f_order_date ) && ! empty( $this->f_order_date ) ) ? $this->f_order_date : '';
		$db_data    = ( isset( $this->db_customer->f_order_date ) && ! empty( $this->db_customer->f_order_date ) ) ? $this->db_customer->f_order_date : '0000-00-00';

		return empty( $order_date ) ? $db_data : $order_date;
	}

	/**
	 * Set total order count
	 *
	 * @param $count
	 */
	public function set_total_order_count( $count ) {
		$this->total_order_count = ( $count >= 0 ) ? $count : $this->get_total_order_count();
	}

	/**
	 * Get total order count
	 */
	public function get_total_order_count() {
		$total_order = ( isset( $this->total_order_count ) && ! empty( $this->total_order_count ) ) ? $this->total_order_count : 0;
		$db_data     = ( isset( $this->db_customer->total_order_count ) && ! empty( $this->db_customer->total_order_count ) ) ? $this->db_customer->total_order_count : 0;

		return empty( $total_order ) ? $db_data : $total_order;
	}

	/**
	 * Set total order value
	 *
	 * @param $value
	 */
	public function set_total_order_value( $value ) {
		$this->total_order_value = ( $value >= 0 ) ? $value : $this->get_total_order_value();
	}

	/**
	 * Get total order value
	 */
	public function get_total_order_value() {
		$total_order_value = ( isset( $this->total_order_value ) && ! empty( $this->total_order_value ) ) ? $this->total_order_value : 0;
		$db_data           = ( isset( $this->db_customer->total_order_value ) && ! empty( $this->db_customer->total_order_value ) ) ? $this->db_customer->total_order_value : 0;

		return empty( $total_order_value ) ? $db_data : $total_order_value;
	}

	/**
	 * Set customer AOV
	 *
	 * @param $value
	 */
	public function set_aov( $value ) {
		$this->aov = $value;
	}

	/**
	 * Get customer AOV
	 *
	 * @return int
	 */
	public function get_aov() {
		$aov     = ( isset( $this->aov ) && ! empty( $this->aov ) ) ? $this->aov : 0;
		$db_data = ( isset( $this->db_customer->aov ) && ! empty( $this->db_customer->aov ) ) ? $this->db_customer->aov : 0;

		return empty( $aov ) ? $db_data : $aov;
	}

	/**
	 * Set purchased products
	 *
	 * @param $products
	 */
	public function set_purchased_products( $products ) {
		$this->purchased_products = empty( $products ) ? $this->get_purchased_products() : $products;
	}

	/**
	 * Get purchased products
	 *
	 */
	public function get_purchased_products() {
		$products    = ( isset( $this->purchased_products ) && ! empty( $this->purchased_products ) ) ? $this->purchased_products : array();
		$db_products = isset( $this->db_customer->purchased_products ) ? $this->db_customer->purchased_products : array();
		if ( ! empty( $db_products ) && ! is_array( $db_products ) ) {
			$db_products = json_decode( $db_products, true );
		}
		if ( ! empty( $products ) && ! is_array( $products ) ) {
			$products = json_decode( $products, true );
		}

		$arr = empty( $products ) ? $db_products : $products;
		if ( is_array( $arr ) && count( $arr ) > 0 ) {
			$arr = array_map( 'intval', $arr );
		}

		return $arr;
	}

	/**
	 * Set purchased product categories
	 *
	 * @param $cats
	 */
	public function set_purchased_products_cats( $cats ) {
		$this->purchased_products_cats = empty( $cats ) ? $this->get_purchased_products_cats() : $cats;
	}

	/**
	 * Get purchased product categories
	 *
	 */
	public function get_purchased_products_cats() {
		$purchased_products_cats    = ( isset( $this->purchased_products_cats ) && ! empty( $this->purchased_products_cats ) ) ? $this->purchased_products_cats : array();
		$db_purchased_products_cats = isset( $this->db_customer->purchased_products_cats ) ? $this->db_customer->purchased_products_cats : array();
		if ( ! empty( $db_purchased_products_cats ) && ! is_array( $db_purchased_products_cats ) ) {
			$db_purchased_products_cats = json_decode( $db_purchased_products_cats, true );
		}
		if ( ! empty( $purchased_products_cats ) && ! is_array( $purchased_products_cats ) ) {
			$purchased_products_cats = json_decode( $purchased_products_cats, true );
		}

		$arr = empty( $purchased_products_cats ) ? $db_purchased_products_cats : $purchased_products_cats;
		if ( is_array( $arr ) && count( $arr ) > 0 ) {
			$arr = array_map( 'intval', $arr );
		}

		return $arr;
	}

	/**
	 * Set purchased product tags
	 *
	 * @param $tags
	 */
	public function set_purchased_products_tags( $tags ) {
		$this->purchased_products_tags = empty( $tags ) ? $this->get_purchased_products_tags() : $tags;
	}

	/**
	 * Get purchased product tags
	 *
	 */
	public function get_purchased_products_tags() {
		$purchased_products_tags    = ( isset( $this->purchased_products_tags ) && ! empty( $this->purchased_products_tags ) ) ? $this->purchased_products_tags : array();
		$db_purchased_products_tags = isset( $this->db_customer->purchased_products_tags ) ? $this->db_customer->purchased_products_tags : array();
		if ( ! empty( $db_purchased_products_tags ) && ! is_array( $db_purchased_products_tags ) ) {
			$db_purchased_products_tags = json_decode( $db_purchased_products_tags, true );
		}
		if ( ! empty( $purchased_products_tags ) && ! is_array( $purchased_products_tags ) ) {
			$purchased_products_tags = json_decode( $purchased_products_tags, true );
		}

		$arr = empty( $purchased_products_tags ) ? $db_purchased_products_tags : $purchased_products_tags;
		if ( is_array( $arr ) && count( $arr ) > 0 ) {
			$arr = array_map( 'intval', $arr );
		}

		return $arr;
	}

	/**
	 * Set used coupons
	 *
	 * @param $state
	 */
	public function set_used_coupons( $coupons ) {
		$this->used_coupons = empty( $coupons ) ? $this->get_used_coupons() : $coupons;
	}

	/**
	 * Get customer used coupons
	 */
	public function get_used_coupons() {
		$coupons    = isset( $this->used_coupons ) ? $this->used_coupons : array();
		$db_coupons = isset( $this->db_customer->used_coupons ) ? $this->db_customer->used_coupons : array();
		if ( ! empty( $coupons ) && ! is_array( $coupons ) ) {
			$coupons = json_decode( $coupons, true );
		}
		if ( ! empty( $db_coupons ) && ! is_array( $db_coupons ) ) {
			$db_coupons = json_decode( $db_coupons, true );
		}

		return ! empty( $coupons ) ? $coupons : $db_coupons;
	}

	/**
	 * Set customer created date
	 *
	 * @param $date
	 */
	public function set_creation_date( $date ) {
		$this->creation_date = $date;
	}

	/**
	 * Updating customer table with set data
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 */
	public function save() {
		$customer                 = array();
		$customer['l_order_date'] = $this->get_l_order_date();
		$customer['f_order_date'] = $this->get_f_order_date();

		$customer['total_order_count'] = $this->get_total_order_count();
		$customer['total_order_value'] = $this->get_total_order_value();

		$customer['aov'] = $customer['total_order_value'] / absint( $customer['total_order_count'] );

		$purchased_products             = $this->get_purchased_products();
		$purchased_products             = array_map( 'strval', $purchased_products );
		$customer['purchased_products'] = wp_json_encode( $purchased_products );

		$purchased_products_cats             = $this->get_purchased_products_cats();
		$purchased_products_cats             = array_map( 'strval', $purchased_products_cats );
		$customer['purchased_products_cats'] = wp_json_encode( $purchased_products_cats );

		$purchased_products_tags             = $this->get_purchased_products_tags();
		$purchased_products_tags             = array_map( 'strval', $purchased_products_tags );
		$customer['purchased_products_tags'] = wp_json_encode( $purchased_products_tags );

		$customer['used_coupons'] = wp_json_encode( $this->get_used_coupons() );

		if ( ( $this->get_id() > 0 ) ) {
			$customer['id'] = $this->get_id();
			$this->db_operations->update_customer( $customer );
		} elseif ( empty( $this->get_id() ) ) {
			$customer['cid'] = $this->get_cid();

			$this->id = $this->db_operations->insert_customer( $customer );
		}
	}

	/**
	 * Get customer id
	 */
	public function get_id() {
		$id    = ( isset( $this->id ) && $this->id > 0 ) ? $this->id : 0;
		$db_id = ( isset( $this->db_customer->id ) && ( $this->db_customer->id > 0 ) ) ? $this->db_customer->id : 0;

		return ( $id > 0 ) ? $id : $db_id;
	}

	/**
	 * Set customer last order date
	 *
	 * @param $date
	 */
	public function set_id( $id ) {
		$this->id = empty( $id ) ? $this->id : $id;
	}

	/**
	 * Get customer cid
	 */
	public function get_cid() {
		$cid    = ( isset( $this->cid ) && ! empty( $this->cid ) ) ? $this->cid : '';
		$db_cid = ( isset( $this->db_customer->cid ) && ( $this->db_customer->cid > 0 ) ) ? $this->db_customer->cid : 0;

		return empty( $cid ) ? $db_cid : $cid;
	}

	/**
	 * Set customer last order date
	 *
	 * @param $date
	 */
	public function set_cid( $cid ) {
		$this->cid = empty( $cid ) ? $this->get_cid() : $cid;
	}

	/**
	 * Get customer by id
	 *
	 * @param $customer_id
	 *
	 * @return mixed
	 */
	public function get_customer_by_customer_id( $customer_id ) {
		return $this->db_operations->get_customer_by_customer_id( $customer_id );
	}

}
