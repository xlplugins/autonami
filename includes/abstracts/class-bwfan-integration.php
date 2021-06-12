<?php

abstract class BWFAN_Integration {
	/**
	 * Return Save setting of integration
	 * @var array
	 */
	protected $integration_settings = [];
	/**
	 * this property use for admin builder ui
	 * @var array
	 */
	protected $localize_data = [];
	/**
	 * Return Nice Human readable name of integration
	 * @var null
	 */
	protected $nice_name = 'WordPress';

	/**
	 * Return integration belong native WordPress or other like activecampaign
	 * @var bool
	 */
	protected $native_integration = false;
	/**
	 * If you add new integration and have respective action then please override this property in child class
	 * @var string
	 */
	protected $action_dir = __DIR__;
	/**
	 * Return Unique Slug of Integration
	 * @var string
	 */
	protected $slug = '';
	/**
	 * Return used connector slug in integration
	 * @var string
	 */
	protected $connector_slug = '';

	/**
	 * Return integration need a separate connector
	 * @var bool
	 */
	protected $need_connector = false;

	protected $group_name = '';
	protected $group_slug = '';

	protected $priority = 10;

	/**
	 * Loads all actions of current integration
	 */
	public function load_actions() {
		$resource_dir = $this->action_dir . '/actions';

		if ( file_exists( $resource_dir ) ) {
			foreach ( glob( $resource_dir . '/class-*.php' ) as $_field_filename ) {
				$file_data = pathinfo( $_field_filename );
				if ( isset( $file_data['basename'] ) && 'index.php' === $file_data['basename'] ) {
					continue;
				}
				$action_class = require_once( $_field_filename );

				if ( is_string( $action_class ) && method_exists( $action_class, 'get_instance' ) ) {
					/**
					 * @var $action_obj BWFAN_Action
					 */
					$action_obj = $action_class::get_instance();
					$action_obj->load_hooks();
					$action_obj->set_integration_type( $this->get_slug() );
					BWFAN_Load_Integrations::register_actions( $action_obj );
					$this->do_after_action_registration( $action_obj );

				}
			}
		}
		do_action( 'bwfan_' . $this->get_slug() . '_actions_loaded', $this );
	}

	/**
	 * Get slug of an integration
	 * Note: Used in change_in_automations method of BWFAN_Automations class
	 *
	 * @return mixed|string
	 */
	public function get_slug() {
		$this->slug = str_replace( array( 'bwfan_', '_integration' ), '', sanitize_title( get_class( $this ) ) );

		return $this->slug;
	}

	protected function do_after_action_registration( BWFAN_Action $action_object ) {

	}

	public function get_localize_data() {
		$this->localize_data = [
			'nice_name'          => $this->get_name(),
			'slug'               => $this->get_slug(),
			'connector_slug'     => $this->get_connector_slug(),
			'native_integration' => $this->native_integration(),
			'group_slug'         => $this->get_group_slug(),
			'group_name'         => $this->get_group_name(),
			'priority'           => $this->get_priority()
		];

		return $this->localize_data;
	}

	public function get_name() {
		return trim( $this->nice_name );
	}

	public function get_connector_slug() {
		return $this->connector_slug;
	}

	public function get_group_slug() {
		return $this->group_slug;
	}

	public function get_group_name() {
		return $this->group_name;
	}

	public function get_priority() {
		return $this->priority;
	}

	public function native_integration() {
		return $this->native_integration;
	}

	public function set_settings( $data ) {
		if ( is_array( $data ) ) {
			$this->integration_settings = $data;
		}
	}

	public function get_settings( $key = '' ) {
		if ( ! empty( $key ) ) {
			return isset( $this->integration_settings[ $key ] ) ? $this->integration_settings[ $key ] : '';
		}

		return $this->integration_settings;
	}

	public function need_connector() {
		return $this->need_connector;
	}

	public function handle_response( $result, $connector_slug, $action_call_class_slug, $action_data = null ) {
		return $result;
	}

	/**
	 * to avoid unserialize of the current class
	 */
	public function __wakeup() {
		throw new ErrorException( 'BWFAN_Core can`t converted to string' );
	}

	/**
	 * to avoid serialize of the current class
	 */
	public function __sleep() {
		throw new ErrorException( 'BWFAN_Core can`t converted to string' );
	}

	/**
	 * To avoid cloning of current class
	 */
	protected function __clone() {
	}

}
