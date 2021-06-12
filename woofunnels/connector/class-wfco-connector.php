<?php

abstract class BWF_CO {

	public static $GET = 1;
	public static $POST = 2;
	public static $DELETE = 3;
	public static $PUT = 4;
	public static $PATCH = 5;

	/** @var string Connector folder directory */
	public $dir = __DIR__;

	/** @var string Autonami integration class name */
	public $autonami_int_slug = '';

	/** @var null Nice name */
	public $nice_name = null;

	/** @var bool Connector has settings */
	public $is_setting = true;

	/** @var string Public directory URL */
	protected $connector_url = '';

	/** @var array Connector keys which are tracked during syncing and update */
	protected $keys_to_track = [];
	protected $form_req_keys = [];
	protected $sync = false;
	protected $is_oauth = false;

	/**
	 * Loads all calls of current connector
	 */
	public function load_calls() {
		$resource_dir = $this->dir . '/calls';
		if ( @file_exists( $resource_dir ) ) {
			foreach ( glob( $resource_dir . '/class-*.php' ) as $filename ) {
				$call_class = require_once( $filename );
				if ( ( is_object( $call_class ) || is_string( $call_class ) ) && method_exists( $call_class, 'get_instance' ) ) {
					$call_obj = $call_class::get_instance();
					$call_obj->set_connector_slug( $this->get_slug() );
					WFCO_Load_Connectors::register_calls( $call_obj );
				}
			}
		}

		do_action( 'bwfan_' . $this->get_slug() . '_actions_loaded' );
	}

	public function get_slug() {
		return sanitize_title( get_class( $this ) );
	}

	/**
	 * Handles the settings form submission
	 *
	 * @param $data
	 * @param string $type
	 *
	 * @return $array
	 */
	public function handle_settings_form( $data, $type = 'save' ) {
		$old_data = [];
		$new_data = [];

		$status = 'failed';
		$resp   = array(
			'status'  => $status,
			'id'      => 0,
			'message' => '',
		);

		/** Validating form settings */
		if ( 'sync' !== $type ) {
			$is_valid = $this->validate_settings_fields( $data, $type );
			if ( false === $is_valid ) {
				$resp['message'] = $this->get_connector_messages( 'connector_settings_missing' );

				return $resp;
			}
		}

		switch ( $type ) {
			case 'save':
				$new_data = $this->get_api_data( $data );
				if ( is_array( $new_data['api_data'] ) && count( $new_data['api_data'] ) > 0 && isset( $new_data['status'] ) && 'failed' !== $new_data['status'] ) {
					$id = WFCO_Common::save_connector_data( $new_data['api_data'], $this->get_slug(), 1 );

					$resp['id']      = $id;
					$resp['message'] = $this->get_connector_messages( 'connector_saved' );
				}
				break;
			case 'update':
				$saved_data = WFCO_Common::$connectors_saved_data;
				$old_data   = $saved_data[ $this->get_slug() ];
				$new_data   = $this->get_api_data( $data );

				if ( isset( $new_data['status'] ) && 'success' === $new_data['status'] ) {
					$resp['message'] = $this->get_connector_messages( 'connector_updated' );
				}
				break;
			case 'sync':
				$saved_data = WFCO_Common::$connectors_saved_data;
				$old_data   = $saved_data[ $this->get_slug() ];
				$new_data   = $this->get_api_data( $old_data );

				if ( isset( $new_data['status'] ) && 'success' === $new_data['status'] ) {
					$resp['message'] = $this->get_connector_messages( 'connector_synced' );
				}
				break;
		}

		$resp['status'] = $this->get_response_status( $new_data, 'status' );
		if ( '' === $resp['message'] && isset( $new_data['message'] ) && '' !== $new_data['message'] ) {
			$resp['message'] = $new_data['message'];
		}

		$resp['data_changed'] = 0;

		/** Return for save type case */
		if ( 'save' === $type ) {
			return $resp;
		}

		/** Assigning ID */
		$resp['id'] = $data['id'];

		/** Saving new data */
		WFCO_Common::update_connector_data( $new_data['api_data'], $resp['id'] );

		/** Tracking if data changed */
		$is_data_changed = $this->track_sync_changes( $new_data['api_data'], $old_data );
		if ( true === $is_data_changed ) {
			do_action( 'change_in_connector_data', $this->get_slug() );
			$resp['data_changed'] = 1;
		}

		return $resp;
	}

