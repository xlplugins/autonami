<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WFCO_Admin {

	private static $ins = null;
	public $admin_path;
	public $admin_url;
	public $section_page = '';
	public $should_show_shortcodes = null;

	public function __construct() {
		define( 'WFCO_PLUGIN_FILE', __FILE__ );
		define( 'WFCO_PLUGIN_DIR', __DIR__ );
		define( 'WFCO_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_PLUGIN_FILE ) ) );
		$this->admin_path = WFCO_PLUGIN_DIR;
		$this->admin_url  = WFCO_PLUGIN_URL;

		add_action( 'admin_enqueue_scripts', array( $this, 'include_global_assets' ), 98 );

		$should_include = apply_filters( 'wfco_include_connector', false );
		if ( false === $should_include ) {
			return;
		}
		$this->initialize_connector();
	}

	private function initialize_connector() {
		include_once( $this->admin_path . '/class-wfco-connector.php' ); //phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		include_once( $this->admin_path . '/class-wfco-call.php' ); //phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		include_once( $this->admin_path . '/class-wfco-load-connectors.php' ); //phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		include_once( $this->admin_path . '/class-wfco-common.php' ); //phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		include_once( $this->admin_path . '/class-wfco-ajax-controller.php' ); //phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		include_once( $this->admin_path . '/class-wfco-db.php' ); //phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		include_once( $this->admin_path . '/class-wfco-connector-api.php' ); //phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

		WFCO_Common::init();

		/**
		 * Admin enqueue scripts
		 */
		add_action( 'admin_init', array( $this, 'register_assets' ), 99 );

		/**
		 * Admin footer text
		 */
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 9999, 1 );
		add_filter( 'update_footer', array( $this, 'update_footer' ), 9999, 1 );
		add_action( 'in_admin_header', array( $this, 'maybe_remove_all_notices_on_page' ) );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public static function get_plugins() {
		return apply_filters( 'all_plugins', get_plugins() );
	}

	public static function localize_data() {
		$data = array(
			'ajax_nonce'            => wp_create_nonce( 'wfcoaction-admin' ),
			'plugin_url'            => plugin_dir_url( WFCO_PLUGIN_FILE ),
			'ajax_url'              => admin_url( 'admin-ajax.php' ),
			'admin_url'             => admin_url(),
			'ajax_chosen'           => wp_create_nonce( 'json-search' ),
			'search_products_nonce' => wp_create_nonce( 'search-products' ),
			'connectors_pg'         => admin_url( 'admin.php?page=connector&tab=connectors' ),
			'oauth_nonce'           => wp_create_nonce( 'wfco-connector' ),
			'oauth_connectors'      => self::get_oauth_connector(),
			'errors'                => self::get_error_message(),
			'texts'                 => self::js_text(),
		);
		wp_localize_script( 'wfco-admin', 'wfcoParams', $data );
	}

	public static function get_oauth_connector() {
		$oauth_connectors = [];
		$all_connector    = WFCO_Admin::get_available_connectors();
		if ( empty( $all_connector ) ) {
			return $oauth_connectors;
		}

		foreach ( $all_connector as $addons ) {
			if ( empty( $addons ) ) {
				continue;
			}
			foreach ( $addons as $addons_slug => $addon ) {
				if ( $addon->is_activated() ) {
					$instance = $addons_slug::get_instance();
					if ( $instance->is_oauth() ) {
						$oauth_connectors[] = $addons_slug;
					}
				}
			}
		}

		return $oauth_connectors;
	}

	public static function get_available_connectors( $type = '' ) {
		$transient = new WooFunnels_Transient();
		$data      = $transient->get_transient( 'get_available_connectors' );

		if ( ! empty( $data ) && is_array( $data ) ) {
			$data = apply_filters( 'wfco_connectors_loaded', $data );

			return self::load_connector_screens( $data, $type );
		}

		$connector_api = new WFCO_Connector_api();
		$response_data = $connector_api->set_action( 'get_available_connectors' )->get()->get_package();

		if ( is_array( $response_data ) ) {
			$transient->set_transient( 'get_available_connectors', $response_data, 3 * HOUR_IN_SECONDS );
		}

		$response_data = apply_filters( 'wfco_connectors_loaded', $response_data );
		if ( '' !== $type ) {
			return isset( $response_data[ $type ] ) ? $response_data[ $type ] : [];
		}

		return self::load_connector_screens( $response_data, $type );
	}

	private static function load_connector_screens( $response_data, $type = '' ) {

		foreach ( $response_data as $slug => $data ) {
			$connectors = $data['connectors'];
			foreach ( $connectors as $c_slug => $connector ) {
				$connector['type']            = $slug;
				$connector['source']          = $data['source'];
				$connector['file']            = $data['file'];
				$connector['support']         = $data['support'];
				$connector['connector_class'] = $data['connector_class'];
				WFCO_Connector_Screen_Factory::create( $c_slug, $connector );
			}
		}

		return WFCO_Connector_Screen_Factory::getAll( $type );
	}

	public static function get_error_message() {
		$errors      = [];
		$errors[100] = __( 'Connector not found' );
		$errors[101] = __( 'Autonami license is required in order to install a connector' );
		$errors[102] = __( 'Autonami license is invalid, kindly contact woofunnels team.' );
		$errors[103] = __( 'Autonami license is expired, kindly renew and activate it first.' );

		return $errors;
	}

	public static function js_text() {
		$data = array(
			'text_copied'             => __( 'Text Copied', 'woofunnels' ),
			'sync_title'              => __( 'Sync Connector', 'woofunnels' ),
			'sync_text'               => __( 'All the data of this Connector will be Synced.', 'woofunnels' ),
			'sync_wait'               => __( 'Please Wait...', 'woofunnels' ),
			'sync_progress'           => __( 'Sync in progress...', 'woofunnels' ),
			'sync_success_title'      => __( 'Connector Synced', 'woofunnels' ),
			'sync_success_text'       => __( 'We have detected change in the connector during syncing. Please re-save the Automations.', 'woofunnels' ),
			'oops_title'              => __( 'Oops', 'woofunnels' ),
			'oops_text'               => __( 'There was some error. Please try again later.', 'woofunnels' ),
			'delete_int_title'        => __( 'There was some error. Please try again later.', 'woofunnels' ),
			'delete_int_text'         => __( 'There was some error. Please try again later.', 'woofunnels' ),
			'update_int_prompt_title' => __( 'Connector Updated', 'woofunnels' ),
			'update_int_prompt_text'  => __( 'We have detected change in the connector during updating. Please re-save the Automations.', 'woofunnels' ),
			'delete_int_prompt_title' => __( 'Disconnecting Connector?', 'woofunnels' ),
			'delete_int_prompt_text'  => __( 'All the action, tasks, logs of this connector will be deleted.', 'woofunnels' ),
			'delete_int_wait_title'   => __( 'Please Wait...', 'woofunnels' ),
			'delete_int_wait_text'    => __( 'Disconnecting the connector ...', 'woofunnels' ),
			'delete_int_success'      => __( 'Connector Disconnected', 'woofunnels' ),
			'update_btn'              => __( 'Update', 'woofunnels' ),
			'save_progress'           => __( 'Saving in progress...', 'woofunnels' ),
			'update_btn_process'      => __( 'Updating...', 'woofunnels' ),
			'connect_btn_process'     => __( 'Connecting...', 'woofunnels' ),
			'install_success_title'   => __( 'Connector Installed Successfully', 'woofunnels-autonami-automation' ),
			'connect_success_title'   => __( 'Connected Successfully', 'woofunnels-autonami-automation' ),
		);

		return $data;
	}

	public function get_admin_url() {
		return plugin_dir_url( WFCO_PLUGIN_FILE ) . 'admin';
	}

	public function include_global_assets() {
		wp_enqueue_script( 'wfco-admin-ajax', $this->admin_url . '/assets/js/wfco-admin-ajax.js', array(), WooFunnel_Loader::$version );
		wp_localize_script( 'wfco-admin-ajax', 'bwf_secure', [
			'nonce' => wp_create_nonce( 'bwf_secure_key' ),
		] );
	}

	public function register_assets() {
		/**
		 * Including izimodal assets
		 */
		wp_register_style( 'wfco-sweetalert2-style', $this->admin_url . '/assets/css/sweetalert2.css', array(), WooFunnel_Loader::$version );
		wp_register_style( 'wfco-izimodal', $this->admin_url . '/assets/css/iziModal/iziModal.css', array(), WooFunnel_Loader::$version );
		wp_register_style( 'wfco-toast-style', $this->admin_url . '/assets/css/toast.min.css', array(), WooFunnel_Loader::$version );
		wp_register_style( 'wfco-sweetalert2-script', $this->admin_url . '/assets/js/sweetalert2.js', array( 'jquery' ), WooFunnel_Loader::$version, true );
		wp_register_style( 'wfco-izimodal', $this->admin_url . '/assets/js/iziModal/iziModal.js', array(), WooFunnel_Loader::$version );
		wp_register_style( 'wfco-toast-script', $this->admin_url . '/assets/js/toast.min.js', array( 'jquery' ), WooFunnel_Loader::$version, true );
		/**
		 * Including Connector assets on all connector pages.
		 */
		wp_register_style( 'wfco-admin', $this->admin_url . '/assets/css/wfco-admin.css', array(), WooFunnel_Loader::$version );
		wp_register_script( 'wfco-admin', $this->admin_url . '/assets/js/wfco-admin.js', array(), WooFunnel_Loader::$version );
	}

	public function is_connector_page( $section = '' ) {
		if ( 'autonami' === filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING) && '' === $section ) {
			return true;
		}

		if (  'autonami' === filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING) && filter_input(INPUT_GET,'section',FILTER_SANITIZE_STRING) === $section ) {
			return true;
		}

		return false;
	}

	public function connector_page() {
		if ( 'autonami' === filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING) ) {
			include_once( $this->admin_path . '/view/connector-admin.php' ); //phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		}
	}

	public function admin_footer_text( $footer_text ) {
		if ( WFCO_Common::is_load_admin_assets( 'all' ) ) {
			return '';
		}

		return $footer_text;
	}

	public function update_footer( $footer_text ) {
		if ( WFCO_Common::is_load_admin_assets( 'all' ) ) {
			return '';
		}

		return $footer_text;
	}

	public function tooltip( $text ) {
		?>
        <span class="wfco-help"><i class="icon"></i><div class="helpText"><?php echo $text; ?></div></span>
		<?php
	}

	/**
	 * Remove all the notices in our dashboard pages as they might break the design.
	 */
	public function maybe_remove_all_notices_on_page() {
		if ( 'autonami' === filter_input(INPUT_GET,'page',FILTER_SANITIZE_STRING) && filter_input(INPUT_GET,'section',FILTER_SANITIZE_STRING) ) {
			remove_all_actions( 'admin_notices' );
		}
	}

}
