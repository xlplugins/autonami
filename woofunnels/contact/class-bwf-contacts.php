<?php
/**
 * Contact Controller Class
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BWF_Contacts
 *
 */
class BWF_Contacts {
	/**
	 * @var static instance
	 */
	private static $ins;
	/**
	 * public db_operations $db_operations
	 */
	public $db_operations;
	public $child_entities;

	public $cached_contact_obj;

	/**
	 * Get the contact details for the email passed if this uid exits other create a new contact with this email
	 *
	 * @param  $email
	 */
	public function __construct() {
		$this->db_operations = WooFunnels_DB_Operations::get_instance();
		$this->get_registerd_child_entities();

		$this->cached_contact_obj = [
			'cid'   => [],
			'uid'   => [],
			'email' => [],
			'wp_id' => [],
			'phone' => [],
		];
	}

	/**
	 * @return mixed|void
	 */
	public static function get_registerd_child_entities() {
		$entities = apply_filters( 'bwf_child_entities', array( 'customer' => 'WooFunnels_Customer' ) );

		return $entities;
	}

	/**
	 * @return BWF_Contacts
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	/**
	 * Get contacts based on different criteria
	 */
	public function get_contacts( $args = array() ) {
		$default_args = array(
			'min_creation_date' => '',
			'max_creation_date' => '',
		);

		$args = wp_parse_args( $args, $default_args );

		if ( ! empty( $args['min_creation_date'] ) ) {
			$args['min_creation_date'] = strtotime( 'midnight', strtotime( sanitize_text_field( $args['min_creation_date'] ) ) );
		}

		if ( ! empty( $args['max_creation_date'] ) ) {
			$args['max_creation_date'] = strtotime( 'midnight', strtotime( sanitize_text_field( $args['max_creation_date'] ) ) );
		}

		$customers = $this->db_operations->get_contacts( $args );

		return $customers;
	}

	/**
	 * get contact by given field
	 */
	public function get_contact_by( $field, $value ) {
		if ( 'id' === $field ) {
			return new WooFunnels_Contact( '', '', '', $value );
		}

		if ( 'wpid' === $field ) {
			return new WooFunnels_Contact( $value );
		}

		if ( 'email' === $field ) {
			return new WooFunnels_Contact( '', $value );
		}

		if ( 'phone' === $field ) {
			return new WooFunnels_Contact( '', '', $value );
		}

		if ( 'uid' === $field ) {
			return new WooFunnels_Contact( '', '', '', '', $value );
		}
	}

	/**
	 * Getting date range
	 *
	 * @param $range
	 *
	 */
	public function get_date_range( $range ) {
		$result = array();

		$result['start_date'] = max( strtotime( '-20 years' ), strtotime( sanitize_text_field( $range['start_date'] ) ) );

		$result['end_date'] = strtotime( 'midnight', current_time( 'timestamp' ) );

		if ( ! empty( $range['end_date'] ) ) {
			$result['end_date'] = strtotime( 'midnight', strtotime( sanitize_text_field( $range['end_date'] ) ) );
		}

		return $result;
	}
}
