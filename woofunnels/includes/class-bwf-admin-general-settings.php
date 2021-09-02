<?php
/**
 * Class to control Settings and its behaviour across the buildwoofunnels
 * @author buildwoofunnels
 */

if ( ! class_exists( 'BWF_Admin_General_Settings' ) ) {

	class BWF_Admin_General_Settings {

		private static $ins = null;
		private $options = array();

		public function __construct() {

			add_filter( 'woofunnels_global_settings', function ( $menu ) {
				array_push( $menu, array(
					'title'    => __( 'General', 'woofunnels' ),
					'slug'     => 'woofunnels_general_settings',
					'link'     => apply_filters( 'bwf_general_settings_link', 'javascript:void(0)' ),
					'priority' => 5,
				) );

				return $menu;
			} );
			add_action( 'wp_ajax_bwf_general_settings_update', [ $this, 'update_general_settings' ] );
			add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 101 );

			add_action( 'admin_head', array( $this, 'hide_from_menu' ) );
			add_filter( 'admin_title', array( $this, 'maybe_change_title' ), 99 );
			add_filter( 'woofunnels_global_settings_fields', array( $this, 'add_settings_fields_array' ), 99 );
			add_action( 'bwf_global_save_settings_woofunnels_general_settings', array( $this, 'update_global_settings_fields' ), 99 );

		}

		public static function get_instance() {

			if ( null === self::$ins ) {
				self::$ins = new self;
			}

			return self::$ins;
		}

		public function maybe_flush_rewrite_rules() {
			$is_required_rewrite = get_option( 'bwf_needs_rewrite', 'no' );
			if ( 'yes' === $is_required_rewrite ) {
				flush_rewrite_rules(); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules
				WooFunnels_Dashboard::get_all_templates();
				update_option( 'bwf_needs_rewrite', 'no', true );
			}
		}

		public function add_settings_fields_array( $fields ) {
			$fields['woofunnels_general_settings'] = $this->all_fields();

			return $fields;
		}

		public function __callback() {
			/** Registering Settings in top bar */
			if ( class_exists( 'BWF_Admin_Breadcrumbs' ) ) {
				BWF_Admin_Breadcrumbs::register_node( [ 'text' => 'Settings' ] );
			}
			BWF_Admin_Breadcrumbs::render_sticky_bar();
			?>
            <div class="wrap bwf-funnel-common">
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Settings', 'woofunnels' ); ?></h1>
				<?php
				$admin_settings = BWF_Admin_Settings::get_instance();
				$admin_settings->render_tab_html( 'woofunnels_general_settings' );
				$i = 0;
				?>
                <div id="bwf_general_settings_vue_wrap" class="bwf-hide" v-bind:class="`1`===is_initialized?'bwf-show':''">
                    <div class="bwf-vue-custom-msg" v-if="'' != errorMsg"><p v-html="errorMsg"></p></div>
                    <div class="bwf-tabs-view-vertical bwf-widget-tabs">
                        <div class="bwf-tabs-wrapper">
                            <div class="bwf-tab-title" data-tab="<?php $i ++;
							echo $i; ?>" role="tab">
								<?php esc_html_e( 'Permalinks', 'woofunnels' ); ?>
                            </div>
                            <div class="bwf-tab-title" data-tab="<?php $i ++;
							echo $i; ?>" role="tab">
								<?php esc_html_e( 'Facebook Pixel', 'woofunnels' ); ?>
                            </div>
                            <div class="bwf-tab-title" data-tab="<?php $i ++;
							echo $i; ?>" role="tab">
								<?php esc_html_e( 'Google Analytics', 'woofunnels' ); ?>
                            </div>


							<?php if ( apply_filters( 'bwf_enable_ecommerce_integration_gad', false ) ) { ?>
                                <div class="bwf-tab-title" data-tab="<?php $i ++;
								echo $i; ?>" role="tab">
									<?php esc_html_e( 'Google Ads', 'woofunnels' ); ?>
                                </div>
							<?php }
							if ( apply_filters( 'bwf_enable_ecommerce_integration_pinterest', false ) ) { ?>
                                <div class="bwf-tab-title" data-tab="<?php $i ++;
								echo $i; ?>" role="tab">
									<?php esc_html_e( 'Pinterest', 'woofunnels' ); ?>
                                </div>
							<?php } ?>

                        </div>


                        <div class="bwf-tabs-content-wrapper">
                            <div class="bwf_setting_inner">
                                <form class="bwf_forms_wrap">
                                    <fieldset>
                                        <vue-form-generator :schema="schema" :model="model" :options="formOptions"></vue-form-generator>
                                    </fieldset>
                                    <div style="display: none" id="modal-general-settings_success" data-iziModal-icon="icon-home">
                                    </div>
                                </form>
                                <div class="bwf_form_button">
                                    <span class="bwf_loader_global_save spinner" style="float: left;"></span>
                                    <button v-on:click.self="onSubmit" class="bwf_save_btn_style"><?php esc_html_e( 'Save changes', 'woofunnels' ); ?></button>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

			<?php
		}

		public function default_general_settings() {
			return apply_filters( 'bwf_general_settings_default_config', array(

				'fb_pixel_key'                      => '',
				'ga_key'                            => '',
				'gad_key'                           => '',
				'gad_conversion_label'              => '',
				'is_fb_purchase_event'              => array(),
				'is_fb_synced_event'                => array(),
				'is_fb_advanced_event'              => array(),
				'content_id_value'                  => '',
				'content_id_variable'               => array(),
				'content_id_prefix'                 => '',
				'content_id_suffix'                 => '',
				'track_traffic_source'              => array(),
				'exclude_from_total'                => array(),
				'enable_general_event'              => array(),
				'general_event_name'                => 'GeneralEvent',
				'custom_aud_opt_conf'               => array(),
				'is_ga_purchase_event'              => array(),
				'is_gad_purchase_event'             => array(),
				'pixel_initiate_checkout_event'     => '',
				'pixel_add_to_cart_event'           => '',
				'pixel_add_payment_info_event'      => '',
				'pixel_variable_as_simple'          => '',
				'pixel_content_id_type'             => '0',
				'pixel_content_id_prefix'           => '',
				'pixel_content_id_suffix'           => '',
				'google_ua_add_to_cart_event'       => '',
				'google_ua_initiate_checkout_event' => '',
				'google_ua_add_payment_info_event'  => '',
				'google_ua_variable_as_simple'      => '',
				'google_ua_content_id_type'         => '0',
				'google_ua_content_id_prefix'       => '',
				'google_ua_content_id_suffix'       => '',
				'ga_track_traffic_source'           => array(),
				'gad_exclude_from_total'            => array(),
				'id_prefix_gad'                     => '',
				'id_suffix_gad'                     => '',
				'pixel_is_page_view'                => '',
				'google_ua_is_page_view'            => '',
				'is_fb_purchase_page_view'          => array(),
				'is_ga_purchase_page_view'          => array(),
				'is_fb_page_view_lp'                => array(),
				'is_fb_page_view_op'                => array(),
				'pint_key'                          => '',
				'is_pint_purchase_event'            => array(),
				'is_fb_purchase_conversion_api'     => array(),
				'conversion_api_test_event_code'    => '',
				'conversion_api_access_token'       => '',
				'is_fb_conv_enable_test'            => array(),
				'is_fb_conversion_api_log'          => array(),
				'default_selected_builder'          => 'elementor',

			) );
		}

		public function get_option( $key = 'all' ) {

			if ( empty( $this->options ) ) {
				$this->setup_options();
			}
			if ( 'all' === $key ) {
				return $this->options;
			}

			return isset( $this->options[ $key ] ) ? $this->options[ $key ] : false;
		}

		public function setup_options() {
			$db_options = get_option( 'bwf_gen_config', [] );

			$db_options    = ( ! empty( $db_options ) && is_array( $db_options ) ) ? array_map( function ( $val ) {
				return is_scalar( $val ) ? html_entity_decode( $val ) : $val;
			}, $db_options ) : array();
			$this->options = wp_parse_args( $db_options, $this->default_general_settings() );

			return $this->options;
		}

		public function maybe_add_js() {
			wp_enqueue_script( 'bwf-general-settings', plugin_dir_url( WooFunnel_Loader::$ultimate_path ) . 'woofunnels/assets/js/bwf-general-settings.js', [], BWF_VERSION );
			wp_enqueue_style( 'bwf-general-settings', plugin_dir_url( WooFunnel_Loader::$ultimate_path ) . 'woofunnels/assets/css/bwf-general-settings.css', array(), BWF_VERSION );


			wp_localize_script( 'bwf-general-settings', 'bwfAdminGen', $this->get_localized_data() );
		}

		public function get_localized_data() {
			$localized_data = [
				'nonce_general_settings' => wp_create_nonce( 'bwf_general_settings_update' ),
				'texts'                  => array(
					'settings_success'    => __( 'Changes saved', 'woofunnels' ),
					'permalink_help_text' => __( 'Leave empty to remove slug completely from url', 'woofunnels' ),
				),
				'globalOptionsFields'    => array(
					'options'       => $this->filter_admin_options( $this->get_option() ),
					'legends_texts' => array(
						'fb'         => __( 'Facebook Pixel', 'woofunnels' ),
						'ga'         => __( 'Google Analytics', 'woofunnels' ),
						'gad'        => __( 'Google Ads', 'woofunnels' ),
						'pint'       => __( 'Pinterest', 'woofunnels' ),
						'permalinks' => __( 'Permalinks', 'woofunnels' ),
					),
					'fields'        => $this->all_fields()
				)
			];


			$localized_data['is_pinterest_enabled']   = ( true === apply_filters( 'bwf_enable_ecommerce_integration_pinterest', false ) ) ? 1 : 0;
			$localized_data['is_gad_enabled']         = ( true === apply_filters( 'bwf_enable_ecommerce_integration_gad', false ) ) ? 1 : 0;
			$localized_data['if_fb_checkout_enabled'] = ( true === apply_filters( 'bwf_enable_ecommerce_integration_fb_checkout', false ) ) ? 1 : 0;
			$localized_data['if_fb_purchase_enabled'] = ( true === apply_filters( 'bwf_enable_ecommerce_integration_fb_purchase', false ) ) ? 1 : 0;
			$localized_data['if_ga_checkout_enabled'] = ( true === apply_filters( 'bwf_enable_ecommerce_integration_ga_checkout', false ) ) ? 1 : 0;
			$localized_data['if_ga_purchase_enabled'] = ( true === apply_filters( 'bwf_enable_ecommerce_integration_ga_purchase', false ) ) ? 1 : 0;
			$localized_data['if_landing_enabled']     = ( true === apply_filters( 'bwf_enable_ecommerce_integration_landing', false ) ) ? 1 : 0;
			$localized_data['if_optin_enabled']       = ( true === apply_filters( 'bwf_enable_ecommerce_integration_optin', false ) ) ? 1 : 0;
			$checkout_page_slug                       = 'checkout';
			$checkout_id                              = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'checkout' ) : 0;
			if ( $checkout_id > - 1 ) {
				$checkout_page = get_post( $checkout_id );
				if ( $checkout_page instanceof WP_Post ) {
					$checkout_page_slug = $checkout_page->post_name;
				}
			}
			$localized_data['checkout_page_slug']  = $checkout_page_slug;
			$localized_data['permalink_structure'] = get_option( 'permalink_structure' );
			$localized_data['errors']              = array(
				'checkout_slug' => sprintf( __( 'Error: The permalink "%s" is reserved by Native WooCommerce Checkout Page. Try another permalink.', 'woofunnels' ), $checkout_page_slug ),
				'empty_base'    => sprintf( __( 'Error: The current Permalinks settings does not allow blank values. Switch Permalink settings to \'Post name\'. <a href="%s">Click Here To Change</a>', 'woofunnels' ), admin_url( 'options-permalink.php' ) ),
			);

			return $localized_data;
		}

		public function update_general_settings() {
			check_admin_referer( 'bwf_general_settings_update', '_nonce' );
			$options = isset( $_POST['data'] ) ? bwf_clean( $_POST['data'] ) : 0;
			$resp    = $this->update_global_settings_fields( $options );
			wp_send_json( $resp );
		}

		public function update_global_settings_fields( $options ) {
			$options = ( is_array( $options ) && wp_unslash( bwf_clean( $options ) ) ) ? bwf_clean( $options ) : 0;
			$resp    = [
				'status' => false,
				'msg'    => __( 'Settings Updated', 'woofunnels' ),
				'data'   => '',
			];

			if ( $options !== 0 ) {
				update_option( 'bwf_gen_config', $options, true );
				update_option( 'bwf_needs_rewrite', 'yes', true );
				$resp['status'] = true;
			}

			return $resp;
		}

		public function get_settings_link() {
			return apply_filters( 'bwf_general_settings_link', 'javascript:void(0)' );
		}

		public function hide_from_menu() {
			global $woofunnels_menu_slug;

			global $parent_file, $plugin_page, $submenu_file; //phpcs:ignore
			if ( filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) === 'bwf_settings' ) :
				$parent_file  = $woofunnels_menu_slug;//phpcs:ignore
				$submenu_file = 'admin.php?page=woofunnels_settings'; //phpcs:ignore
			endif;
		}

		/**
		 * Filter options before passing it to the javascript
		 *
		 * @param $config array configuration array
		 *
		 * @return array
		 */
		public function filter_admin_options( $config ) {
			foreach ( $config as $key => &$data ) {

				/**
				 * Check if data is 'false' (string) then make it blank so that checkboxes works accordingly
				 */
				if ( 'false' === $data ) {
					$config[ $key ] = '';
				}
			}

			return $config;
		}

		public function maybe_change_title( $title ) {
			if ( 'bwf_settings' === filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) || 'bwf_settings' === filter_input( INPUT_GET, 'section', FILTER_SANITIZE_STRING ) ) {
				$admin_title = get_bloginfo( 'name' );
				$title       = sprintf( __( '%1$s &lsaquo; %2$s &#8212; WordPress' ), 'WooFunnels', $admin_title );
			}

			return $title;
		}

		public function all_fields() {

			$static_config  = apply_filters( 'bwf_settings_config', array(

				'general'          => array(
					'title'    => __( 'General', 'woofunnels' ),
					'heading'  => __( 'General', 'woofunnels' ),
					'slug'     => 'general',
					'fields'   => apply_filters( 'bwf_settings_config_general', array(
						array(
							'key'           => 'default_selected_builder',
							'type'          => 'select',
							'label'         => 'Default Page Builder',
							'hint'          => '',
							'values'        => [
								[ 'id' => 'elementor', 'name' => __( 'Elementor', 'woofunnels' ) ],
								[ 'id' => 'divi', 'name' => __( 'Divi', 'woofunnels' ) ],
								[ 'id' => 'customizer', 'name' => __( 'Customizer', 'woofunnels' ) ],
								[ 'id' => 'wp_editor', 'name' => __( 'Other', 'woofunnels' ) ],
							],
							'selectOptions' => [
								'hideNoneSelectedText' => true,
							],
						),
					) ),
					'priority' => 1,
				),
				'permalinks'       => array(
					'title'    => __( 'Permalinks', 'woofunnels' ),
					'heading'  => __( 'Permalinks', 'woofunnels' ),
					'slug'     => 'permalinks',
					'fields'   => array(),
					'priority' => 5,
				),
				'facebook_pixel'   => array(
					'title'    => __( 'Facebook Pixel', 'woofunnels' ),
					'heading'  => __( 'Facebook Pixel', 'woofunnels' ),
					'slug'     => 'facebook_pixel',
					'fields'   => array(
						array(
							'key'   => 'fb_pixel_key',
							'label' => __( 'Pixel ID', 'woofunnels' ),
							'type'  => "input",
							'hint'  => __( 'Log into your Facebook ads account to find your Pixel ID. <a target="_blank" href="https://www.facebook.com/ads/manager/pixel/facebook_pixel/">Click here for more info.</a>', 'woofunnels' ),
						),

						array(
							'key'    => 'is_fb_purchase_conversion_api',
							'type'   => 'checklist',
							'label'  => '',
							'hint'   => __( 'Send events directly from server to Facebook through the Conversion API. An access token is required to use the server-side API. <a target="_blank" href="https://buildwoofunnels.com/docs/funnel-builder/global-settings/facebook-conversion-api/">Generate Access Token</a>', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable Conversion API', 'woofunnels' ),
									'value' => 'yes',
								),
							),

						),
						array(
							'key'         => 'conversion_api_access_token',
							'type'        => 'textArea',
							'label'       => '',
							'placeholder' => __( 'Paste your access token here', 'woofunnels' ),
							'toggler'     => array(
								'key'   => 'is_fb_purchase_conversion_api',
								'value' => array( 'yes' ),
							),
						),


						array(
							'key'   => 'is_fb_conv_enable_test',
							'label' => __( '', 'woofunnels' ),
							'hint'  => __( 'Use test_event_code to verify server-side events. <strong>Uncheck this option after testing</strong>.', 'woofunnels' ),

							'type'    => "checklist",
							'values'  => array(
								array(
									'name'  => __( 'Test server events via test_event_code', 'woofunnels' ),
									'value' => 'yes',
								),
							),
							'toggler' => array(
								'key'   => 'is_fb_purchase_conversion_api',
								'value' => array( 'yes' ),
							),
						),


						array(
							'key'         => 'conversion_api_test_event_code',
							'type'        => 'input',
							'label'       => '',
							'hint'        => __( '<a target="_blank" href="https://buildwoofunnels.com/docs/funnel-builder/global-settings/facebook-conversion-api/#step-1-select-your-pixel-id-and-go-to-%E2%80%9Ctest-events%E2%80%9D">Learn how to get test_event_code</a>', 'woofunnels' ),
							'placeholder' => __( 'Paste your test_event_code here', 'woofunnels' ),
							'toggler'     => array(
								'key'   => 'is_fb_conv_enable_test',
								'value' => array( 'yes' ),
							),
						),
						array(
							'key'     => 'is_fb_conversion_api_log',
							'type'    => 'checklist',
							'label'   => '',
							'hint'    => __( 'Use this option to log API request & response. <strong>Uncheck this option after testing</strong>.  <a target="_blank" href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs' ) ) . '">Click here to access logs.</a>', 'woofunnels' ),
							'values'  => array(
								array(
									'name'  => __( 'Enable Purchase Event Logs', 'woofunnels' ),
									'value' => 'yes',
								),
							),
							'toggler' => array(
								'key'   => 'is_fb_purchase_conversion_api',
								'value' => array( 'yes' ),
							),
						),
						array(
							'key'    => 'is_fb_page_view_lp',
							'label'  => __( 'Landing Page Events', 'woofunnels' ),
							'type'   => "checklist",
							'values' => array(
								array(
									'name'  => __( 'Enable PageView Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						array(
							'key'    => 'is_fb_page_view_op',
							'label'  => __( 'Optin Page Events', 'woofunnels' ),
							'type'   => "checklist",
							'values' => array(
								array(
									'name'  => __( 'Enable PageView Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						array(
							'key'          => 'label_section_head_fb',
							'type'         => "label",
							'label'        => __( 'Checkout Events', 'woofunnels' ),
							'styleClasses' => [ 'wfacp_setting_track_and_events_start', 'bwf_wrap_custom_html_tracking_general' ],
						),
						array(
							'key'          => 'pixel_is_page_view',
							'type'         => 'checkbox',
							'label'        => __( 'Enable PageView Event', 'woofunnels' ),
							'styleClasses' => [ 'wfacp_checkbox_wrap', 'wfacp_setting_track_and_events_end' ],
						),
						array(
							'key'   => 'pixel_initiate_checkout_event',
							'type'  => 'checkbox',
							'label' => __( 'Enable InitiateCheckout Event', 'woofunnels' ),

						),
						array(
							'key'   => 'pixel_add_to_cart_event',
							'type'  => 'checkbox',
							'label' => __( 'Enable AddtoCart Event', 'woofunnels' ),

						),
						array(
							'key'   => 'pixel_add_payment_info_event',
							'type'  => 'checkbox',
							'label' => __( 'Enable AddPaymentInfo Event', 'woofunnels' ),

						),
						array(
							'key'   => 'pixel_variable_as_simple',
							'type'  => 'checkbox',
							'label' => __( 'Treat variable products like simple products', 'woofunnels' ),
							'hint'  => __( 'Turn this option ON when your Product Catalog doesn\'t include the variants for variable products.', 'woofunnels' ),

						),
						array(
							'key'           => 'pixel_content_id_type',
							'styleClasses'  => [ 'group-one-class' ],
							'type'          => 'select',
							'label'         => '',
							'default'       => '0',
							'values'        => [
								[ 'id' => '0', 'name' => __( 'Select content id parameter', 'woofunnels' ) ],
								[ 'id' => 'product_id', 'name' => __( 'Product ID', 'woofunnels' ) ],
								[ 'id' => 'product_sku', 'name' => __( 'Product SKU', 'woofunnels' ) ],
							],
							'selectOptions' => [
								'hideNoneSelectedText' => true,
							],
						),
						array(
							'key'         => 'pixel_content_id_prefix',
							'type'        => 'input',
							'label'       => '',
							'placeholder' => __( 'content id prefix', 'woofunnels' ),
							'hint'        => __( 'Add prefix to the content_id parameter (optional)', 'woofunnels' ),

						),
						array(
							'key'         => 'pixel_content_id_suffix',
							'type'        => 'input',
							'label'       => '',
							'placeholder' => __( 'content id suffix', 'woofunnels' ),
							'hint'        => __( 'Add suffix to the content_id parameter (optional)', 'woofunnels' ),

						),
						array(
							'key'   => 'pixel_exclude_tax',
							'type'  => 'checkbox',
							'label' => __( 'Exclude Taxes', 'woofunnels' ),
							'hint'  => __( "Exclude Tax in Total( Item Subtotal,Order Total )", 'woofunnels' ),

						),
						array(
							'key'    => 'is_fb_purchase_page_view',
							'type'   => 'checklist',
							'label'  => __( 'Purchase Events', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( "Enable PageView Event", 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						array(
							'key'    => 'is_fb_purchase_event',
							'type'   => 'checklist',
							'label'  => '',
							'hint'   => __( 'Note: WooFunnels will send total order value and store currency based on order. <a target="_blank" href="https://developers.facebook.com/docs/facebook-pixel/pixel-with-ads/conversion-tracking#add-value">Click here to know more.</a>', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable Purchase Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),

						array(
							'key'     => 'custom_aud_opt_conf',
							'label'   => '',
							'type'    => 'checklist',
							'hint'    => __( 'Choose the parameters you want to send with purchase event', 'woofunnels' ),
							'values'  => array(
								array(
									'name'  => __( 'Add Town,State & Country Parameters', 'woofunnels' ),
									'value' => 'add_town_s_c',
								),
								array(
									'name'  => __( 'Add Payment Method Parameters', 'woofunnels' ),
									'value' => 'add_payment_method',
								),
								array(
									'name'  => __( 'Add Shipping Method Parameters', 'woofunnels' ),
									'value' => 'add_shipping_method',
								),
								array(
									'name'  => __( 'Add Coupon parameters', 'woofunnels' ),
									'value' => 'add_coupon',
								),
							),
							'toggler' => array(
								'key'   => 'is_fb_purchase_event',
								'value' => array( 'yes' ),
							),
						),
						array(
							'key'     => 'exclude_from_total',
							'label'   => '',
							'type'    => 'checklist',
							'hint'    => __( 'Check above boxes to exclude shipping/taxes from the total.', 'woofunnels' ),
							'values'  => array(
								array(
									'name'  => __( 'Exclude Shipping from Total', 'woofunnels' ),
									'value' => 'is_disable_shipping',
								),
								array(
									'name'  => __( 'Exclude Taxes from Total', 'woofunnels' ),
									'value' => 'is_disable_taxes',
								),

							),
							'toggler' => array(
								'key'   => 'is_fb_purchase_event',
								'value' => array( 'yes' ),
							),
						),
						array(
							'key'    => 'is_fb_synced_event',
							'type'   => 'checklist',
							'label'  => '',
							'hint'   => __( 'Note: Your Product catalog must be synced with Facebook. <a target="_blank" href="https://developers.facebook.com/docs/facebook-pixel/implementation/dynamic-ads">Click here to know more.</a>', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable Content Settings for Dynamic Ads', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						array(
							'key'     => 'content_id_variable',
							'type'    => 'checklist',
							'label'   => '',
							'hint'    => __( 'Turn this option ON when your Product Catalog doesn\'t include the variants for variable products.', 'woofunnels' ),
							'values'  => array(
								array(
									'name'  => __( 'Treat variable products like simple products', 'woofunnels' ),
									'value' => 'yes',
								),
							),
							'toggler' => array(
								'key'   => 'is_fb_synced_event',
								'value' => array( 'yes' ),
							),
						),
						array(
							'key'     => 'content_id_value',
							'type'    => 'select',
							'label'   => '',
							'hint'    => __( 'Select either Product ID or SKU to pass value in content_id parameter', 'woofunnels' ),
							'values'  => array(
								array(
									'id'   => '',
									'name' => __( 'Select content_id parameter', 'woofunnels' ),
								),
								array(
									'id'   => 'product_id',
									'name' => __( 'Product ID', 'woofunnels' ),
								),
								array(
									'id'   => 'product_sku',
									'name' => __( 'Product SKU', 'woofunnels' ),
								),

							),
							'toggler' => array(
								'key'   => 'is_fb_synced_event',
								'value' => array( 'yes' ),
							),
						),
						array(
							'key'         => 'content_id_prefix',
							'type'        => 'input',
							'label'       => '',
							'placeholder' => __( 'content_id prefix', 'woofunnels' ),
							'hint'        => __( 'Add prefix to the content_id parameter (optional)', 'woofunnels' ),
							'toggler'     => array(
								'key'   => 'is_fb_synced_event',
								'value' => array( 'yes' ),
							),

						),
						array(
							'key'         => 'content_id_suffix',
							'type'        => 'input',
							'label'       => '',
							'placeholder' => __( 'content_id suffix', 'woofunnels' ),
							'hint'        => __( 'Add suffix to the content_id parameter (optional)', 'woofunnels' ),
							'toggler'     => array(
								'key'   => 'is_fb_synced_event',
								'value' => array( 'yes' ),
							),
						),
						array(
							'key'    => 'enable_general_event',
							'type'   => 'checklist',
							'label'  => '',
							'hint'   => __( 'Use the GeneralEvent for your Custom Audiences and Custom Conversions.', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable General Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),

						),
						array(
							'type'        => 'input',
							'key'         => 'general_event_name',
							'label'       => '',
							'placeholder' => __( 'General Event Name', 'woofunnels' ),
							'hint'        => __( 'Customize the name of general event.', 'woofunnels' ),
							'toggler'     => array(
								'key'   => 'enable_general_event',
								'value' => array( 'yes' ),
							),
						),
						array(
							'type'   => 'checklist',
							'key'    => 'is_fb_advanced_event',
							'label'  => '',
							'hint'   => __( 'Note: WooFunnels will send customer\'s email, name, phone, address fields whichever available in the order. <a target="_blank" href="https://developers.facebook.com/docs/facebook-pixel/pixel-with-ads/conversion-tracking#advanced_match">Click here to know more.', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable Advanced Matching With the Pixel', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						array(
							'key'    => 'track_traffic_source',
							'type'   => 'checklist',
							'label'  => '',
							'hint'   => __( 'Add traffic source as traffic_source and URL parameters (UTM) as parameters to all your events.', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Track Traffic Source & UTMs', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
					),
					'priority' => 10,
				),
				'google_analytics' => array(
					'title'    => __( 'Google Analytics', 'woofunnels' ),
					'heading'  => __( 'Google Analytics', 'woofunnels' ),
					'slug'     => 'google_analytics',
					'fields'   => array(
						array(
							'key'   => 'ga_key',
							'type'  => 'input',
							'label' => __( 'Analytics ID', 'woofunnels' ),
							'hint'  => __( 'Log into your Google Analytics account to find your Analytics ID. <a target="_blank" href="http://analytics.google.com/analytics/web/">Click here for more info.</a>', 'woofunnels' ),
						),
						array(
							'key'    => 'is_ga_page_view_lp',
							'type'   => 'checklist',
							'label'  => __( 'Landing Page Events', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable PageView Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						array(
							'type'   => 'checklist',
							'key'    => 'is_ga_page_view_op',
							'label'  => __( 'Optin Page Events', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable PageView Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						array(
							'type'         => 'label',
							'key'          => 'label_section_head_ga',
							'label'        => __( 'Checkout Events', 'woofunnels' ),
							'styleClasses' => [ 'wfacp_setting_track_and_events_start', 'bwf_wrap_custom_html_tracking_general' ],
						),
						array(
							'key'          => 'google_ua_is_page_view',
							'type'         => 'checkbox',
							'label'        => __( 'Enable PageView Event', 'woofunnels' ),
							'styleClasses' => [ 'wfacp_checkbox_wrap', 'wfacp_setting_track_and_events_end', 'bwf_remove_lft_pad' ],
						),
						array(
							'type'  => 'checkbox',
							'key'   => 'google_ua_add_to_cart_event',
							'label' => __( 'Enable AddtoCart Event', 'woofunnels' ),
						),
						array(
							'type'  => 'checkbox',
							'key'   => 'google_ua_initiate_checkout_event',
							'label' => __( 'Enable BeginCheckout Event', 'woofunnels' ),
						),
						array(
							'type'  => 'checkbox',
							'key'   => 'google_ua_add_payment_info_event',
							'label' => __( 'Enable AddPaymentInfo Event', 'woofunnels' ),
						),
						array(
							'type'        => 'checkbox',
							'key'         => 'google_ua_exclude_tax',
							'label'       => __( 'Exclude Taxes', 'woofunnels' ),
							'placeholder' => __( 'Exclude Tax in Total(Item Subtotal,Order Total)', 'woofunnels' ),
							'hint'        => __( 'Exclude tax in Total', 'woofunnels' ),

						),
						array(
							'type'  => 'checkbox',
							'key'   => 'google_ua_variable_as_simple',
							'label' => __( 'Treat variable products like simple products', 'woofunnels' ),
							'hint'  => __( 'Turn this option ON when your Product Catalog doesn\'t include the variants for variable products.', 'woofunnels' ),
						),
						array(
							'key'           => 'google_ua_content_id_type',
							'type'          => 'select',
							'label'         => '',
							'hint'          => __( 'Select either Product ID or SKU to pass value in content_id parameter', 'woofunnels' ),
							'values'        => [
								[ 'id' => '0', 'name' => __( 'Select content id parameter', 'woofunnels' ) ],
								[ 'id' => 'product_id', 'name' => __( 'Product ID', 'woofunnels' ) ],
								[ 'id' => 'product_sku', 'name' => __( 'Product Sku', 'woofunnels' ) ],
							],
							'selectOptions' => [
								'hideNoneSelectedText' => true,
							],
						),
						array(
							'key'         => 'google_ua_content_id_prefix',
							'type'        => 'input',
							'label'       => '',
							'placeholder' => __( 'content id prefix', 'woofunnels' ),
							'hint'        => __( 'Add prefix to the content_id parameter (optional)', 'woofunnels' ),

						),
						array(
							'key'         => 'google_ua_content_id_suffix',
							'type'        => 'input',
							'label'       => '',
							'placeholder' => __( 'content id suffix', 'woofunnels' ),
							'hint'        => __( 'Add suffix to the content_id parameter (optional)', 'woofunnels' ),

						),
						array(
							'key'    => 'is_ga_purchase_page_view',
							'type'   => 'checklist',
							'label'  => __( 'Purchase Events', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( "Enable PageView Event", 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						array(
							'key'    => 'is_ga_purchase_event',
							'type'   => 'checklist',
							'label'  => '',
							'values' => array(
								array(
									'name'  => __( 'Enable Purchase Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						array(
							'key'     => 'ga_track_traffic_source',
							'type'    => 'checklist',
							'label'   => '',
							'hint'    => __( 'Add traffic source as traffic_source and URL parameters (UTM) as parameters to all your events.', 'woofunnels' ),
							'values'  => array(
								array(
									'name'  => __( 'Track Traffic Source & UTMs', 'woofunnels' ),
									'value' => 'yes',
								),
							),
							'toggler' => array(
								'key'   => 'is_ga_purchase_event',
								'value' => array( 'yes' ),
							),
						),

					),
					'priority' => 15,
				),
				'google_ads'       => array(

					'title'    => __( 'Google Ads', 'woofunnels' ),
					'heading'  => __( 'Google Ads', 'woofunnels' ),
					'slug'     => 'google_ads',
					'fields'   => array(
						array(
							'key'   => 'gad_key',
							'type'  => 'input',
							'label' => __( 'Conversion ID', 'woofunnels' ),
							'hint'  => __( 'Log into your Google Ads account to find your Conversion ID. <a target="_blank" href="https://buildwoofunnels.com/docs/upstroke/global-settings/tracking-analytics/#google-ads-tracking">Click here for more info.</a>', 'woofunnels' ),
						),

						array(
							'key'   => 'gad_conversion_label',
							'type'  => 'input',
							'label' => __( 'Conversion Label', 'woofunnels' ),
							'hint'  => __( 'Log into your Google Ads account to find your conversion label. <a target="_blank" href="https://buildwoofunnels.com/docs/upstroke/global-settings/tracking-analytics/#google-ads-tracking">Click here for more info.</a>', 'woofunnels' ),

						),
						array(
							'key'    => 'is_gad_purchase_event',
							'type'   => 'checklist',
							'label'  => __( 'Purchase Events', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable Conversion Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
						array(
							'key'   => 'gad_exclude_from_total',
							'type'  => 'checklist',
							'label' => '',
							'hint'  => __( 'Check above boxes to exclude shipping/taxes from the total.', 'woofunnels' ),

							'values' => array(
								array(
									'name'  => __( 'Exclude Shipping from Total', 'woofunnels' ),
									'value' => 'is_disable_shipping',
								),
								array(
									'name'  => __( 'Exclude Taxes from Total', 'woofunnels' ),
									'value' => 'is_disable_taxes',
								),

							),

							'toggler' => array(
								'key'   => 'is_gad_purchase_event',
								'value' => array( 'yes' ),
							),
						),
						array(
							'key'         => 'id_prefix_gad',
							'type'        => 'input',
							'label'       => '',
							'placeholder' => __( 'Product ID prefix', 'woofunnels' ),
							'hint'        => __( 'Add prefix to the product_id parameter (optional)', 'woofunnels' ),
							'toggler'     => array(
								'key'   => 'is_gad_purchase_event',
								'value' => array( 'yes' ),
							),

						),
						array(
							'key'         => 'id_suffix_gad',
							'type'        => 'input',
							'label'       => '',
							'placeholder' => __( 'Product ID suffix', 'woofunnels' ),
							'hint'        => __( 'Add suffix to the product_id parameter (optional)', 'woofunnels' ),
							'toggler'     => array(
								'key'   => 'is_gad_purchase_event',
								'value' => array( 'yes' ),
							),
						),
					),
					'priority' => 20,
				),
				'pinterest'        => array(
					'title'    => __( 'Pinterest', 'woofunnels' ),
					'heading'  => __( 'Pinterest', 'woofunnels' ),
					'slug'     => 'pinterest',
					'fields'   => array(
						array(
							'type'  => 'input',
							'key'   => 'pint_key',
							'label' => __( 'Tag ID', 'woofunnels' ),
						),

						array(
							'key'    => 'is_pint_purchase_event',
							'type'   => 'checklist',
							'label'  => __( 'Purchase Events', 'woofunnels' ),
							'values' => array(
								array(
									'name'  => __( 'Enable Purchase Event', 'woofunnels' ),
									'value' => 'yes',
								),
							),
						),
					),
					'priority' => 25,
				)

			) );
			$legacy         = apply_filters( 'bwf_general_settings_fields', [] );
			$legacy_altered = [];
			if ( count( $legacy ) > 0 ) {
				$i = 0;
				foreach ( $legacy as $key => $new ) {
					$legacy_altered[ $i ] = array( 'key' => $key );
					$legacy_altered[ $i ] = array_merge( $legacy_altered[ $i ], $new );
					$i ++;
				}
			}
			$static_config['permalinks']['fields'] = $legacy_altered;

			foreach ( $static_config as &$arr ) {
				$values = [];
				foreach ( $arr['fields'] as &$field ) {

					$values[ $field['key'] ] = $this->get_option( $field['key'] );
				}
				$arr['values'] = $values;
			}

			return $static_config;

		}
	}


}
BWF_Admin_General_Settings::get_instance();
