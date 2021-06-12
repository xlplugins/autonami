<?php
/**
 * WooFunnels customer and contact DB operations
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WooFunnels_DB_Operations
 */
class WooFunnels_DB_Operations {
	/**
	 * @var $ins
	 */
	public static $ins;

	/**
	 * @var wp_db
	 */
	public $wp_db;

	/**
	 * @var $contact_tbl
	 */
	public $contact_tbl;

	/**
	 * @var $customer_tbl
	 */
	public $customer_tbl;

	/**
	 * WooFunnels_DB_Operations constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wp_db            = $wpdb;
		$this->contact_tbl      = $this->wp_db->prefix . 'bwf_contact';
		$this->contact_meta_tbl = $this->wp_db->prefix . 'bwf_contact_meta';
		$this->customer_tbl     = $this->wp_db->prefix . 'bwf_wc_customers';
	}

	/**
	 * @return WooFunnels_DB_Operations
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	/**
	 * Inserting a new row in bwf_contact table
	 *
	 * @param $customer
	 *
	 * @return int
	 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
	 */
	public function insert_contact( $contact ) {
		if ( isset( $contact['id'] ) ) {
			unset( $contact['id'] );
		}
		$inserted = $this->wp_db->insert( $this->contact_tbl, $contact );
		$lastId   = 0;
		if ( $inserted ) {
			$lastId = $this->wp_db->insert_id;
		}
		if ( $this->wp_db->last_error !== '' ) {
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( 'Get last error in insert_contact: ' . print_r( $this->wp_db->last_error, true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		return $lastId;
	}

	/**
	 * Updating a contact
	 *
	 * @param $contact
	 *
	 * @return array|object|null
	 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
	 */
	public function update_contact( $contact ) {

		$update_data = array();

		foreach ( is_array( $contact ) ? $contact : array() as $key => $value ) {
			$update_data[ $key ] = $value;
		}

		$this->wp_db->update( $this->contact_tbl, $update_data, array( 'id' => $contact['id'] ) );

		if ( $this->wp_db->last_error !== '' ) {
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Get last error in update_customer for cid: {$contact['id']} " . print_r( $this->wp_db->last_error, true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	/**
	 * Getting contacts
	 *
	 * @return array|object|null
	 */
	public function get_all_contacts() {
		$sql = "SELECT * FROM `$this->contact_tbl`";

		$contacts = $this->wp_db->get_results( $sql ); //WPCS: unprepared SQL ok

		return $contacts;
	}

	/**
	 * Getting contacts
	 *
	 * @return array|object|null
	 */
	public function get_all_contacts_count() {
		$sql = "SELECT COUNT(id) FROM `$this->contact_tbl`";

		$contacts = $this->wp_db->get_var( $sql ); //WPCS: unprepared SQL ok

		return $contacts;
	}

	/**
	 * Getting contacts based on given criteria
	 *
	 * @param $args
	 *
	 * @return array|object|null
	 */
	public function get_contacts( $args ) {
		$query = array();

		$query['select'] = 'SELECT * ';

		$query['from'] = "FROM {$this->contact_tbl} AS contact";

		$query['where'] = '';

		$query['where'] = ' WHERE 1=1 ';

		if ( ! empty( $args['min_creation_date'] ) ) {
			$query['where'] .= "AND contact.creation_date >= '" . gmdate( 'Y-m-d H:i:s', $args['min_creation_date'] ) . "'"; //phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		}

		if ( ! empty( $args['max_creation_date'] ) ) {
			$query['where'] .= "AND contact.creation_date < '" . gmdate( 'Y-m-d H:i:s', $args['max_creation_date'] ) . "'"; //phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		}

		if ( - 1 !== $args['contact_limit'] ) {
			$query['limit'] = "LIMIT {$args['contact_limit']}";
		}

		$query = implode( ' ', $query );

		$contacts = $this->wp_db->get_results( $query ); //WPCS: unprepared SQL ok

		return $contacts;
	}


	/**
	 * Get contact for given uid id if it exists
	 */
	public function get_contact( $uid ) {
		$sql = "SELECT * FROM `$this->contact_tbl` WHERE `uid` = '$uid' ";

		$contact = $this->wp_db->get_row( $sql ); //WPCS: unprepared SQL ok

		return $contact;
	}

	/**
	 * Get contact for given wpid id if it exists
	 */
	public function get_contact_by_wpid( $wp_id ) {
		$sql     = "SELECT * FROM `$this->contact_tbl` WHERE `wpid` = '$wp_id' ";
		$contact = $this->wp_db->get_row( $sql ); //WPCS: unprepared SQL ok

		return $contact;
	}

	/**
	 * Get contact for given email id if it exists
	 */
	public function get_contact_by_email( $email ) {
		$sql = "SELECT * FROM `$this->contact_tbl` WHERE `email` = '$email' ";

		$contact = $this->wp_db->get_row( $sql ); //WPCS: unprepared SQL ok

		return $contact;
	}


	/**
	 * Get contact for given contact id if it exists
	 */
	public function get_contact_by_contact_id( $contact_id ) {
		$sql = "SELECT * FROM `$this->contact_tbl` WHERE `id` = '$contact_id' ";

		$contact = $this->wp_db->get_row( $sql ); //WPCS: unprepared SQL ok

		return $contact;
	}

	/**
	 * Get all contact meta key value for a given contact id
	 *
	 * @param $contact_id
	 *
	 * @return array|object|null
	 */
	public function get_contact_metadata( $contact_id ) {
		$sql        = "SELECT `meta_key`, `meta_value` FROM `$this->contact_meta_tbl` WHERE `contact_id` = '$contact_id'";
		$meta_value = $this->wp_db->get_results( $sql ); //WPCS: unprepared SQL ok

		return $meta_value;
	}

	/**
	 * @param $contact_id
	 * @param $contact_meta
	 */
	public function save_contact_meta( $contact_id, $contact_meta ) {

		foreach ( is_object( $contact_meta ) ? $contact_meta : array() as $meta_key => $meta_value ) {

			$meta_exists = false;
			$meta_value  = ( is_array( $meta_value ) ) ? maybe_serialize( $meta_value ) : $meta_value;

			if ( $this->meta_id_exists( $contact_id, $meta_key ) ) {
				$meta_exists = true;
				$this->wp_db->update( $this->contact_meta_tbl, array(
					'meta_value' => $meta_value,    //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				), array(
					'meta_key'   => $meta_key,  //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'contact_id' => $contact_id,
				), array(
					'%s',    // meta_value
				), array( '%s', '%s' ) );
			}
			if ( ! $meta_exists ) {
				$contact_meta = array(
					'contact_id' => $contact_id,
					'meta_key'   => $meta_key, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value' => $meta_value, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				);
				$this->wp_db->insert( $this->contact_meta_tbl, $contact_meta ); // WPCS: unprepared SQL ok
			}
		}

	}

	/**
	 * @param $contact_id
	 * @param $meta_key
	 */
	public function meta_id_exists( $contact_id, $meta_key ) {
		$sql     = "SELECT `meta_id` FROM `$this->contact_meta_tbl` WHERE `contact_id` = '$contact_id' AND `meta_key` = '$meta_key'";
		$meta_id = $this->wp_db->get_var( $sql ); // WPCS: unprepared SQL ok

		return ( ! empty( $meta_id ) && $meta_id > 0 ) ? true : false;
	}

	/**
	 * @param $contact_id
	 * @param $meta_key
	 * @param $meta_value
	 *
	 * @return int
	 */
	public function update_contact_meta( $contact_id, $meta_key, $meta_value ) {
		$db_meta_value = $this->get_contact_meta_value( $contact_id, $meta_key );

		if ( is_array( $meta_value ) || is_object( $meta_value ) ) {

			$meta_value_ids = empty( $db_meta_value ) ? array() : json_decode( $db_meta_value, true );


			if ( false === is_array( $meta_value_ids ) ) {
				$meta_value_ids = [];
			}

			if ( false === is_array( $meta_value ) ) {
				$meta_value = [];
			}
			$meta_value = wp_json_encode( array_unique( array_merge( $meta_value_ids, $meta_value ) ) );

		}
		$meta_exists = false;

		if ( $this->meta_id_exists( $contact_id, $meta_key ) ) {
			$meta_exists = true;
			$this->wp_db->update( $this->contact_meta_tbl, array(
				'meta_value' => $meta_value,    //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			), array(
				'meta_key'   => $meta_key, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'contact_id' => $contact_id,
			), array(
				'%s',    // meta_value
			), array( '%s', '%s' ) );
		}
		if ( ! $meta_exists ) {
			$contact_meta = array(
				'contact_id' => $contact_id,
				'meta_key'   => $meta_key,      //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => $meta_value,    //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			);
			$inserted     = $this->wp_db->insert( $this->contact_meta_tbl, $contact_meta );
			$last_id      = 0;
			if ( $inserted ) {
				$last_id = $this->wp_db->insert_id;
			}

			return $last_id;
		}
	}

	/**
	 * Get contact meta for a given contact id and meta key
	 *
	 * @param $contact_id
	 *
	 * @return string|null
	 */
	public function get_contact_meta_value( $contact_id, $meta_key ) {
		$sql        = "SELECT `meta_value` FROM `$this->contact_meta_tbl` WHERE `contact_id` = '$contact_id' AND `meta_key` = '$meta_key'";
		$meta_value = $this->wp_db->get_var( $sql ); // WPCS: unprepared SQL ok

		return $meta_value;
	}

	/**
	 * Inserting a new row in bwf_customer table
	 *
	 * @param $customer
	 *
	 * @return int
	 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
	 */
	public function insert_customer( $customer ) {

		$customer_data = array(
			'cid'                     => $customer['cid'],
			'l_order_date'            => $customer['l_order_date'],
			'f_order_date'            => $customer['f_order_date'],
			'total_order_count'       => $customer['total_order_count'],
			'total_order_value'       => $customer['total_order_value'],
			'aov'                     => $customer['aov'],
			'purchased_products'      => $customer['purchased_products'],
			'purchased_products_cats' => $customer['purchased_products_cats'],
			'purchased_products_tags' => $customer['purchased_products_tags'],
			'used_coupons'            => $customer['used_coupons'],
		);

		$inserted = $this->wp_db->insert( $this->customer_tbl, $customer_data ); // WPCS: unprepared SQL ok

		$lastId = 0;
		if ( $inserted ) {
			$lastId = $this->wp_db->insert_id;
		}

		if ( $this->wp_db->last_error !== '' ) {
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( 'Get last error in insert_customer: ' . print_r( $this->wp_db->last_error, true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		return $lastId;
	}

	/**
	 * Updating a customer
	 *
	 * @param $customer
	 *
	 * @return array|object|null
	 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
	 */
	public function update_customer( $customer ) {

		$update_data = array();

		foreach ( is_array( $customer ) ? $customer : array() as $key => $value ) {
			$update_data[ $key ] = $value;
		}

		$this->wp_db->update( $this->customer_tbl, $update_data, array( 'id' => $customer['id'] ) );

		if ( $this->wp_db->last_error !== '' ) {
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Get last error in update_customer for cid: {$customer['cid']} " . print_r( $this->wp_db->last_error, true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	/**
	 * Getting customers
	 *
	 * @return array|object|null
	 */
	public function get_all_customers() {
		$sql = "SELECT * FROM `$this->customer_tbl`";

		$customers = $this->wp_db->get_results( $sql ); // WPCS: unprepared SQL ok

		return $customers;
	}

	/**
	 * Getting customers based on given criteria
	 *
	 * @param $args
	 *
	 * @return array|object|null
	 */
	public function get_customers( $args ) {
		$query = array();

		$query['select'] = 'SELECT * ';

		$query['from'] = "FROM {$this->customer_tbl} AS customer";

		$query['where'] = '';

		$query['where'] = ' WHERE 1=1 ';

		$query['where'] .= '
                AND     customer.total_order_count >= ' . $args['min_order_count'] . '
                AND     customer.total_order_count < ' . $args['max_order_count'] . '
                AND     customer.total_order_value >= ' . $args['min_order_value'] . '
                AND     customer.total_order_value < ' . $args['max_order_value'] . '
            ';

		if ( ! empty( $args['min_last_order_date'] ) ) {
			$query['where'] .= "
				AND 	customer.l_order_date >= '" . gmdate( 'Y-m-d H:i:s', $args['min_last_order_date'] ) . "'
			";
		}

		if ( ! empty( $args['max_last_order_date'] ) ) {
			$query['where'] .= "
				AND 	customer.l_order_date < '" . gmdate( 'Y-m-d H:i:s', $args['max_last_order_date'] ) . "'
			";
		}

		if ( ! empty( $args['min_creation_date'] ) ) {
			$query['where'] .= "
				AND 	customer.creation_date >= '" . gmdate( 'Y-m-d H:i:s', $args['min_creation_date'] ) . "'";
		}

		if ( ! empty( $args['max_creation_date'] ) ) {
			$query['where'] .= "
				AND 	customer.creation_date < '" . gmdate( 'Y-m-d H:i:s', $args['max_creation_date'] ) . "'";
		}

		if ( - 1 !== $args['customer_limit'] ) {
			$query['limit'] = "LIMIT {$args['customer_limit']}";
		}

		$query = implode( ' ', $query );

		$customers = $this->wp_db->get_results( $query ); // WPCS: unprepared SQL ok

		return $customers;
	}

	/**
	 * Get customer for given uid id if it exists
	 */
	public function get_customer( $uid ) {
		$sql = "SELECT * FROM `$this->customer_tbl` WHERE `uid` = '$uid' ";

		$customer = $this->wp_db->get_row( $sql ); // WPCS: unprepared SQL ok

		return $customer;
	}


	/**
	 * Get customer for given cid id if it exists
	 */
	public function get_customer_by_cid( $cid ) {
		$sql = "SELECT * FROM `$this->customer_tbl` WHERE `cid` = '$cid' ";

		$customer = $this->wp_db->get_row( $sql ); // WPCS: unprepared SQL ok

		return $customer;
	}

	/**
	 * Get customer for given customer id if it exists
	 */
	public function get_customer_by_customer_id( $customer_id ) {
		$sql = "SELECT * FROM `$this->customer_tbl` WHERE `id` = '$customer_id' ";

		$customer = $this->wp_db->get_row( $sql ); // WPCS: unprepared SQL ok

		return $customer;
	}

	/**
	 * Deleting a meta key from contact meta table
	 *
	 * @param $cid
	 * @param $meta_key
	 */
	public function delete_contact_meta( $cid, $meta_key ) {
		if ( $this->meta_id_exists( $cid, $meta_key ) ) {
			$this->wp_db->delete( $this->contact_meta_tbl, array(
				'contact_id' => $cid,
				'meta_key'   => $meta_key, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			) );
		}
	}
}

WooFunnels_DB_Operations::get_instance();