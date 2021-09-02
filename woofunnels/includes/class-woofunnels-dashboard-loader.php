<?php

/**
 * This class is a mail loader class for dashboard page , controls and sets up all the necessary actions
 *
 * @author woofunnels
 * @package WooFunnels
 */
define( 'BWF_VERSION', '1.9.72' );
define( 'BWF_DB_VERSION', '1.0.3' );

class WooFunnels_Dashboard {

	public static $currentPage;
	public static $parent;
	public static $selected = '';
	public static $pagefullurl = '';
	public static $is_dashboard_page = false;
	public static $loader_url = '';
	public static $is_core_menu = false;
	public static $classes = [];
	protected static $expectedurl;
	protected static $expectedslug;

	/**
	 * Function Loads the html and required javascript to render on dashboard page
	 */
	public static function load_page() {
		//do_action
		do_action( 'woofunnels_before_dashboard_page' );
		self::register_dashboard();
		$model = apply_filters( 'woofunnels_tabs_modal_' . self::$selected, array() );

		?>
		<div class="wrap">
			<div class="icon32" id="icon-themes"><br></div>
			<div class="woofunnels_dashboard_tab_content" id="<?php echo self::$selected; ?>">
				<?php include_once self::$loader_url . 'views/woofunnels-tabs-' . self::$selected . '.phtml'; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Register dashboard function just initializes the execution by firing some hooks that helps getting and rendering data
	 */
	public static function register_dashboard() {

		//registering necessary hooks
		//making sure these hooks loads only when register for dashboard happens (specific page)
		self::woofunnels_dashboard_scripts();
		add_action( 'woofunnels_tabs_modal_licenses', array( __CLASS__, 'woofunnels_licenses_data' ), 99 );
		add_action( 'woofunnels_tabs_modal_support', array( __CLASS__, 'woofunnels_support_data' ), 99 );
		add_action( 'woofunnels_tabs_modal_tools', array( __CLASS__, 'woofunnels_tools_data' ), 99 );
		add_action( 'woofunnels_tabs_modal_logs', array( __CLASS__, 'woofunnels_logs_data' ), 99 );

		add_action( 'woofunnels_tools_right_area', array( __CLASS__, 'show_right_area' ) );

		add_filter( 'woofunnels_additional_tabs', array( __CLASS__, 'add_logs_tabs' ), 10, 1 );


	}

	/**
	 * Hooked over 'admin_enqueue_scripts' under the register function, cannot run on every admin page
	 * Enqueues `updates` handle script,  core script that is responsible for plugin updates
	 */
	public static function woofunnels_dashboard_scripts() {

		?>
		<style type="text/css">

            /* product grid */
            .woofunnels_plugins_wrap .filter-links.filter-primary {
                border-right: 2px solid #e5e5e5;
            }

            .woofunnels_plugins_wrap .wp-filter {
                margin-bottom: 0;
            }

            .woofunnels_plugins_wrap .filter-links li {
                border-bottom: 4px solid white;
            }

            .woofunnels_plugins_wrap .filter-links li a.current {
                border-bottom: 4px solid #fff;
            }

            .woofunnels_plugins_wrap .filter-links li.current {
                border-bottom-color: #666666;
            }

            .woofunnels_plugins_wrap .woofunnels_dashboard_tab_content {
                float: left;
                width: 54%;
            }

            .woofunnels_plugins_wrap .woofunnels_dashboard_license_content {
                float: left;
                width: 74%;
            }

            .woofunnels_plugins_wrap .woofunnels_dashboard_tab_content .woofunnels_core_tools {
                width: 100% !important;
                background: #fff;
            }

            .woofunnels_plugins_wrap .woofunnels_dashboard_tab_content .woofunnels_core_tools h2 {
                margin-top: 0;
            }

            .woofunnels_plugins_wrap .woofunnels_plugins_status {
                font-style: italic;
            }

            .woofunnels_plugins_wrap .woofunnels_plugins_features_div {
            }

            .woofunnels_plugins_wrap div#col-container.about-wrap {
                max-width: 100%;
                margin: 30px 20px 0 0;
                width: auto;
                margin-right: 0;
                clear: both;

            }

            .woofunnels_plugins_wrap div#col-container.about-wrap .col-wrap {
                float: left;
            }

