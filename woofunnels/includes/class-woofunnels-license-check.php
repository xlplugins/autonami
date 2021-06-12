<?php

if ( ! defined( 'WF_API_ENDPOINT' ) ) {
	define( 'WF_API_ENDPOINT', 'https://account.buildwoofunnels.com/' );
}

class WooFunnels_License_check {
	private $server_point = WF_API_ENDPOINT;
	private $software_end_point = '';
	private $update_end_point = '';
	private $http = null;
	private $license_data = array();
	private $request_body = false;
	private $request_args = array(
		'timeout'   => 30,
		'sslverify' => false,
	);
	private $plugin_hash_key = '';
	private $human_name = '';
	private $version = '0.1.0';

	private $name = '';
	private $cache_key = '';
	private $invalidate_cache = '';
	private $default_keys = array(
		'plugin_slug'      => '',
		'email'            => '',
		'license_key'      => '',
		'product_id'       => '',
		'api_key'          => '',
		'version'          => '',
		'activation_email' => '',
	);

	public function __construct( $hash_key = '', $data = array() ) {
		$this->software_end_point = add_query_arg( array(
			'wc-api' => 'am-software-api',
		), $this->server_point );
		$this->update_end_point   = add_query_arg( array(
			'wc-api' => 'upgrade-api',
		), $this->server_point );
		if ( '' !== $hash_key ) {
			$this->set_hash( $hash_key );
		}
		if ( is_array( $data ) && count( $data ) > 0 ) {
			$this->setup_data( $data );
		}

		$this->invalidate_cache = filter_input( INPUT_GET, 'bwf_clear_plugin_cache', FILTER_SANITIZE_STRING );
		WooFunnels_License_Controller::register_plugin( $hash_key, $this );
	}

	public function set_hash( $hash ) {
		if ( '' !== $hash ) {
			$this->plugin_hash_key = $hash;
		}
	}

	public function setup_data( $data ) {
		$default = array(
			'plugin_slug'      => '',
			'plugin_name'      => '',
			'email'            => '',
			'license_key'      => '',
			'product_id'       => '',
			'api_key'          => '',
			'version'          => '',
			'activation_email' => '',
			'platform'         => $this->get_domain(),
			'domain'           => $this->get_domain(),
		);
		if ( isset( $data['email'] ) ) {
			$data['activation_email'] = $data['email'];
		}
		if ( isset( $data['license_key'] ) ) {
			$data['api_key'] = $data['license_key'];
		}

		$data['instance']   = $this->pass_instance();
		$this->license_data = wp_parse_args( $data, $default );
	}

	public function get_domain() {
		global $sitepress;
		$domain = site_url();

		if ( isset( $sitepress ) ) {
			$default_language = $sitepress->get_default_language();
			$domain           = $sitepress->convert_url( $sitepress->get_wp_api()->get_home_url(), $default_language );
		}

		return $domain;
	}

	public function pass_instance() {
		$plugin    = $this->get_hash();
		$instances = self::get_plugins();
		if ( is_array( $instances ) ) {

			if ( isset( $instances[ $plugin ] ) && '' !== $instances[ $plugin ]['instance'] ) {
				return $instances[ $plugin ]['instance'];
			} else {
				return md5( wp_generate_password( 12 ) );
			}
		}

		return false;
	}

	public function get_hash() {
		return $this->plugin_hash_key;
	}

	public static function get_plugins() {
		return WooFunnels_License_Controller::get_plugins();
	}

	/**
	 * Find activated plugin license data using Encode basename of plugin
	 *
	 * @param $basename
	 *
	 * @return [] |null
	 */
	public static function find_licence_data_using_basename( $basename ) {
		if ( is_multisite() ) {

			$active_plugins = get_site_option( 'active_sitewide_plugins', array() );
			if ( is_array( $active_plugins ) && ( in_array( $basename, apply_filters( 'active_plugins', $active_plugins ), true ) || array_key_exists( $basename, apply_filters( 'active_plugins', $active_plugins ) ) ) ) {
				$activated_licences = get_blog_option( get_network()->site_id, 'woofunnels_plugins_info', [] );
			} else {
				$activated_licences = get_option( 'woofunnels_plugins_info', [] );
			}

		} else {
			$activated_licences = get_option( 'woofunnels_plugins_info', [] );
		}
		if ( empty( $activated_licences ) ) {
			return null;
		}

		if ( isset( $activated_licences[ $basename ] ) ) {
			return $activated_licences[ $basename ];
		}

		return null;
	}

