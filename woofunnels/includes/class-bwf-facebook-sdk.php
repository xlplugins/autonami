<?php


class BWF_Facebook_Sdk {
	private static $instance = null;
	protected $container = array();
	protected $event_data = array();
	private $api_url = 'https://graph.facebook.com';
	private $version = '';
	private $pixel_id = '';
	private $event_name = '';
	private $time = '';
	private $test_event_code = '';
	private $partner_agent = '';
	private $body = [];
	private $response_body = null;
	private $access_token = '';

	public function __construct( $pixel_id, $access_token, $version = 'v11.0' ) {
		if ( ! empty( $pixel_id ) ) {
			$this->pixel_id = $pixel_id;
		}
		if ( ! empty( $access_token ) ) {
			$this->access_token = $access_token;
		}
		if ( ! empty( $version ) ) {
			$this->version = $version;
		}
		$this->time = time();

	}

	public static function create( $pixel_id, $access_token, $version = 'v11.0' ) {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $pixel_id, $access_token, $version );
		}

		return self::$instance;
	}

	public function set_event_data( $event_name, $event_data ) {
		$this->event_name = $event_name;
		$this->event_data = $event_data;
	}

	public function set_event_source_url( $url = '' ) {
		$this->source_url = $url;
	}

	public function execute() {
		$out_response = [ 'status' => false, 'errors' => [] ];
		if ( empty( $this->event_name ) ) {
			$out_response['errors'][] = 'Event Name is empty';
		}
		if ( empty( $this->event_data ) ) {
			$out_response['errors'][] = 'Event Data is empty';
		}

		if ( ! empty( $response['status'] ) ) {
			return $out_response;
		}

		$event_id = $this->get_event_id();
		$input    = [ 'event_name' => $this->event_name, 'event_time' => $this->get_time(), 'event_id' => $event_id ];

		if ( isset( $this->source_url ) ) {
			$input['event_source_url'] = $this->source_url;
		}
		$user_data = $this->get_user_data();
		if ( ! empty( $user_data ) ) {
			$input['user_data'] = $user_data;
		}

		$input['custom_data'] = $this->event_data;

		$body = [ 'data' => [ $input ] ];

		if ( ! empty( $this->test_event_code ) ) {
			$body['test_event_code'] = $this->test_event_code;
		}
		if ( ! empty( $this->partner_agent ) ) {
			$body['partner_agent'] = $this->partner_agent;
		}
		$headers    = [
			'Authorization' => 'Bearer ' . $this->access_token,
		];
		$this->body = $body;

		$post                = wp_remote_post( $this->get_api_url(), [
			'timeout'   => 2,
			'sslverify' => false,
			'body'      => $this->body,
			'headers'   => $headers
		] );
		$this->response_body = wp_remote_retrieve_body( $post );


		return array( 'request' => $this->get_request_body(), 'response' => $this->response_body );
	}

	public function get_event_id() {
		return $this->container['event_id'];
	}

	public function get_time() {
		return time();
	}

	public function get_user_data() {
		$normalized_payload                      = array();
		$normalized_payload['em']                = self::hash( $this->getEmail() );
		$normalized_payload['ph']                = self::hash( $this->getPhone() );
		$normalized_payload['ge']                = self::hash( $this->getGender() );
		$normalized_payload['db']                = self::hash( $this->getDateOfBirth() );
		$normalized_payload['ln']                = self::hash( $this->getLastName() );
		$normalized_payload['fn']                = self::hash( $this->getFirstName() );
		$normalized_payload['ct']                = self::hash( $this->getCity() );
		$normalized_payload['st']                = self::hash( $this->getState() );
		$normalized_payload['zp']                = self::hash( $this->getZipCode() );
		$normalized_payload['country']           = self::hash( $this->getCountryCode() );
		$normalized_payload['dobd']              = self::hash( $this->getDobd() );
		$normalized_payload['dobm']              = self::hash( $this->getDobm() );
		$normalized_payload['doby']              = self::hash( $this->getDoby() );
		$normalized_payload['client_ip_address'] = $this->getIpAddress();
		$normalized_payload['client_user_agent'] = $this->getHttpUserAgent();
		$normalized_payload['fbc']               = $this->getFbc();
		$normalized_payload['fbp']               = $this->getFbp();
		$normalized_payload                      = array_filter( $normalized_payload );

		return $normalized_payload;
	}

	/**
	 * @param string $data hash input data using SHA256 algorithm.
	 *
	 * @return string
	 */
	public static function hash( $data ) {
		if ( $data == null || self::isHashed( $data ) ) {
			return $data;
		}

		return hash( 'sha256', $data, false );
	}

	/**
	 * @param string $pii PII data to check if its hashed.
	 *
	 * @return bool
	 */
	public static function isHashed( $pii ) {
		// it could be sha256 or md5
		return preg_match( '/^[A-Fa-f0-9]{64}$/', $pii ) || preg_match( '/^[a-f0-9]{32}$/', $pii );
	}

	/**
	 * Gets an email address, in lowercase.
	 * @return string
	 */
	public function getEmail() {
		return $this->container['email'];
	}

	/**
	 * Gets a phone number
	 * @return string
	 */
	public function getPhone() {
		return $this->container['phone'];
	}

	/**
	 * Gets gender.
	 * @return string
	 */
	public function getGender() {
		return $this->container['gender'];
	}

	/**
	 * Gets Date Of Birth.
	 * @return string
	 */
	public function getDateOfBirth() {
		return $this->container['date_of_birth'];
	}

	/**
	 * Gets Last Name.
	 * @return string
	 */
	public function getLastName() {
		return $this->container['last_name'];
	}

	/**
	 * Gets First Name.
	 * @return string
	 */
	public function getFirstName() {
		return $this->container['first_name'];
	}

	/**
	 * Gets city.
	 * @return string
	 */
	public function getCity() {
		return $this->container['city'];
	}

	/**
	 * Gets state.
	 * @return string
	 */
	public function getState() {
		return $this->container['state'];
	}

	/**
	 * Gets zip code
	 * @return string
	 */
	public function getZipCode() {
		return $this->container['zip_code'];
	}

	/**
	 * Gets country code.
	 * @return string
	 */
	public function getCountryCode() {
		return $this->container['country_code'];
	}

	/**
	 * Gets the date of birth day.
	 * @return string
	 */
	public function getDobd() {
		return $this->container['dobd'];
	}

	/**
	 * Gets the date of birth month.
	 * @return string
	 */
	public function getDobm() {
		return $this->container['dobm'];
	}

	/**
	 * Gets the date of birth year.
	 * @return string
	 */
	public function getDoby() {
		return $this->container['doby'];
	}

	/**
	 * Extracts the IP Address from the PHP Request Context.
	 * @return string
	 */
	public function getIpAddress() {
		return $this->container['client_ip_address'];
	}

	/**
	 * Extracts the HTTP User Agent from the PHP Request Context.
	 * @return string
	 */
	public function getHttpUserAgent() {
		return $this->container['client_user_agent'];
	}

	/**
	 * Extracts the FBC cookie from the PHP Request Context.
	 * @return string
	 */
	public function getFbc() {
		return $this->container['fbc'];
	}

	/**
	 * Extracts the FBP cookie from the PHP Request Context.
	 * @return string
	 */
	public function getFbp() {

		return $this->container['fbp'];
	}

	public function get_api_url() {
		return $this->api_url . '/' . $this->version . '/' . $this->pixel_id . '/events';
	}

	public function get_request_body() {
		return $this->body;
	}

	public function set_user_data( $data = [] ) {
		$this->container['email']             = isset( $data['email'] ) ? $data['email'] : null;
		$this->container['phone']             = isset( $data['phone'] ) ? $data['phone'] : null;
		$this->container['gender']            = isset( $data['gender'] ) ? $data['gender'] : null;
		$this->container['date_of_birth']     = isset( $data['date_of_birth'] ) ? $data['date_of_birth'] : null;
		$this->container['last_name']         = isset( $data['last_name'] ) ? $data['last_name'] : null;
		$this->container['first_name']        = isset( $data['first_name'] ) ? $data['first_name'] : null;
		$this->container['city']              = isset( $data['city'] ) ? $data['city'] : null;
		$this->container['state']             = isset( $data['state'] ) ? $data['state'] : null;
		$this->container['dobd']              = isset( $data['dobd'] ) ? $data['dobd'] : null;
		$this->container['dobm']              = isset( $data['dobm'] ) ? $data['dobm'] : null;
		$this->container['doby']              = isset( $data['doby'] ) ? $data['doby'] : null;
		$this->container['country_code']      = isset( $data['country_code'] ) ? $data['country_code'] : null;
		$this->container['zip_code']          = isset( $data['zip_code'] ) ? $data['zip_code'] : null;
		$this->container['client_user_agent'] = isset( $data['client_user_agent'] ) ? $data['client_user_agent'] : null;
		$this->container['client_ip_address'] = isset( $data['client_ip_address'] ) ? $data['client_ip_address'] : null;
		$this->container['fbp']               = isset( $data['fbp'] ) ? $data['fbp'] : null;
		$this->container['fbc']               = isset( $data['fbc'] ) ? $data['fbc'] : null;
		$this->container['fbp']               = isset( $data['_fbp'] ) ? $data['_fbp'] : $this->container['fbp'];
		$this->container['fbc']               = isset( $data['_fbc'] ) ? $data['_fbc'] : $this->container['fbc'];
	}

	public function get_response_body() {
		return $this->response_body;
	}

	public function set_event_id( $event_id ) {
		return $this->container['event_id'] = $event_id;
	}

	public function set_test_event_code( $event_code ) {
		$this->test_event_code = $event_code;
	}

	public function set_partner_agent( $partner_agent ) {
		$this->partner_agent = $partner_agent;
	}

	/**
	 * Extracts the URI from the PHP Request Context.
	 * @return string
	 */
	public function getRequestUri() {
		return $this->container['reuqesturi'];
	}

}