            .woofunnels_dashboard_tab_content .woofunnels_plugins_wrap .woofunnels-area-right {
                float: right;
                width: 44%;
                margin: 0;
            }

            .woofunnels_dashboard_tab_content#licenses .woofunnels_plugins_wrap .woofunnels-area-right {
                width: 24%;
            }

            .woofunnels_plugins_wrap .woofunnels-area-right table {
                padding: 15px;
            }

            .woofunnels_plugins_wrap .woofunnels-area-right table th, .woofunnels_plugins_wrap .woofunnels-area-right table td {
                vertical-align: middle;
                padding: 15px 0;
            }

            .woofunnels_plugins_wrap .woofunnels-area-right table td {
                text-align: right;
            }

            .woofunnels_plugins_wrap .woofunnels_plugins_features {
                margin-left: -10px;
                margin-right: -10px;
            }

            .woofunnels_plugins_wrap .woofunnels_plugins_features .woofunnels_plugins_half_col {
                width: 100%;
                margin: 4px 0;
                padding-left: 30px;
                padding-right: 10px;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
            }

            .woofunnels_plugins_wrap .woofunnels_plugins_features .woofunnels_plugins_half_col:before {
                margin-left: -20px;
                content: "\f147";
                font: 400 20px/.5 dashicons;
                speak: none;
                display: inline-block;
                padding: 0;
                top: 4px;
                left: -2px;
                position: relative;
                vertical-align: top;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                text-decoration: none !important;
                color: #444;
            }

            @media screen and (min-width: 481px) {
                .woofunnels_plugins_wrap .woofunnels_plugins_features .woofunnels_plugins_half_col {
                    width: 50%;
                    float: left;
                }

                .woofunnels_plugins_wrap .woofunnels_plugins_features .woofunnels_plugins_half_col:nth-child(2n) {
                    text-align: right;
                }

                .woofunnels_plugins_wrap .woofunnels_plugins_features .woofunnels_plugins_half_col:nth-child(2n+1) {
                    clear: both;
                }
            }

            .woofunnels_plugins_wrap .woofunnels_plugins_status_div {
                padding-top: 8px;
                padding-bottom: 8px;
                border-color: rgba(221, 221, 221, 0.4);
                background: #fff;
            }

            .woofunnels_plugins_wrap .woofunnels_plugins_status_div .woofunnels_plugins_status {
                margin: 0;
            }

            .woofunnels_plugins_wrap .button-primary.woofunnels_plugins_renew_btn {
                min-width: 120px;
                text-align: center;
            }

            .woofunnels_plugins_wrap .button-primary.woofunnels_plugins_renew_btn:before {
                content: "\f321";
                font: 400 20px/.5 dashicons;
                speak: none;
                display: inline-block;
                padding: 0;
                top: 4px;
                left: -2px;
                position: relative;
                vertical-align: top;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                text-decoration: none !important;
                color: #fff;
            }

            .woofunnels_plugins_wrap .button-primary.woofunnels_plugins_buy_btn {
                min-width: 120px;
                text-align: center;
            }

            .woofunnels_plugins_wrap .button-primary.woofunnels_plugins_buy_btn:before {
                content: "\f174";
                font: 400 20px/.5 dashicons;
                speak: none;
                display: inline-block;
                padding: 0;
                top: 4px;
                left: -2px;
                position: relative;
                vertical-align: top;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                text-decoration: none !important;
                color: #fff;
            }

            .woofunnels_plugins_wrap .plugin-card-bottom.woofunnels_plugins_features_links_div {
                background: #fff;
            }

            .woofunnels_plugins_wrap .plugin-card-bottom.woofunnels_plugins_features_links_div .woofunnels_plugins_features_links ul {
                margin: 0;
            }

            .woofunnels_plugins_wrap .woofunnels_plugins_deactivate_add.woofunnels_plugins_features_links {
                padding-right: 125px;
                display: block;
                position: relative;
            }

            .woofunnels_plugins_wrap .woofunnels_plugins_deactivate_add.woofunnels_plugins_features_links .woofunnels_plugins_deactivate {
                color: #a00000;
                display: inline-block;
                line-height: 26px;
            }

            .clearfix:after, .clearfix:before {
                display: table;
                content: '';
            }

            .clearfix:after {
                clear: both;
            }

            .woofunnels_plugins_wrap ul.woofunnels_plugins_options {
                display: inline-block;
                line-height: 26px;
                float: right;
                position: absolute;
                z-index: 1;
                right: 0;
            }

            .woofunnels_plugins_wrap ul.woofunnels_plugins_options li {
                display: inline-block;
                margin: 0;
            }

            .woofunnels_plugins_wrap .js_filters li a:focus {
                box-shadow: none;
                -webkit-box-shadow: none;
                color: #23282d;
            }

            #licenses .column-product_status, .index_page_woothemes-helper-network .column-product_status {
                width: 350px;
            }

            #licenses .below_input_message {
                color: #9E0B0F;
                padding-left: 1px;
            }

            #licenses .below_input_message a {
                text-decoration: underline;
            }

            .woofunnels-updater-plugin-upgrade-notice {
                font-weight: 400;
                color: #fff;
                background: #d54d21;
                padding: 1em;
                margin: 9px 0;
            }

            .woofunnels-updater-plugin-upgrade-notice:before {
                content: "\f348";
                display: inline-block;
                font: 400 18px/1 dashicons;
                speak: none;
                margin: 0 8px 0 -2px;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                vertical-align: top;
            }

            #support-request label:not(.radio) {
                display: block;
                font-weight: 600;
                font-size: 14px;
                line-height: 1.3;
            }

            #pdf-system-status {
                overflow: hidden;
            }

            #pdf-system-status p {
                clear: left;
            }

            #pdf-system-status span.details, #support-request span.details {
                font-size: 95%;
                color: #444;
                margin-top: 7px;
                display: inline-block;
                clear: left;
            }

            #pdf-system-status span.details.path, #support-request span.details.path {
                padding: 2px;
                background: #f2f2f2;
            }

            #support-request input:not([type="radio"]), #support-request select {
                width: 20em;
                max-width: 350px;
                width: 100%;
            }

            #support-request input[type="submit"] {
                width: auto;
            }

            #support-request textarea {
                width: 65%;
                height: 150px;
            }

            #support-request input, #support-request textarea {
                padding: 5px 4px;
            }

            #support-request #support-request-button {
                padding: 0 8px;
            }

            #support-request .gfspinner {
                vertical-align: middle;
                margin-left: 5px;
            }

            #support-request textarea {
                max-width: 350px;
                width: 100%;
                /*                border: 1px solid #999;
								color: #444;*/
            }

            #support-request :disabled, #support-request textarea:disabled {
                color: #CCC;
                border: 1px solid #CCC;
            }

            #support-request input.error, #support-request textarea.error, #support-request select.error {
                color: #d10b0b;
                border: 1px solid #d10b0b;
            }

            #support-request .form-table .radioBtns span {

                margin-right: 10px;
                display: inline-block;
            }

            #support-request .fa-times-circle {
                vertical-align: middle;
            }

            .icon-spinner {
                font-size: 18px;
                margin-left: 5px;
            }

            #support-request span.msg {
                margin-left: 5px;
                color: #008000;
            }

            #support-request span.error {
                margin-left: 5px;
                color: #d10b0b;
            }

            #lv_pointer_target {
                float: right;
                background: #0e3f7a;
                color: #fff;
                border: none;
                position: relative;
                top: -6px;
            }

            #lv_pointer_target:focus {
                border: none;
                -webkit-box-shadow: none;
                box-shadow: none;

            }

            .woofunnels_plugins_wrap .filter-links li > a:focus {
                -webkit-box-shadow: none;
                box-shadow: none;
                -moz-box-shadow: none;
            }

            @media (max-width: 1023px) {

                #support-request .form-table .radioBtns span {
                    display: block;
                    margin-bottom: 4px;
                }
            }

            @media screen and (max-width: 782px) {
                .woofunnels_plugins_wrap .woofunnels_dashboard_tab_content {
                    float: none;
                }

                .woofunnels_plugins_wrap div#col-container.about-wrap {
                    margin-right: 0;
                }

                .woofunnels_plugins_wrap div#col-container.about-wrap .woofunnels-area-right {
                    float: none;
                    margin-right: 0;
                    width: 280px;
                    margin-left: 0;
                    margin: auto;
                }

                .woofunnels_plugins_wrap div#col-container.about-wrap .col-wrap {
                    float: left;
                }

                #support-request .form-table input[type="radio"] {
                    height: 16px;
                    width: 16px;
                }
            }

            .woofunnels_core_tools .woofunnels_download_files_label {
                display: inline-block;
                padding-left: 10px;
                vertical-align: -webkit-baseline-middle;
            }

            .woofunnels_core_tools .woofunnels_download_buttons {
                display: inline-block;
                padding-left: 5px;

            }
		</style>
		<?php
	}

	/**
	 * Init function hooked on `admin_init`
	 * Set the required variables and register some important hooks
	 */
	public static function init() {


		self::$loader_url = WooFunnel_Loader::$ultimate_path;
		$selected         = null !== sanitize_text_field( filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) ) ? sanitize_text_field( filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) ) : 'plugins';

		self::$selected = $selected;

		/**
		 * Function to trigger error message at WordPress plugins page when we have update but license invalid
		 */
		self::add_notice_unlicensed_product();

		/**
		 * Initialize Localization
		 */
		add_action( 'init', array( __CLASS__, 'localization' ) );


		add_action( 'admin_head', [ __CLASS__, 'apply_scroll_fix_css' ] );
	}

	/**
	 * Getting and parsing all our licensing products and checking if there update is available
	 */
	public static function add_notice_unlicensed_product() {
		/**
		 * Getting necessary data
		 */
		$licenses = WooFunnels_licenses::get_instance()->get_data();

		/**
		 * Looping over to check how many licenses are invalid and pushing notification and error accordingly
		 */
		if ( $licenses && count( $licenses ) > 0 ) {
			foreach ( $licenses as $key => $license ) {
				if ( $license['product_status'] === 'invalid' ) {
					add_action( 'in_plugin_update_message-' . $key, array( __CLASS__, 'need_license_message' ), 10, 2 );
				}
			}
		}
	}

	public static function localization() {
		load_plugin_textdomain( 'woofunnels', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Message displayed if license not activated. <br/>
	 *
	 * @param array $plugin_data
	 * @param object $r
	 *
	 * @return void
	 */
	public static function need_license_message( $plugin_data, $r ) {
		if ( empty( $r->package ) ) {
			echo wp_kses_post( '<div class="woofunnels-updater-plugin-upgrade-notice">' . __( 'To enable this update please activate your WooFunnels license by visiting the Dashboard Page.', 'woofunnels' ) . '</div>' );
		}
	}

	/**
	 * Model function to fire over licensing page. <br/>
	 * Hooked over 'woofunnels_tabs_modal_licenses'. <br/>
	 * @return mixed false on failure and data on success
	 */
	public static function woofunnels_licenses_data() {
		if ( false === WooFunnels_API::get_woofunnels_status() ) {
			return;
		}
		$get_list = array();

		$License = WooFunnels_licenses::get_instance();

		return (object) array_merge( (array) $get_list, (array) array(
			'additional_tabs' => apply_filters( 'woofunnels_additional_tabs', array(
				array(
					'slug'  => 'tools',
					'label' => __( 'Tools', 'woofunnels' ),
				),
			) ),
			'licenses'        => $License->get_data(),
			'current_tab'     => self::$selected,
		) );
	}

	/**
	 * Model function to fire over licensing page. <br/>
	 * Hooked over 'woofunnels_tabs_modal_licenses'. <br/>
	 * @return mixed false on failure and data on success
	 */
	public static function woofunnels_tools_data() {
		if ( false === WooFunnels_API::get_woofunnels_status() ) {
			return;
		}
		$get_list = array();

		return (object) array_merge( (array) $get_list, (array) array(
			'additional_tabs' => apply_filters( 'woofunnels_additional_tabs', array(
				array(
					'slug'  => 'tools',
					'label' => __( 'Tools', 'woofunnels' ),
				),
			) ),

			'current_tab' => self::$selected,
		) );
	}

	/**
	 * Model function to fire over support page. <br/>
	 * Hooked over 'woofunnels_tabs_modal_support'. <br/>
	 * @return mixed false on failure and data on success
	 */
	public static function woofunnels_support_data( $data ) {
		//getting plugins list and tabs data
		$get_list = array();

		return (object) array_merge( (array) $get_list, (array) array(
			'additional_tabs' => apply_filters( 'woofunnels_additional_tabs', array(
				array(
					'slug'  => 'tools',
					'label' => __( 'Tools', 'woofunnels' ),
				),
			) ),
			'email'           => get_bloginfo( 'admin_email' ),
			'current_tab'     => self::$selected,
		) );
	}

	/**
	 * Model function to fire over support page. <br/>
	 * Hooked over 'woofunnels_tabs_modal_logs'. <br/>
	 * @return mixed false on failure and data on success
	 */
	public static function woofunnels_logs_data( $data ) {
		//getting plugins list and tabs data
		$get_list = array();

		return (object) array_merge( (array) $get_list, (array) array(
			'additional_tabs' => apply_filters( 'woofunnels_additional_tabs', array(
				array(
					'slug'  => 'tools',
					'label' => __( 'Tools', 'woofunnels' ),
				),
			) ),
			'current_tab'     => self::$selected,
		) );
	}

	public static function show_right_area() {
		if ( wp_verify_nonce( filter_input( INPUT_GET, '_nonce', FILTER_SANITIZE_STRING ), 'bwf_tools_action' ) && isset( $_GET['woofunnels_transient'] ) && ( 'clear' === sanitize_text_field( $_GET['woofunnels_transient'] ) ) ) {
			$woofunnels_transient_obj = WooFunnels_Transient::get_instance();
			$woofunnels_transient_obj->delete_force_transients();
			$message = __( 'All Plugins transients cleared.', 'woofunnels' );

			?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
			<?php
		}
		$nonce               = wp_create_nonce( 'bwf_tools_action' );
		$clear_transient_url = admin_url( 'admin.php?page=woofunnels&tab=tools&woofunnels_transient=clear&_nonce=' . $nonce );
		$show_reset_tracking = apply_filters( 'woofunnels_show_reset_tracking', false );
		?>
		<table class="widefat" cellspacing="0">
			<tbody class="tools">
			<?php do_action( 'woofunnels_tools_add_tables_row_start' ); ?>
			<tr>
				<th>
					<strong class="name"><?php esc_html_e( 'WooFunnels transients', 'woofunnels' ); ?></strong>
					<p class="description"><?php esc_html_e( 'This tool will clear all the WooFunnels plugins transients cache.', 'woofunnels' ); ?></p>
				</th>
				<td class="run-tool">
					<a href="<?php echo esc_url( $clear_transient_url ); ?>" class="button button-large"><?php esc_html_e( 'Clear transients', 'woofunnels' ); ?></a>
				</td>
			</tr>
			<?php if ( true === $show_reset_tracking ) {

				$reset_tracking_url = admin_url( 'admin.php?page=woofunnels&tab=tools&woofunnels_tracking=reset&_nonce=' . $nonce );
				$is_opted           = WooFunnels_OptIn_Manager::get_optIn_state();
				if ( 'yes' === $is_opted ) {
					$text_btn = __( 'Turn Off', 'woofunnels' );
				} else {
					$reset_tracking_url .= '&action=yes';
					$text_btn           = __( 'Turn On', 'woofunnels' );
				}
				?>
				<tr>
					<th>
						<strong class="name"><?php esc_html_e( 'Usage Tracking', 'woofunnels' ); ?></strong>
						<p class="description"><?php esc_html_e( 'This action controls Usage Tracking', 'woofunnels' ); ?></p>
					</th>
					<td class="run-tool">
						<a href="<?php echo esc_url( $reset_tracking_url ); ?>" class="button button-large"><?php echo esc_html( $text_btn ); ?></a>
					</td>
				</tr>
			<?php } ?>
			<?php do_action( 'woofunnels_tools_add_tables_row' ); ?>
			</tbody>
		</table>
		<?php
	}

	public static function add_logs_tabs( $tabs ) {
		$tabs[] = array(
			'slug'  => 'logs',
			'label' => __( 'Logs', 'woofunnels' ),
		);

		return $tabs;
	}

	public static function autoloader( $class_name ) {
		if ( 0 === strpos( $class_name, 'WooFunnels_' ) || 0 === strpos( $class_name, 'BWF_' ) ) {
			$path         = WooFunnel_Loader::$ultimate_path . 'includes/class-' . self::slugify_classname( $class_name ) . '.php';
			$contact_path = WooFunnel_Loader::$ultimate_path . 'contact/class-' . self::slugify_classname( $class_name ) . '.php';

			if ( is_file( $path ) ) {
				require_once $path;
			}
			if ( is_file( $contact_path ) ) {
				require_once $contact_path;
			}
		}
		if ( 0 === strpos( $class_name, 'WFCO_' ) ) {
			$path       = WooFunnel_Loader::$ultimate_path . 'connector/class-' . self::slugify_classname( $class_name ) . '.php';
			$model_path = WooFunnel_Loader::$ultimate_path . 'includes/class-' . self::slugify_classname( $class_name ) . '.php';

			if ( is_file( $path ) ) {
				require_once $path;
			}
			if ( is_file( $model_path ) ) {
				require_once $model_path;
			}
		}
	}

	/**
	 * Slug-ify the class name and remove underscores and convert it to filename
	 * Helper function for the auto-loading
	 *
	 * @param $class_name
	 *
	 *
	 * @return mixed|string
	 * @see WooFunnels_Dashboard::autoloader();
	 *
	 */
	public static function slugify_classname( $class_name ) {
		$classname = sanitize_title( $class_name );
		$classname = str_replace( '_', '-', $classname );

		return $classname;
	}

	public static function load_core_classes() {

		require dirname( __DIR__ ) . '/includes/bwf-functions.php';
		require dirname( __DIR__ ) . '/contact/woofunnels-db-updater-functions.php';
		require dirname( __DIR__ ) . '/contact/woofunnels-contact-functions.php';
		require dirname( __DIR__ ) . '/contact/woofunnels-customer-functions.php';

		$core_classes = array(
			'WooFunnels_process',
			'BWF_Logger',
			'WooFunnels_Notifications',
			'WooFunnels_DB_Updater',
			'WooFunnels_DB_Tables',
			'WFCO_Admin',
			'WooFunnels_Funnel_Builder_Commons',
			'BWF_Data_Tags'
		);
		foreach ( $core_classes as $class ) {
			self::$classes[ $class ] = $class::get_instance();
		}

		$static_classes = array( 'WooFunnels_OptIn_Manager', 'WooFunnels_deactivate' );
		foreach ( $static_classes as $class ) {
			$class::init();
		}

		/**
		 * Common function files
		 */

		include_once dirname( __DIR__ ) . '/compatibilities/class-bwf-wc-compatibility.php';
		include_once dirname( __DIR__ ) . '/compatibilities/class-bwf-plugin-compatibilities.php';

		/** WooFunnels AS Data Store */
		include_once dirname( __DIR__ ) . '/as-data-store/class-woofunnels-as-ds.php';
		add_action( 'shutdown', [ __CLASS__, 'fetch_template_json' ] );
		add_action( 'admin_init', array( __CLASS__, 'index_templates' ) );
		//add_action( 'woofunnels_tools_add_tables_row', [ __CLASS__, 'wfocu_add_template_indexing_tools' ] );
	}

	public static function apply_scroll_fix_css() {
		global $current_screen;
		if ( $current_screen instanceof WP_Screen && strpos( $current_screen->base, 'woofunnels' ) !== false ) {
			/** Allow for woofunnels pages */
		} elseif ( false === apply_filters( 'bwf_scroll_fix_allow', false ) ) {
			return;
		}
		ob_start();
		?>
		<style>
            #adminmenuwrap {
                position: relative !important;
                top: 0 !important;
            }

            body, html {
                height: auto;
                position: relative
            }
		</style>
		<?php
		echo ob_get_clean();
	}

	public static function wfocu_add_template_indexing_tools() {
		?>
		<tr>
			<th>
				<strong class="name"><?php esc_html_e( 'Index Templates JSON', 'woofunnels' ); ?></strong>
				<p class="description"><?php esc_html_e( 'This tool will populate the option key with latest templates.', 'woofunnels' ); ?></p>
			</th>
			<td class="run-tool">
				<a href="<?php echo esc_url( admin_url() . '?wffn_parse_template=yes' ); ?>" class="button button-large"><?php esc_html_e( 'Index Templates', 'woofunnels' ); ?></a>
			</td>
		</tr>

		<?php
	}

	public static function index_templates() {
		$get_templates = filter_input( INPUT_GET, 'wffn_parse_template', FILTER_SANITIZE_STRING );

		if ( 'yes' === $get_templates && current_user_can( 'manage_options' ) ) {
			self::get_all_templates( true );
			wp_safe_redirect( admin_url( 'admin.php?page=woofunnels&tab=tools' ) );
			exit;
		}
	}

	public static function get_template_api_url() {
		return 'http://gettemplates.buildwoofunnels.com/';
	}

	public static function get_remote_templates() {
		$json_templates = [];
		$endpoint_url   = self::get_template_api_url();
		$response       = wp_safe_remote_get( $endpoint_url . 'templates.json' );
		WooFunnels_Dashboard::$classes['BWF_Logger']->log( 'API Call templates.json response::: ' . print_r( $response, true ), 'wffn_template_import' ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			if ( ! empty( $body ) ) {
				$json_templates = json_decode( $body, true );
				if ( is_array( $json_templates ) ) {
					update_option( '_bwf_fb_templates', $json_templates, false );
				}
			}
		}

		return $json_templates;
	}

	public static function get_all_templates( $force = false ) {
		$get_transient_instance = WooFunnels_Transient::get_instance();
		$transient              = $get_transient_instance->get_transient( '_bwf_fb_templates' );
		if ( empty( $transient ) || true === $force ) {
			$templates = self::get_remote_templates();
			if ( is_array( $templates ) ) {
				$get_transient_instance->set_transient( '_bwf_fb_templates', count( $templates ), HOUR_IN_SECONDS * 12 );
			}

			return $templates;
		}

		return get_option( '_bwf_fb_templates' );
	}

	public static function fetch_template_json() {
		if ( 'yes' === filter_input( INPUT_GET, 'activated', FILTER_SANITIZE_STRING ) ) {
			flush_rewrite_rules(); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules
			self::get_all_templates();
		}
	}


}

spl_autoload_register( array( 'WooFunnels_dashboard', 'autoloader' ) );
WooFunnels_dashboard::load_core_classes();

//Initialize the instance so that some necessary hooks can run on each load
add_action( 'admin_init', array( 'WooFunnels_dashboard', 'init' ), 11 );