	public function start_updater() {
		$data = $this->get_data();

		$this->version     = $data['version'];
		$this->name        = $data['plugin_slug'];
		$this->human_name  = $data['plugin_name'];
		$this->slug        = str_replace( '.php', '', basename( $data['plugin_slug'] ) );
		$this->wp_override = false;
		$this->cache_key   = md5( maybe_serialize( $this->slug ) );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );

		add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 999, 3 );
	}

	public function get_data() {
		return count( $this->license_data ) > 0 ? $this->license_data : $this->default_keys;
	}

	/**
	 * preform here license check for all installed plugin
	 */
	public function woofunnels_license_check() {

		$plugins    = $this->get_plugins();
		$all_status = array();
		if ( is_array( $plugins ) && count( $plugins ) > 0 ) {
			foreach ( $plugins as $slug => $plugin ) {
				$api_data = array(
					'plugin_slug' => $slug,
					'email'       => $plugin['data_extra']['license_email'],
					'license_key' => $plugin['data_extra']['api_key'],
					'product_id'  => $plugin['data_extra']['software_title'],
					'version'     => '0.1.0',
				);

				$this->setup_data( $api_data );
			}
		}

		return $all_status;
	}

	public function license_status() {
		//do nothing here as the login moved to License Controller class to batch this request
	}

	public function activate_license() {

		$parse_data            = $this->get_data();
		$parse_data['request'] = 'activation';
		$end_point_url         = add_query_arg( $parse_data, $this->software_end_point );
		$this->request_body    = $this->http()->get( $end_point_url, $this->request_args );
		$output                = $this->build_output();
		if ( false !== $output ) {
			$this->save_license( $output );
		}

		return $output;
	}

	public function http() {
		if ( is_null( $this->http ) ) {
			$this->http = new WP_Http();
		}

		return $this->http;
	}

	private function build_output( $is_serialize = false ) {
		$output = $this->request_body;
		if ( ! is_wp_error( $output ) ) {
			$body = $output['body'];
			if ( '' !== $body ) {
				if ( false === $is_serialize ) {
					$body = json_decode( $body, true );

					if ( $body ) {
						return $body;
					}
				} else {
					$object = maybe_unserialize( $body );
					if ( is_object( $object ) && count( get_object_vars( $object ) ) > 0 ) {
						return $object;
					}

					return false;
				}
			}
		}

		return false;
	}

	/**
	 * Save plugin activation data in database
	 *
	 * @param $license_data
	 */
	private function save_license( $license_data ) {
		if ( ! empty( $license_data ) && isset( $license_data['activated'] ) && 1 == $license_data['activated'] ) {
			$slug = $this->get_hash();
			if ( '' !== $slug ) {
				$plugin_info          = self::get_plugins();
				$plugin_info[ $slug ] = $license_data;
				$this->update_plugins( $plugin_info );
			}
		}
	}

	public static function update_plugins( $data ) {
		WooFunnels_License_Controller::update_plugins( $data );
	}

	public function deactivate_license() {
		$parse_data            = $this->get_data();
		$parse_data['request'] = 'deactivation';
		$end_point_url         = add_query_arg( $parse_data, $this->software_end_point );
		$this->request_body    = $this->http()->get( $end_point_url, $this->request_args );
		$ouput                 = $this->build_output();

		if ( false !== $ouput ) {
			$this->remove_license();
		}

		return $this->build_output();
	}

	/**
	 *remove plugin license from database
	 */
	private function remove_license() {
		$slug = $this->get_hash();
		if ( '' !== $slug ) {
			$plugin_info = self::get_plugins();
			if ( isset( $plugin_info[ $slug ] ) ) {
				unset( $plugin_info[ $slug ] );
				$this->update_plugins( $plugin_info );
			}
		}
	}

	public function handle_license_check_response( $output ) {
		if ( false !== $output ) {
			/**
			 *  $output['status_check']
			 *  1 == active and valid
			 *  2 == expired
			 *  3 == trial ended
			 *  0 == invalid
			 */

			if ( $output['status_check'] && 3 === absint( $output['status_check'] ) ) {
				/**
				 * Trial license came with the info that trial ends and user did not renew the license
				 * Here we need to mark the data so that plugin gets abandoned.
				 */

				$this->remove_license();
			} elseif ( 2 === absint( $output['status_check'] ) ) {
				$this->mark_license_expired();
			} elseif ( 0 === absint( $output['status_check'] ) ) {
				$this->mark_license_invalid();
			} else {
				$this->mark_license_active();
			}

			if ( isset( $output['data_extra'] ) && is_array( $output['data_extra'] ) ) {
				$this->update_data( $output["data_extra"] );
			}
			if ( 'active' === $output['status_check'] ) {
				$activation_domain = $output['status_extra']['activation_domain'];
				$activation_domain = str_replace( [ 'https://', 'http://' ], '', $activation_domain );
				$activation_domain = trim( $activation_domain, '/' );

				if ( strpos( $this->get_domain(), $activation_domain ) === false ) {
					$this->remove_license();
				}
			}
		}
	}

	private function mark_license_expired() {
		$slug = $this->get_hash();
		if ( '' !== $slug ) {
			$plugin_info = self::get_plugins();
			if ( isset( $plugin_info[ $slug ] ) ) {
				$plugin_info[ $slug ]["expired"] = 1;
				$this->update_plugins( $plugin_info );
			}
		}
	}

	private function mark_license_invalid() {
		$slug = $this->get_hash();
		if ( '' !== $slug ) {
			$plugin_info = self::get_plugins();
			if ( isset( $plugin_info[ $slug ] ) ) {

				$plugin_info[ $slug ]["activated"] = 0;
				$plugin_info[ $slug ]["expired"]   = 0;
				$this->update_plugins( $plugin_info );
			}
		}
	}

	private function mark_license_active() {
		$slug = $this->get_hash();
		if ( '' !== $slug ) {
			$plugin_info = self::get_plugins();
			if ( isset( $plugin_info[ $slug ] ) ) {
				$plugin_info[ $slug ]["activated"] = 1;
				$plugin_info[ $slug ]["expired"]   = 0;
				$this->update_plugins( $plugin_info );
			}
		}
	}

	public function update_data( $output_data_extra ) {
		$slug = $this->get_hash();
		if ( '' !== $slug ) {
			$plugin_info = self::get_plugins();
			if ( isset( $plugin_info[ $slug ] ) ) {
				$plugin_info[ $slug ]["data_extra"] = $output_data_extra;
				$this->update_plugins( $plugin_info );
			}
		}
	}

	public function check_plugin_info() {
		$parse_data = $this->get_data();

		$parse_data['request'] = 'pluginupdatecheck';
		$end_point_url         = add_query_arg( $parse_data, $this->update_end_point );
		$this->request_body    = $this->http()->get( $end_point_url, $this->request_args );

		return $this->build_output( true );
	}

	public function check_update( $_transient_data ) {
		global $pagenow;

		if ( ! is_object( $_transient_data ) ) {
			$_transient_data = new stdClass;
		}
		if ( 'plugins.php' === $pagenow && is_multisite() ) {
			return $_transient_data;
		}
		if ( ! empty( $_transient_data->response ) && ! empty( $_transient_data->response[ $this->name ] ) && false === $this->wp_override ) {
			return $_transient_data;
		}

		$version_info = $this->get_cached_version_info();
		if ( 'yes' === $this->invalidate_cache || false === $version_info || is_null( $version_info ) ) {

			$output = $this->check_update_info();
			if ( false !== $output ) {
				$output->slug = str_replace( '.php', '', basename( $this->slug ) );
				$version_info = $output;
				$this->set_version_info_cache( $version_info );
			}
		}

		if ( ! is_object( $version_info ) ) {
			return $_transient_data;
		}
		if ( false !== $version_info && is_object( $version_info ) && isset( $version_info->version ) ) {
			if ( version_compare( $this->version, $version_info->version, '<' ) ) {
				$_transient_data->response[ $this->name ] = $version_info;
			}
			$_transient_data->last_checked           = current_time( 'timestamp' );
			$_transient_data->checked[ $this->name ] = $this->version;
		}

		return $_transient_data;
	}

	public function get_cached_version_info( $cache_key = '' ) {
		if ( empty( $cache_key ) ) {
			$cache_key = "_bwf_version_cache_" . $this->cache_key;
		}
		$cache = get_option( $cache_key );

		if ( empty( $cache['timeout'] ) || current_time( 'timestamp' ) > $cache['timeout'] ) {
			return false; // Cache is expired
		}

		return json_decode( $cache['value'] );
	}

	public function check_update_info() {
		$output = WooFunnels_License_Controller::get_plugin_update_check( $this->get_hash() );

		if ( false !== $output ) {
			if ( isset( $output->errors ) ) {
				return false;
			}

			if ( version_compare( $this->version, $output['new_version'], '<' ) ) {
				;
				$parse_data            = $this->get_data();
				$parse_data['request'] = 'plugininformation';
				$end_point_url         = add_query_arg( $parse_data, $this->update_end_point );

				$this->request_body = $this->http()->get( $end_point_url, $this->request_args );
				$out                = $this->build_output( true );
				if ( false !== $out ) {
					$out->new_version    = $output['new_version'];
					$out->package        = $output['package'];
					$out->download_link  = $output['package'];
					$out->access_expires = $output['access_expires'];

					return $out;
				}
			}
		}

		return false;
	}

	public function set_version_info_cache( $value = '', $cache_key = '' ) {
		if ( empty( $cache_key ) ) {
			$cache_key = "_bwf_version_cache_" . $this->cache_key;
		}

		$data = array(
			'timeout' => strtotime( '+3 hours', current_time( 'timestamp' ) ),
			'value'   => wp_json_encode( $value ),
		);

		update_option( $cache_key, $data, 'no' );
	}

	/**
	 * Updates information on the "View version x.x details" page with custom data.
	 *
	 *
	 * @param mixed $_data
	 * @param string $_action
	 * @param object $_args
	 *
	 * @return object $_data
	 */
	public function plugins_api_filter( $_data, $_action = '', $_args = null ) {
		if ( $_action !== 'plugin_information' ) {
			return $_data;
		}
		if ( ! isset( $_args->slug ) || ( $_args->slug !== $this->slug ) ) {
			return $_data;
		}

		$version_info = $this->get_cached_version_info();

		if ( false === $version_info || is_null( $version_info ) ) {
			$output = $this->check_update_info();
			if ( false !== $output ) {
				$output->slug = str_replace( '.php', '', basename( $this->slug ) );
				$_data        = $output;
				$this->set_version_info_cache( $_data );
			}
		} else {
			$_data = $version_info;
		}

		// Convert sections into an associative array, since we're getting an object, but Core expects an array.
		if ( isset( $_data->sections ) && ! is_array( $_data->sections ) ) {
			$new_sections = array();
			foreach ( $_data->sections as $key => $value ) {
				$new_sections[ $key ] = $value;
			}

			$_data->sections = $new_sections;
		}

		// Convert banners into an associative array, since we're getting an object, but Core expects an array.
		if ( isset( $_data->banners ) && ! is_array( $_data->banners ) ) {
			$new_banners = array();
			foreach ( $_data->banners as $key => $value ) {
				$new_banners[ $key ] = $value;
			}

			$_data->banners = $new_banners;
		}

		return $_data;
	}

	/**
	 * Disable SSL verification in order to prevent download update failures
	 *
	 * @param array $args
	 * @param string $url
	 *
	 * @return $array
	 */
	public function http_request_args( $args, $url ) {
		$verify_ssl = $this->verify_ssl();
		if ( strpos( $url, 'https://' ) !== false && strpos( $url, 'edd_action=package_download' ) ) {
			$args['sslverify'] = $verify_ssl;
		}

		return $args;
	}

	/**
	 * Returns if the SSL of the store should be verified.
	 *
	 * @return bool
	 * @since  1.6.13
	 */
	private function verify_ssl() {
		return (bool) apply_filters( 'edd_sl_api_request_verify_ssl', true, $this );
	}

}
