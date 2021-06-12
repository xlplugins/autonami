<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class BWFAN_Load_Integrations
 * @package Autonami
 * @author XlPlugins
 */
class BWFAN_Load_Integrations {
	/**
	 * Saves all the main integration's object
	 * @var array
	 */
	private static $integrations = array();
	/**
	 * Saves all the action's object
	 * @var array
	 */
	private static $available_actions = array();
	private static $integration_actions = array();

	private static $integration_localize_data = array();
	private static $integration_actions_localize_data = array();
	private static $action_localize_data = array();
	private static $ins = null;

	/**
	 * BWFAN_Load_Integrations constructor.
	 */
	protected function __construct() {
	}

	/**
	 * Return the object of current class
	 *
	 * @return null|BWFAN_Load_Integrations
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Register the integration when the integration file is included
	 *
	 * @param $class
	 */
	public static function register( $class ) {
		if ( class_exists( $class ) && method_exists( $class, 'get_instance' ) ) {
			$temp_integration = $class::get_instance();
			if ( $temp_integration instanceof BWFAN_Integration ) {
				$connector_slug = $temp_integration->get_connector_slug();

				/**
				 * If a connector
				 */
				if ( ! empty( $connector_slug ) ) {
					$all_connectors = WFCO_Load_Connectors::get_all_connectors();

					if ( empty( $all_connectors ) ) {
						return;
					}
					$saved_connectors = WFCO_Common::$connectors_saved_data;

					if ( empty( $saved_connectors ) ) {
						/** One time fetching from database is required */
						WFCO_Common::get_connectors_data();
						$saved_connectors = WFCO_Common::$connectors_saved_data;
					}

					if ( empty( $saved_connectors ) ) {
						/** If still no saved connectors, then return */
						return;
					}

					if ( isset( $all_connectors[ $connector_slug ] ) && ! isset( $saved_connectors[ $connector_slug ] ) ) {
						return;
					}
				}

				$slug                                     = $temp_integration->get_slug();
				self::$integrations[ $slug ]              = $temp_integration;
				self::$integration_localize_data[ $slug ] = $temp_integration->get_localize_data();
				$temp_integration->load_actions();
			}
		}
	}

	/**
	 * Register every action when action file is included
	 *
	 * @param $action_obj BWFAN_Action
	 */
	public static function register_actions( BWFAN_Action $action_obj ) {
		if ( method_exists( $action_obj, 'get_instance' ) ) {
			/**
			 * @var $temp_instance BWFAN_Action;
			 */
			$slug                                                                  = $action_obj->get_slug();
			$integration_type                                                      = $action_obj->get_integration_type();
			self::$available_actions[ $slug ]                                      = $action_obj;
			self::$integration_actions[ $integration_type ][ $slug ]               = $action_obj;
			self::$action_localize_data[ $slug ]                                   = $action_obj->get_localize_data();
			self::$integration_actions_localize_data[ $integration_type ][ $slug ] = $action_obj->get_localize_data();
		}
	}

	/**
	 * Return all available action registered which register by their integration
	 * @return array
	 */
	public function get_actions( $slug = '' ) {
		return self::$available_actions;
	}

	/**
	 * Return all the integrations with instance
	 *
	 * @return array
	 */
	public function get_integrations() {
		return self::$integrations;
	}

	/**
	 * @param string $type
	 *
	 * Return integration object
	 *
	 * @return BWFAN_Action
	 */
	public function get_action( $slug = 'wp' ) {
		return isset( self::$available_actions[ $slug ] ) ? self::$available_actions[ $slug ] : null;
	}

	public function get_integration_localize_data( $type = '' ) {
		if ( '' !== $type ) {
			return isset( self::$integration_localize_data[ $type ] ) ? self::$integration_localize_data[ $type ] : [];
		}

		return self::$integration_localize_data;
	}

	/**
	 * Array
	 * (
	 * [wc_add_order_note] => wc
	 * [wc_create_coupon] => wc
	 * [wc_remove_coupon] => wc
	 * [wp_custom_callback] => wp
	 * [wp_debug] => wp
	 * [wp_http_post] => wp
	 * [wp_sendemail] => wp
	 * )
	 * @return array
	 */
	public function get_mapped_arr_action_with_integration() {
		$actions  = self::$available_actions;
		$map_data = [];
		if ( count( $actions ) > 0 ) {
			foreach ( $actions as $action ) {
				$type              = $action->get_integration_type();
				$slug              = $action->get_slug();
				$map_data[ $slug ] = $type;

			}
		}

		return $map_data;
	}

	/**
	 *
	 *
	 * @return array
	 */
	public function get_mapped_arr_integration_name_with_action_name() {
		$integrations = self::get_all_integrations();
		$data         = [];
		if ( count( $integrations ) > 0 ) {
			/**
			 * @var $integration BWFAN_Integration
			 */
			foreach ( $integrations as $slug => $actions ) {
				$integration = $this->get_integration( $slug );
				if ( count( $actions ) === 0 ) {
					continue;
				}
				$nice_name          = $integration->get_name();
				$data[ $nice_name ] = [];
				/**
				 * @var $action BWFAN_Action
				 */
				foreach ( $actions as $action ) {
					$data[ $nice_name ][ $action->get_slug() ] = [
						'label'     => $action->get_name(),
						'available' => 'yes',
						'priority'  => $action->get_action_priority(),
					];
				}

				uasort( $data[ $nice_name ], function ( $item1, $item2 ) {
					return $item1['priority'] >= $item2['priority'];
				} );
			}
		}

		return $data;
	}

	/**
	 * Return all the actions with group and their integrations
	 *
	 * @return array
	 */
	public static function get_all_integrations() {
		return self::$integration_actions;
	}

	/**
	 * @param string $type
	 *
	 * Return integration object
	 *
	 * @return BWFAN_Integration|null
	 */
	public function get_integration( $type = 'wp' ) {
		return isset( self::$integrations[ $type ] ) ? self::$integrations[ $type ] : null;
	}

	public function get_integration_actions_localize_data() {
		return self::$integration_actions_localize_data;
	}

}

if ( class_exists( 'BWFAN_Load_Integrations' ) ) {
	BWFAN_Core::register( 'integration', 'BWFAN_Load_Integrations' );
}
