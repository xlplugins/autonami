<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class BWFAN_Connectors
 * Autonami Connectors Controller
 *
 * @package Autonami
 * @author XlPlugins
 */
class BWFAN_Connectors {
	private static $ins = null;

	/** @var WFCO_Connector_Screen[] $_connectors */
	private $_connectors = array();

	/**
	 * Check which connectors will provide extra data
	 */
	private static $connector_meta_data_required = array(
		'bwfco_slack'       => true,
		'bwfco_bitly'       => true,
		'bwfco_mailchimp'   => true,
		'bwfco_getresponse' => true,
		'bwfco_mautic'      => true,
	);

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function get_connectors() {
		if ( empty( $this->_connectors ) ) {
			$connector_screens = WFCO_Admin::get_available_connectors();
			$this->_connectors = $connector_screens['autonami'];
		}

		return $this->_connectors;
	}

	public function get_connectors_for_listing() {
		/** Fill connectors if not already fetched */
		$this->get_connectors();

		$connectors = array();
		foreach ( $this->_connectors as $connector_screen ) {

			$slug          = $connector_screen->get_slug();
			$connector     = WFCO_Load_Connectors::get_connector( $slug );
			$fields_schema = isset( $connector->v2 ) && true === $connector->v2 ? $connector->get_fields_schema() : array();
			$fields_values = isset( $connector->v2 ) && true === $connector->v2 ? $connector->get_settings_fields_values() : array();

			$is_connected = $connector_screen->is_activated() && isset( WFCO_Common::$connectors_saved_data[ $slug ] ) && true === $connector->has_settings();
			/** For Bitly */
			$is_connected = false === $is_connected && method_exists( $connector, 'is_connected' ) ? $connector->is_connected() : $is_connected;

			/** Connector DB ID */
			$connector_id = isset( $connector->v2 ) && true === $connector->v2 && $is_connected ? $this->get_connector_id( $slug ) : 0;

			/** Connector Meta */
			$meta = method_exists( $connector, 'get_meta_data' ) ? $connector->get_meta_data() : array();

			$final_connector = array(
				'name'          => $connector_screen->get_name(),
				'logo'          => $connector_screen->get_logo(),
				'description'   => $connector_screen->get_desc(),
				'is_syncable'   => $connector_screen->is_activated() && $connector->is_syncable(),
				'is_connected'  => $is_connected,
				'fields_schema' => $fields_schema,
				'fields_values' => $fields_values,
				'connector_id'  => $connector_id,
				'meta'          => $meta,
			);

			/** For Wizard Connectors */
			$initial_schema = method_exists( $connector, 'get_initial_schema' ) ? $connector->get_initial_schema() : false;
			if ( false !== $initial_schema ) {
				$final_connector['initial_schema'] = $initial_schema;
			}

			$connectors[ $connector_screen->get_slug() ] = $final_connector;
		}

		return $connectors;
	}

	public function get_connector_id( $slug ) {
		$saved_data = WFCO_Common::$connectors_saved_data;
		$old_data   = ( isset( $saved_data[ $slug ] ) && is_array( $saved_data[ $slug ] ) && count( $saved_data[ $slug ] ) > 0 ) ? $saved_data[ $slug ] : array();
		if ( isset( $old_data['id'] ) ) {
			return absint( $old_data['id'] );
		}

		return 0;
	}

	public function is_connected( $slug ) {
		$connector    = WFCO_Load_Connectors::get_connector( $slug );
		$is_connected = isset( WFCO_Common::$connectors_saved_data[ $slug ] ) && ! is_null( $connector ) && true === $connector->has_settings();
		/** For Bitly */
		$is_connected = false === $is_connected && method_exists( $connector, 'is_connected' ) ? $connector->is_connected() : $is_connected;

		return $is_connected;
	}

	public function is_wizard_connector( $connector ) {
		$meta = method_exists( $connector, 'get_meta_data' ) ? $connector->get_meta_data() : array();
		return isset( $meta['connect_type'] ) && 'wizard' === $meta['connect_type'];
	}
}

BWFAN_Core::register( 'connectors', 'BWFAN_Connectors' );