	/**
	 * Validating connector form settings fields, all required fields should be present with values
	 *
	 * @param $data
	 * @param string $type
	 *
	 * @return boolean
	 *
	 * @todo empty values need to check
	 */
	protected function validate_settings_fields( $data, $type = 'save' ) {
		$available_keys = array_keys( $data );
		if ( 'save' !== $type ) {
			$available_keys[] = 'id';
		}

		$diff = array_diff( $this->form_req_keys, $available_keys );
		if ( count( $diff ) > 0 ) {
			return false;
		}

		return true;
	}

	public function get_connector_messages( $key = 'connector_saved' ) {
		$messages = array(
			'connector_saved'            => __( 'Connector saved successfully', 'woofunnels' ),
			'connector_synced'           => __( 'Connector synced successfully', 'woofunnels' ),
			'connector_updated'          => __( 'Connector updated successfully', 'woofunnels' ),
			'connector_settings_missing' => __( 'Connector settings missing', 'woofunnels' ),
		);

		return ( isset( $messages[ $key ] ) ) ? $messages[ $key ] : '';
	}

	/**
	 * Get data from the API call, must required function otherwise call
	 *
	 * @param $data
	 *
	 * @return array
	 */
	protected function get_api_data( $data ) {
		return array(
			'status'  => 'failed',
			'message' => __( 'Connector forgot to override the method - get_api_data.', 'woofunnels-core' ),
		);
	}

	public function get_response_status( $data, $key = 'status' ) {
		$value = ( isset( $data[ $key ] ) ) ? $data[ $key ] : '';
		$value = ( 'status' === $key && empty( $value ) ) ? 'failed' : $value;

		return $value;
	}

	/**
	 * Track connector old and new data and return if any data change detected.
	 *
	 * @param $new_data
	 * @param $old_data
	 *
	 * @return bool
	 */
	protected function track_sync_changes( $new_data, $old_data ) {
		$has_changes = false;

		if ( empty( $this->keys_to_track ) || empty( $new_data ) || empty( $old_data ) ) {
			return $has_changes;
		}

		foreach ( $this->keys_to_track as $key ) {
			$str1 = isset( $new_data[ $key ] ) ? $new_data[ $key ] : '';
			$str2 = isset( $old_data[ $key ] ) ? $old_data[ $key ] : '';
			$str1 = is_array( $str1 ) ? wp_json_encode( $str1 ) : $str1;
			$str2 = is_array( $str2 ) ? wp_json_encode( $str2 ) : $str2;

			$diff = strcmp( $str1, $str2 );
			if ( 0 === $diff ) {
				continue;
			}
			$has_changes = true;
			break;
		}

		return $has_changes;
	}

	public function get_image() {
		return $this->connector_url . '/views/logo.png';
	}

	public function has_settings() {
		return $this->is_setting;
	}

	public function is_syncable() {
		return $this->sync;
	}

	public function is_oauth() {
		return $this->is_oauth;
	}

	public function setting_view() {
		?>
        <script type="text/html" id="tmpl-connector-<?php echo esc_html( $this->get_slug() ); ?>">
			<?php $this->get_settings_view(); ?>
        </script>
		<?php
	}

	public function get_settings_view() {
		$file_path = $this->dir . '/views/settings.php';
		if ( file_exists( $file_path ) ) {
			include $file_path;
		}
	}

}
