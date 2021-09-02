<?php

/**
 * Class BWFAN_Common
 * Handles Common Functions For Admin as well as front end interface
 */
class BWFAN_Common {

	public static $http;

	public static $integrations_saved_data = array();
	public static $date_format = null;
	public static $time_format = null;
	public static $admin_email = null;
	public static $select2ajax_functions = null;
	public static $events_async_data = null;
	public static $time_periods = null;
	public static $taxonomy_post_type = array();
	public static $offer_product_types = array(
		'simple',
		'variable',
		'variation',
		'subscription',
		'variable-subscription',
		'subscription_variation',
	);

	public static $exec_task_id = null;
	public static $general_options = null;

	public static function init() {
		self::$date_format           = get_option( 'date_format' );
		self::$time_format           = get_option( 'time_format' );
		self::$admin_email           = get_option( 'admin_email' );
		self::$select2ajax_functions = array( 'get_subscription_product', 'get_membership_plans', 'get_coupon' );
		self::$general_options       = self::get_global_settings();

		register_deactivation_hook( BWFAN_PLUGIN_FILE, array( __CLASS__, 'deactivation' ) );

		/** Loading WooFunnels core */
		add_action( 'plugins_loaded', function () {
			WooFunnel_Loader::include_core();
		}, - 99 );

		add_filter( 'modify_set_data', array( __CLASS__, 'parse_default_merge_tags' ), 10, 1 );
		add_action( 'bwfan_delete_order_meta_payment_failed', array( __CLASS__, 'delete_order_meta' ), 10, 1 );
		add_filter( 'bwfan_select2_ajax_callable', array( __CLASS__, 'get_callable_object' ), 1, 2 );

		add_action( 'admin_notices', array( __CLASS__, 'bwfan_run_cron_test' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'add_plugin_endpoint' ) );

		/** showing consent text on checkout page */
		self::display_marketing_optin_checkbox();

		add_filter( 'action_scheduler_queue_runner_batch_size', array( __CLASS__, 'ac_increase_queue_batch_size' ) );
		add_filter( 'action_scheduler_queue_runner_time_limit', array( __CLASS__, 'ac_increase_max_execution_time' ) );
		add_filter( 'cron_schedules', array( __CLASS__, 'make_custom_events_time' ) );

		/** Action Scheduler custom table worker callback */
		add_action( 'bwfan_run_queue', array( __CLASS__, 'run_as_ct_worker' ) );
		add_action( 'action_scheduler_pre_init', array( __CLASS__, 'as_pre_init_cb' ) );
		add_action( 'action_scheduler_pre_init', array( __CLASS__, 'as_pre_init_cli_cb' ) );

		/** Enable WooFunnels Action Scheduler Data Store */
		add_filter( 'enable_woofunnels_as_ds', '__return_true' );

		// Convert all active abandoned rows to abandoned
		add_action( 'bwfan_check_abandoned_carts', array( __CLASS__, 'check_for_abandoned_carts' ) );

		// Delete all the old abandoned cart rows and their queued tasks
		add_action( 'bwfan_delete_old_abandoned_carts', array( __CLASS__, 'delete_old_abandoned_carts' ) );
		add_action( 'bwfan_mark_abandoned_lost_cart', array( __CLASS__, 'mark_abandoned_lost_cart' ) );

		add_action( 'bwfan_delete_expired_autonami_coupons', array( __CLASS__, 'delete_expired_autonami_coupons' ) );

		add_action( 'bwfan_get_sources_events', array( __CLASS__, 'merge_pro_events' ) );

		add_action( 'woofunnels_woocommerce_thankyou', array( __CLASS__, 'hit_cron_to_run_tasks' ) );

		/** Auto deploy coupon in the cart */
		add_action( 'wp', array( __CLASS__, 'auto_apply_wc_coupon' ), 20 );
		/** Handling when restoring abandoned cart */
		add_action( 'bwfan_abandoned_cart_restored', array( __CLASS__, 'auto_apply_wc_coupon' ) );

		/** update order meta marketing_status details */
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'bwfan_update_order_user_consent' ), 10, 2 );
		add_action( 'bwf_normalize_contact_meta_before_save', array( __CLASS__, 'save_marketing_status_for_user' ), 20, 2 );

		/**
		 * Hooked over action_scheduler_pre_init
		 * Initiating core action scheduler
		 */
		add_action( 'bwf_after_action_scheduler_load', array( __CLASS__, 'bwf_after_action_scheduler_load' ), 11 );

		/**
		 * removing abandoned cart tags on cart restoration
		 */
		add_action( 'abandoned_cart_recovered', array( __CLASS__, 'bwfan_remove_abandoned_cart_tags' ), 10, 3 );
	}

	public static function display_marketing_optin_checkbox() {
		/** showing consent text on checkout page */

		if ( isset( self::$general_options['bwfan_user_consent_position'] ) && 'below_term' === self::$general_options['bwfan_user_consent_position'] ) {
			// add_action( 'woocommerce_checkout_order_review', [ __CLASS__, 'add_user_consent_after_terms_and_conditions' ], 25 );
			add_action( 'woocommerce_before_template_part', function ( $template_name, $template_path ) {
				if ( 'checkout/terms.php' === $template_name ) {
					self::add_user_consent_after_terms_and_conditions();
				}
			}, 99, 2 );
		} elseif ( isset( self::$general_options['bwfan_user_consent_position'] ) && 'below_phone' === self::$general_options['bwfan_user_consent_position'] ) {
			/** Below Phone */
			add_filter( 'woocommerce_form_field', function ( $field, $key, $args, $value ) {
				if ( 'billing_phone' === $key ) {
					$field_priority = $args['priority'] ? $args['priority'] : '';
					$field          .= self::add_user_consent_after_terms_and_conditions( true, $field_priority );
				}

				return $field;
			}, 99, 4 );
		} else {
			/** Below Email */
			add_filter( 'woocommerce_form_field', function ( $field, $key, $args, $value ) {
				if ( 'billing_email' === $key ) {
					$field_priority = $args['priority'] ? $args['priority'] : '';
					$field          .= self::add_user_consent_after_terms_and_conditions( true, $field_priority );
				}

				return $field;
			}, 99, 4 );
		}
	}

	/**
	 * Restrict product link display for product layouts in emails
	 *
	 * @return mixed|void
	 */
	public static function disable_product_link() {
		return apply_filters( 'bwfan_disable_product_link', false );
	}

	/**
	 * Restrict thumbnails display for product layouts in emails
	 *
	 * @return mixed|void
	 */
	public static function disable_product_thumbnail() {
		return apply_filters( 'bwfan_disable_product_thumbnail', false );
	}

	/**
	 * @param DateTime $datetime
	 */
	public static function convert_to_gmt( $datetime ) {
		$datetime->modify( '-' . self::get_timezone_offset() * HOUR_IN_SECONDS . ' seconds' );
	}

	public static function get_timezone_offset() {
		$timezone = get_option( 'timezone_string' );
		if ( $timezone ) {
			$timezone_object = new DateTimeZone( $timezone );

			return $timezone_object->getOffset( new DateTime( 'now' ) ) / HOUR_IN_SECONDS;
		} else {
			return floatval( get_option( 'gmt_offset', 0 ) );
		}
	}

	public static function convert_to_site_time( $date ) {
		return self::convert_from_gmt( $date );
	}

	/**
	 * @param $datetime DateTime
	 *
	 * @return mixed
	 */
	public static function convert_from_gmt( $datetime ) {
		return $datetime->modify( '+' . self::get_timezone_offset() * HOUR_IN_SECONDS . ' seconds' );
	}

	public static function is_load_admin_assets( $screen_type = 'single' ) {
		$page = filter_input( INPUT_GET, 'page' );
		if ( empty( $page ) ) {
			return false;
		}
		if ( 'all' === $screen_type ) {
			$is_autonami = ( false !== strpos( $page, 'autonami' ) );
			if ( $page === 'autonami' || $is_autonami ) {
				return true;
			}
		} elseif ( 'builder' === $screen_type ) {
			if ( $page === 'autonami-automations' && filter_input( INPUT_GET, 'edit' ) > 0 ) {
				return true;
			}
		} elseif ( 'all' === $screen_type || 'builder' === $screen_type ) {
			if ( $page === 'autonami-automations' && filter_input( INPUT_GET, 'edit' ) > 0 ) {
				return true;
			}
		} elseif ( 'all' === $screen_type || 'settings' === $screen_type ) {
			if ( $page === 'autonami-settings' || false !== strpos( filter_input( INPUT_GET, 'path' ), 'settings' ) ) {
				return true;
			}
		} elseif ( 'automation' === $screen_type ) {
			if ( $page === 'autonami-automations' && filter_input( INPUT_GET, 'edit' ) > 0 ) {
				return true;
			}
		} elseif ( 'recipe' === $screen_type ) {
			if ( $page === 'autonami-automations' && filter_input( INPUT_GET, 'tab' ) === 'recipe' ) {
				return true;
			}
		}
		$screen = get_current_screen();

		return apply_filters( 'bwfan_enqueue_scripts', false, $screen_type, $screen );
	}

	public static function array_flatten( $array ) {
		if ( ! is_array( $array ) ) {
			return false;
		}
		$result = iterator_to_array( new RecursiveIteratorIterator( new RecursiveArrayIterator( $array ) ), false );

		return $result;
	}

	public static function pr( $arr ) {
		echo '<pre>';
		print_r( $arr ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions
		echo '</pre>';
	}

	public static function pc( $val1, $val2 = '' ) {
		if ( ! class_exists( 'pc' ) ) {
			return;
		}
		pc::debug( $val1, $val2 ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}

	/**
	 * Slug-ify the class name and remove underscores and convert it to filename
	 * Helper function for the auto-loading
	 *
	 * @param $class_name
	 *
	 * @return mixed|string
	 * @see BWFAN_Gateways::integration_autoload();
	 */
	public static function slugify_classname( $class_name ) {
		$classname = sanitize_title( $class_name );
		$classname = str_replace( '_', '-', $classname );

		return $classname;
	}

	public static function maybe_convert_html_tag( $val ) {
		if ( false === is_string( $val ) ) {
			return $val;
		}
		$val = str_replace( '&lt;', '<', $val );
		$val = str_replace( '&gt;', '>', $val );

		return $val;
	}

	public static function string2hex( $string ) {
		$hex = '';
		for ( $i = 0; $i < strlen( $string ); $i ++ ) {
			$hex .= dechex( ord( $string[ $i ] ) );
		}

		return $hex;
	}

	public static function get_date_format() {
		return get_option( 'date_format', '' ) . ' ' . get_option( 'time_format', '' );
	}

	/**
	 * Return sidebar options on single automation screen.
	 *
	 * @return mixed|void
	 */
	public static function get_sidebar_menu() {
		$sidebar_menu = array(

			'20' => array(
				'icon' => 'dashicons dashicons-networking',
				'name' => __( 'Automation', 'wp-marketing-automations' ),
				'key'  => 'automation',
			),
			'50' => array(
				'icon' => 'dashicons dashicons-admin-tools',
				'name' => __( 'Tools', 'wp-marketing-automations' ),
				'key'  => 'tools',
			),
		);

		return apply_filters( 'bwfan_builder_menu', $sidebar_menu );
	}

	/**
	 * Checks if the current page is autonami page or not.
	 *
	 * @return bool
	 */
	public static function is_autonami_page() {
		if ( isset( $_GET['page'] ) && 'autonami-automations' === sanitize_text_field( $_GET['page'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			return true;
		}

		return false;
	}

	/**
	 * Remove autonami events on plugin deactivation.
	 */
	public static function deactivation() {
		if ( bwf_has_action_scheduled( 'bwfan_run_queue' ) ) {
			bwf_unschedule_actions( 'bwfan_run_queue' );
		}
		if ( bwf_has_action_scheduled( 'bwfan_check_abandoned_carts' ) ) {
			bwf_unschedule_actions( 'bwfan_check_abandoned_carts' );
		}
		if ( bwf_has_action_scheduled( 'bwfan_delete_expired_autonami_coupons' ) ) {
			bwf_unschedule_actions( 'bwfan_delete_expired_autonami_coupons' );
		}
		if ( bwf_has_action_scheduled( 'bwfan_mark_abandoned_lost_cart' ) ) {
			bwf_unschedule_actions( 'bwfan_mark_abandoned_lost_cart' );
		}
		if ( bwf_has_action_scheduled( 'bwfan_delete_old_abandoned_carts' ) ) {
			bwf_unschedule_actions( 'bwfan_delete_old_abandoned_carts' );
		}
	}

	/**
	 * Send a remote call.
	 *
	 * @param $api_url
	 * @param $data
	 * @param string $method_type
	 *
	 * @return array|mixed|object|string|null
	 */
	public static function send_remote_call( $api_url, $data, $method_type = 'post' ) {
		if ( 'get' === $method_type ) {
			$httpPostRequest = self::http()->get( $api_url, array(
				'body'      => $data,
				'sslverify' => false,
				'timeout'   => 30,
			) );
		} else {
			$httpPostRequest = self::http()->post( $api_url, array(
				'body'      => $data,
				'sslverify' => false,
				'timeout'   => 30,
			) );
		}

		if ( isset( $httpPostRequest->errors ) ) {
			$response = null;
		} elseif ( isset( $httpPostRequest['body'] ) && '' !== $httpPostRequest['body'] ) {
			$body     = $httpPostRequest['body'];
			$response = json_decode( $body, true );
		} else {
			$response = 'No result';
		}

		return $response;
	}

	public static function http() {
		if ( null === self::$http ) {
			self::$http = new WP_Http();
		}

		return self::$http;
	}

	/**
	 * Return all the merge tags from a string.
	 *
	 * @param $text
	 *
	 * @return array|null
	 */
	public static function get_merge_tags_from_text( $text ) {
		$merge_tags      = null;
		$more_merge_tags = null;
		if ( ! is_array( $text ) ) {
			preg_match_all( '/\{{(.*?)\}}/', $text, $more_merge_tags );
		}

		if ( is_array( $more_merge_tags[1] ) && count( $more_merge_tags[1] ) > 0 ) {
			$merge_tags = $more_merge_tags[1];
		}

		return $merge_tags;
	}

	/**
	 * Return the merge tags which will behave as array.
	 *
	 * @param $merge_tags
	 * @param $integration_merge_tags
	 * @param $action_data
	 *
	 * @return array
	 */
	public static function initial_parse_merge_tags( $merge_tags, $integration_merge_tags, $action_data ) {
		$dynamic_array = array();
		if ( ! is_array( $action_data ) || count( $action_data ) === 0 ) {
			return $dynamic_array;
		}
		foreach ( $action_data as $key1 => $value1 ) {
			if ( ! is_array( $value1 ) || count( $value1 ) === 0 ) {
				$dynamic_array[ $key1 ] = $value1;
				continue;
			}

			foreach ( $value1 as $key2 => $value2 ) {
				if ( ! in_array( $key2, $merge_tags, true ) || ( ! is_array( $integration_merge_tags ) || ! in_array( $key2, $integration_merge_tags, true ) ) ) {
					$dynamic_array[ $key2 ] = $value2;
					continue;
				}

				if ( isset( $dynamic_array[ $key2 ] ) ) {
					array_push( $dynamic_array[ $key2 ], $value2 );
				} else {
					$dynamic_array[ $key2 ] = array( $value2 );
				}
			}
		}

		return $dynamic_array;
	}

	public static function filter_tasks( $all_tasks, $all_tasks_meta ) {
		$result = array();
		foreach ( $all_tasks_meta as $value1 ) {
			if ( isset( $value1['bwfan_task_id'] ) ) {
				$id = $value1['bwfan_task_id'];
			} elseif ( isset( $value1['bwfan_log_id'] ) ) {
				$id = $value1['bwfan_log_id'];
			}
			if ( isset( $all_tasks[ $id ] ) ) {
				$result['all_tasks'][ $id ] = array(
					$all_tasks[ $id ]['integration_slug'] => $all_tasks[ $id ]['integration_action'],
				);
				if ( 'integration_data' === $value1['meta_key'] ) {
					$meta                            = maybe_unserialize( $value1['meta_value'] );
					$meta['automation_id']           = $all_tasks[ $id ]['automation_id'];
					$result['all_tasks_meta'][ $id ] = $meta;
				}
				$result['all_tasks_status'][ $id ]        = $all_tasks[ $id ]['status'];
				$result['all_tasks_automation_id'][ $id ] = $all_tasks[ $id ]['automation_id'];
				$result['all_tasks_attempts'][ $id ]      = $all_tasks[ $id ]['attempts'];
			}
		}

		return $result;
	}

	/**
	 * Remove backslashes from $_POST content of the automation.
	 *
	 * @param $posted_data
	 *
	 * @return array
	 */
	public static function remove_back_slash_from_automation( $posted_data ) {
		if ( ! is_array( $posted_data ) || count( $posted_data ) === 0 ) {
			return $posted_data;
		}
		foreach ( $posted_data as $key1 => $value1 ) {
			if ( isset( $value1['ajax_data'] ) ) {
				$posted_data[ $key1 ]['ajax_data'] = self::remove_backslashes( $value1['ajax_data'] );
			}
			if ( isset( $value1['data']['field_value'] ) && ! is_array( $value1['data']['field_value'] ) ) {
				$posted_data[ $key1 ]['data']['field_value'] = self::remove_newlines( self::remove_backslashes( $value1['data']['field_value'] ) );
			}
		}

		return $posted_data;
	}

	public static function remove_backslashes( $string ) {
		return preg_replace( '/\\\\/', '', $string );
	}

	public static function remove_newlines( $string ) {
		return trim( preg_replace( '/\s+/', ' ', $string ) );
	}

	/**
	 * Get all the merge tags of all the events.
	 *
	 * @param $all_sources
	 *
	 * @return array
	 */
	public static function get_all_events_merge_tags() {
		$all_events_merge_tags = array();
		$merge_tags            = BWFAN_Core()->merge_tags->get_localize_tags_with_source();
		$source_events         = BWFAN_Core()->sources->get_events();

		/**
		 * @var $event_object BWFAN_Event
		 */
		foreach ( $source_events as $event_key => $event_object ) {
			$event_merge_tags = $event_object->get_merge_tag_groups();
			if ( empty( $event_merge_tags ) ) {
				continue;
			}

			$curr_event_merge_tags = array();
			foreach ( $event_merge_tags as $head ) {
				if ( ! isset( $merge_tags[ $head ] ) ) {
					continue;
				}
				$curr_event_merge_tags[ $head ] = $merge_tags[ $head ];
			}
			$all_events_merge_tags[ $event_key ] = apply_filters( 'bwfan_default_merge_tags', $curr_event_merge_tags );
		}

		return $all_events_merge_tags;
	}

	/**
	 * @param $all_sources
	 *
	 * @return array
	 */
	public static function get_all_events_rules() {
		$all_rules_groups = array();
		$events           = BWFAN_Core()->sources->get_events();
		if ( empty( $events ) ) {
			return $all_rules_groups;
		}
		/**
		 * @var $event BWFAN_Event
		 */
		foreach ( $events as $slug => $event ) {
			$all_rules_groups[ $slug ] = array_merge( $event->get_rule_group(), BWFAN_Core()->rules->get_default_rule_groups() );
		}

		return $all_rules_groups;
	}

	public static function sort_automations( $all_automations ) {
		$all_automations_temp = array();
		$nice_names           = array();

		if ( is_array( $all_automations ) && count( $all_automations ) > 0 ) {
			foreach ( $all_automations as $int_slug => $int_obj ) {
				$nice_names[] = $int_obj->get_name();
			}
			asort( $nice_names );

			foreach ( $nice_names as $int_nice_name ) {
				foreach ( $all_automations as $int_slug => $int_obj ) {
					if ( $int_nice_name === $int_obj->get_name() ) {
						$all_automations_temp[ $int_slug ] = $int_obj;
					}
				}
			}

			$all_automations = $all_automations_temp;
		}

		return $all_automations;
	}

	/**
	 * Save integration data for a connector.
	 *
	 * @param $data
	 * @param $slug
	 * @param $status
	 *
	 * @return int
	 */
	public static function save_integration_data( $data, $slug, $status ) {
		$new_task_data                     = array();
		$new_task_data['last_sync']        = current_time( 'timestamp', 1 );
		$new_task_data['integration_slug'] = $slug;
		$new_task_data['api_data']         = maybe_serialize( $data );
		$new_task_data['status']           = $status;
		BWFAN_Model_Settings::insert( $new_task_data );

		return BWFAN_Model_Settings::insert_id();
	}

	/**
	 * Update integration data for a connector.
	 *
	 * @param $data
	 * @param $id
	 */
	public static function update_integration_data( $data, $id ) {
		$meta_data              = array();
		$meta_data['api_data']  = maybe_serialize( $data );
		$meta_data['last_sync'] = current_time( 'timestamp', 1 );
		$where                  = array(
			'ID' => $id,
		);
		BWFAN_Model_Settings::update( $meta_data, $where );
	}

	public static function get_parsed_time( $wp_date_format, $logs ) {
		$logs_temp = array();

		$logs_temp[ date( 'Y-m-d H:i:s' ) ] = __( 'Error in generating logs', 'wp-marketing-automations' );
		if ( ! is_array( $logs ) || count( $logs ) === 0 ) {
			return array_reverse( $logs_temp, true );
		}

		$logs_temp = array();
		foreach ( $logs as $timestamp => $message ) {
			$time = get_date_from_gmt( date( 'Y-m-d H:i:s', $timestamp ), $wp_date_format );
			if ( empty( $message ) ) {
				$message = __( 'No response from API', 'wp-marketing-automations' );
			} else {
				$message = str_replace( "'", '', $message );
			}
			$logs_temp[ $time ] = $message;
		}

		return array_reverse( $logs_temp, true );
	}

	public static function string( $string ) {
		return sanitize_text_field( $string );
	}

	public static function add_default_merge_tags( $event_merge_tags ) {
		$default_merge_tags = self::get_default_merge_tags( false );
		foreach ( $default_merge_tags as $merge_tag => $details ) {
			$event_merge_tags[ $merge_tag ] = $details[0];
		}

		return $event_merge_tags;
	}

	public static function get_default_merge_tags( $load_values ) {
		$current_date      = null;
		$current_time      = null;
		$current_date_time = null;

		if ( $load_values ) {
			$cdt               = self::$date_format . ' ' . self::$time_format;
			$ct                = self::$time_format;
			$cd                = self::$date_format;
			$current_date_time = get_date_from_gmt( date( 'Y-m-d H:i:s' ), $cdt );
			$current_time      = get_date_from_gmt( date( 'H:i:s' ), $ct );
			$current_date      = get_date_from_gmt( date( 'Y-m-d' ), $cd );
		}
		$default_merge_tags = array(
			'admin_email'       => array( __( 'Admin email', 'wp-marketing-automations' ), self::$admin_email ),
			'current_date'      => array( __( 'Current date when task will be executed', 'wp-marketing-automations' ), $current_date ),
			'current_time'      => array( __( 'Current time when task will be executed', 'wp-marketing-automations' ), $current_time ),
			'current_date_time' => array( __( 'Current date and time when task will be executed', 'wp-marketing-automations' ), $current_date_time ),
		);

		return apply_filters( 'bwfan_modify_default_merge_tags', $default_merge_tags );
	}

	public static function parse_default_merge_tags( $data, $recursive = false ) {
		if ( ! is_array( $data ) || count( $data ) === 0 ) {
			return $data;
		}

		$default_merge_tags_values = self::get_default_merge_tags( true );

		/**         *
		 * This function only decode two level array
		 */
		foreach ( $default_merge_tags_values as $merge_tag => $details ) {
			foreach ( $data as $key1 => $value1 ) {
				if ( in_array( gettype( $value1 ), array( 'int', 'boolean' ), true ) ) {
					continue;
				}

				if ( is_array( $value1 ) ) {
					if ( empty( $value1 ) ) {
						continue;
					}
					foreach ( $value1 as $key2 => $value2 ) {
						if ( is_array( $value2 ) ) {
							$data[ $key1 ][ $key2 ] = $value2;
						} else {
							if ( false !== strpos( $value2, '{{' . $merge_tag . '}}' ) ) {
								$data[ $key1 ][ $key2 ] = str_replace( '{{' . $merge_tag . '}}', $details[1], $value2 );
							}
						}
					}
				} else {
					if ( false !== strpos( $value1, '{{' . $merge_tag . '}}' ) ) {
						$data[ $key1 ] = str_replace( '{{' . $merge_tag . '}}', $details[1], $value1 );
						continue;
					}
				}
			}
		}

		return $data;
	}

	public static function filter_actions_conditions( $selected_actions, $automation_details ) {
		$automation_actions    = $automation_details['actions'];
		$automation_conditions = ( isset( $automation_details['condition'] ) ) ? $automation_details['condition'] : array();
		$temp_actions          = array();
		$temp_conditions       = array();

		foreach ( $selected_actions as $single_indexes ) {
			$single_ind                             = explode( '_', $single_indexes );
			$group_id                               = $single_ind[0];
			$child_id                               = $single_ind[1];
			$temp_actions[ $group_id ][ $child_id ] = $automation_actions[ $group_id ][ $child_id ];
		}

		if ( is_array( $automation_conditions ) && count( $automation_conditions ) > 0 ) {
			foreach ( $temp_actions as $group_id => $actions ) {
				/** Checking if group id not present in the condition */
				if ( ! isset( $automation_conditions[ $group_id ] ) ) {
					continue;
				}
				/** Checking if actions empty */
				if ( ! is_array( $actions ) || 0 === count( $actions ) ) {
					continue;
				}
				/** Checking if action id  condition not exist */
				foreach ( $actions as $action_id => $action_data ) {
					if ( ! isset( $automation_conditions[ $group_id ][ $action_id ] ) ) {
						continue;
					}
					if ( ! is_array( $action_data ) ) {
						continue;
					}
					$temp_conditions[ $group_id ][ $action_id ] = $action_data;
				}
			}
		}

		$automation_details['actions']   = $temp_actions;
		$automation_details['condition'] = $temp_conditions;

		return $automation_details;
	}

	/**
	 *
	 * Check for the user order count , if not found in the usermeta then fires wpdb query
	 *
	 * @param int $user_id
	 *
	 * @return int
	 * @since 2.7.1
	 */
	public static function get_customer_order_count( $user_id, $force = false ) {
		global $wpdb;

		$order_count      = implode( "','", wc_get_order_types( 'order-count' ) );
		$trashed_statuses = implode( "','", self::get_order_trashed_statuses() );
		$query            = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts as posts LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id WHERE   meta.meta_key= '_customer_user' AND posts.post_type IN (%s) AND posts.post_status NOT IN (%s) AND meta_value= %d", $order_count, $trashed_statuses, $user_id );

		$count = $wpdb->get_var( $query );//phpcs:ignore WordPress.DB.PreparedSQL

		if ( '' !== $wpdb->last_error ) {
			return 0;
		}

		return absint( $count );
	}

	public static function get_order_trashed_statuses() {
		return apply_filters( 'bwfan_get_order_trashed_statuses', array( 'wc-cancelled', 'wc-refunded', 'wc-failed', 'trash', 'draft' ) );
	}

	public static function get_funnel_data( $funnel_id ) {
		$data                = array();
		$data['funnel_id']   = $funnel_id;
		$funnel_details      = get_post( $funnel_id );
		$data['funnel_name'] = $funnel_details->post_title;

		return $data;
	}

	public static function get_offer_data( $offer_id ) {
		$data               = array();
		$data['offer_id']   = $offer_id;
		$offer_details      = get_post( $offer_id );
		$data['offer_name'] = $offer_details->post_title;
		$data['offer_type'] = get_post_meta( $offer_id, '_offer_type', true );

		return $data;
	}

	/**
	 * Return subscription products by searched term.
	 *
	 * @param $searched_term
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function get_subscription_product( $searched_term ) {
		$subscription_products = array();
		$results               = array();
		$term                  = $searched_term;
		$include_variations    = true;
		$data_store            = WC_Data_Store::load( 'product' );
		$ids                   = $data_store->search_products( $term, '', (bool) $include_variations );
		$product_objects       = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_readable' );

		foreach ( $product_objects as $product_object ) {
			if ( WC_Subscriptions_Product::is_subscription( $product_object ) ) {
				$results[] = array(
					'id'   => $product_object->get_id(),
					'text' => rawurldecode( $product_object->get_formatted_name() ),
				);
			}
		}
		$subscription_products['results'] = $results;

		return $subscription_products;
	}

	/**
	 * Get membership plans by searched term.
	 *
	 * @param $searched_term
	 *
	 * @return array
	 */
	public static function get_membership_plans( $searched_term ) {
		$membership_plans = array();
		$results          = array();
		$query_params     = array(
			'post_type'      => 'wc_membership_plan',
			'posts_per_page' => - 1,
		);

		if ( '' !== $searched_term ) {
			$query_params['s'] = $searched_term;
		}

		$query = new WP_Query( $query_params );

		if ( $query->found_posts > 0 ) {
			foreach ( $query->posts as $post ) {
				$results[] = array(
					'id'   => $post->ID,
					'text' => $post->post_title,
				);
			}
		}

		$membership_plans['results'] = $results;

		return $membership_plans;
	}

	/**
	 * Get membership names by membership ids.
	 *
	 * @param $membership_plans
	 *
	 * @return array
	 */
	public static function get_membership_pre_data( $membership_plans ) {
		$plans = array();
		if ( is_array( $membership_plans ) && count( $membership_plans ) > 0 ) {
			foreach ( $membership_plans as $id ) {
				$plan_name    = get_the_title( $id );
				$plans[ $id ] = $plan_name;
			}
		}

		return $plans;
	}

	/**
	 * Get subscription names by subscription ids.
	 *
	 * @param $subscription_products
	 *
	 * @return array
	 */
	public static function get_subscription_pre_data( $subscription_products ) {
		$products = array();
		if ( is_array( $subscription_products ) && count( $subscription_products ) > 0 ) {
			foreach ( $subscription_products as $id ) {
				$product         = wc_get_product( $id );
				$product_name    = $product->get_formatted_name();
				$products[ $id ] = $product_name;
			}
		}

		return $products;
	}

	public static function delete_order_meta( $order_id ) {
		delete_post_meta( $order_id, '_bwfan_poid' );
		delete_post_meta( $order_id, '_bwfan_package' );
		delete_post_meta( $order_id, '_bwfan_fun_id' );
	}

	public static function get_sorted_automations( $rows ) {
		$result = array();
		if ( is_array( $rows ) && count( $rows ) > 0 ) {
			foreach ( $rows as $value1 ) {
				$result[ $value1['ID'] ] = $value1;
			}
		}

		return $result;
	}

	public static function get_callable_object( $is_empty, $data ) {
		if ( in_array( 'get_' . $data['type'], self::$select2ajax_functions, true ) ) {
			return array( __CLASS__, 'get_' . $data['type'] );
		} else {
			return $is_empty;
		}
	}

	public static function decode_merge_tags( $string, $is_crm = false ) {
		if ( empty( $string ) ) {
			return '';
		}

		$string = self::strip_merge_tags( $string, $is_crm );
		$string = apply_filters( 'bwfan_pre_decode_merge_tags', $string );

		$string = str_replace( '[if', '[bwfno_if', $string );
		$string = str_replace( '[endif', '[bwfno_endif', $string );

		do_action( 'bwfan_before_decode_merge_tags', $string );

		$string = BWFAN_Merge_Tag::maybe_parse_nested_merge_tags( $string );

		do_action( 'bwfan_after_decode_merge_tags', $string );

		$string = str_replace( '[bwfno_if', '[if', $string );
		$string = str_replace( '[bwfno_endif', '[endif', $string );

		$string = apply_filters( 'bwfan_post_decode_merge_tags', $string );

		return $string;
	}

	public static function strip_merge_tags( $string, $is_crm = false ) {
		/** Don't strip from the style tag */
		$elements = explode( '</style>', $string );

		$shortcode_head = true === $is_crm ? '[bwfan_crm_' : '[bwfan_';

		$stripped_merge_tags = array();
		foreach ( $elements as $element ) {
			$strings               = explode( '<style', $element );
			$strings[0]            = str_replace( '{{', $shortcode_head, $strings[0] );
			$strings[0]            = str_replace( '}}', ']', $strings[0] );
			$stripped_merge_tags[] = implode( '<style', $strings );
		}

		return implode( '</style>', $stripped_merge_tags );
	}

	public static function get_product_image( $product, $size = 'shop_catalog', $only_url = false, $img_width = '' ) {
		$image_id = $product->get_image_id();
		if ( $image_id ) {
			$image_url = wp_get_attachment_image_url( $image_id, $size );

			if ( $only_url ) {
				$image = $image_url;
			} else {
				$style = ! empty( $img_width ) ? "width='{$img_width}'" : '';
				$image = '<img src="' . $image_url . '" ' . $style . ' class="bwfan-product-image" alt="' . sanitize_text_field( self::get_name( $product ) ) . '">';
			}
		} else {
			$image = wc_placeholder_img( $size );
		}

		return $image;
	}

	/**
	 * @param $product WC_Product
	 *
	 * @return mixed
	 */
	public static function get_name( $product ) {
		return $product->get_name();
	}

	/**
	 * @param $order WC_Order
	 *
	 * @return array
	 */
	public static function get_order_cross_sells( $order ) {
		$cross_sells = array();
		$in_order    = array();
		$items       = $order->get_items();

		foreach ( $items as $item ) {
			$product     = $item->get_product();
			$in_order[]  = self::is_variation( $product ) ? $product->get_parent_id() : $product->get_id();
			$cross_sells = array_merge( $product->get_cross_sell_ids(), $cross_sells );
		}

		return array_diff( $cross_sells, $in_order );
	}

	public static function is_variation( $product ) {
		return $product->is_type( array( 'variation', 'subscription_variation' ) );
	}

	/**
	 * Set a cookie - wrapper for setcookie using WP constants.
	 *
	 * @param string $name Name of the cookie being set.
	 * @param string $value Value of the cookie.
	 * @param integer $expire Expiry of the cookie.
	 * @param bool $secure Whether the cookie should be served only over https.
	 * @param bool $httponly Whether the cookie is only accessible over HTTP, not scripting languages like JavaScript. @since 3.6.0.
	 */
	public static function set_cookie( $name, $value, $expire = 0, $secure = false, $httponly = false ) {
		if ( self::is_cli() || self::is_cron() || self::is_rest() ) {
			return;
		}
		if ( headers_sent() ) {
			return;
		}
		setcookie( $name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure, apply_filters( 'bwfan_cookie_httponly', $httponly, $name, $value, $expire, $secure ) ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
	}

	/**
	 * Checks whether the current request is a WP cron request
	 *
	 * @return bool
	 */
	public static function is_cron() {
		if ( defined( 'DOING_CRON' ) && true === DOING_CRON ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks whether the current request is a WP rest request
	 *
	 * @return bool
	 */
	public static function is_rest() {
		if ( defined( 'REST_REQUEST' ) && true === REST_REQUEST ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks whether the current request is a WP CLI request
	 *
	 * @return bool
	 */
	public static function is_cli() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return true;
		}

		return false;
	}


	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public static function get_cookie( $name ) {
		return isset( $_COOKIE[ $name ] ) ? sanitize_text_field( $_COOKIE[ $name ] ) : false;
	}

	/**
	 * Clear a cookie.
	 *
	 * @param $name
	 */
	public static function clear_cookie( $name ) {
		if ( isset( $_COOKIE[ $name ] ) ) {
			self::set_cookie( $name, '', time() - HOUR_IN_SECONDS );
		}
	}

	public static function get_line_subtotal( $item ) {
		return isset( $item['line_subtotal'] ) ? floatval( $item['line_subtotal'] ) : 0;
	}

	/**
	 * @return float
	 */
	public static function get_line_subtotal_tax( $item ) {
		return isset( $item['line_subtotal_tax'] ) ? floatval( $item['line_subtotal_tax'] ) : 0;
	}

	public static function get_quantity( $item ) {
		return isset( $item['quantity'] ) ? absint( $item['quantity'] ) : 0;
	}

	public static function price( $price, $currency = '' ) {
		$args = array( 'currency' => $currency );

		return wc_price( $price, $args );
	}

	/**
	 * Get those coupons which are user made only.
	 *
	 * @param $searched_term
	 *
	 * @return array
	 */
	public static function get_coupon( $searched_term ) {
		$membership_plans = array();
		$results          = array();
		$query_params     = array(
			'post_type'      => 'shop_coupon',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => '_is_bwfan_coupon',
					'compare' => 'NOT EXISTS',
				),
			),
		);

		if ( '' !== $searched_term ) {
			$query_params['s'] = $searched_term;
		}

		$query = new WP_Query( $query_params );

		if ( $query->found_posts > 0 ) {
			foreach ( $query->posts as $post ) {
				$results[] = array(
					'id'   => $post->ID,
					'text' => $post->post_title,
				);
			}
		}

		$membership_plans['results'] = $results;

		return $membership_plans;
	}

	public static function validate_action_date_before_save( $all_actions ) {
		if ( ! is_array( $all_actions ) || 0 === count( $all_actions ) ) {
			return false;
		}

		$modified_actions = $all_actions;

		foreach ( $all_actions as $row_index => $row_actions ) {
			if ( null === $row_actions ) {
				continue;
			}
			if ( ! is_array( $row_actions ) || 0 === count( $row_actions ) ) {
				$modified_actions[ $row_index ] = array();
				continue;
			}

			foreach ( $row_actions as $action_index => $action_details ) {
				if ( isset( $action_details['temp_action_slug'] ) && ! empty( $action_details['temp_action_slug'] ) ) {
					$modified_actions[ $row_index ][ $action_index ]['temp_action_slug'] = '';
				}
			}
		}

		return $modified_actions;
	}

	/**
	 * Sort actions when automation is saved.
	 *
	 * @param $all_actions
	 *
	 * @return array
	 */
	public static function sort_actions( $all_actions ) {
		if ( ! is_array( $all_actions ) || 0 === count( $all_actions ) ) {
			return $all_actions;
		}

		foreach ( $all_actions as $row_index => $row_actions ) {
			if ( null === $row_actions && ! is_array( $row_actions ) || 0 === count( $row_actions ) ) {
				unset( $all_actions[ $row_index ] );
				continue;
			}

			foreach ( $row_actions as $action_index => $action_details ) {
				if ( ! is_array( $action_details ) || 0 === count( $action_details ) ) {
					unset( $all_actions[ $row_index ][ $action_index ] );
				}
			}
		}

		return $all_actions;
	}

	/**
	 * Attach default merge tags to every event.
	 *
	 * @param $all_events_merge_tags
	 * @param $all_merge_tags
	 *
	 * @return array
	 */
	public static function attach_default_merge_to_events( $all_events_merge_tags, $all_merge_tags ) {
		if ( ! is_array( $all_events_merge_tags ) || 0 === count( $all_events_merge_tags ) ) {
			return $all_events_merge_tags;
		}

		foreach ( $all_events_merge_tags as $event_slug => $groups ) {
			if ( ! is_array( $groups ) || 0 === count( $groups ) ) {
				continue;
			}

			$all_events_merge_tags[ $event_slug ]['bwfan_default'] = $all_merge_tags['bwfan_default'];
		}

		return $all_events_merge_tags;
	}

	/**
	 * Get wc products by searched term.
	 *
	 * @param bool $term
	 * @param bool $include_variations
	 * @param bool $return
	 *
	 * @return mixed|void
	 */
	public static function product_search( $term = false, $include_variations = false, $return = false ) {
		self::check_nonce();
		if ( empty( $term ) ) {

			if ( isset( $_POST['term'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$term = stripslashes( sanitize_text_field( $_POST['term'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}
		}

		if ( empty( $term ) ) {
			wp_die();
		}

		$variations = true;
		if ( true !== $include_variations ) {
			$variations = false;
		}
		$ids = self::search_products( $term, $variations );

		/**
		 * Products types that are allowed in the offers
		 */
		$product_objects = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_editable' );
		$products        = array();
		foreach ( $product_objects as $product_object ) {
			if ( 'publish' === $product_object->get_status() ) {
				$products[] = array(
					'id'   => $product_object->get_id(),
					'text' => rawurldecode( self::get_formatted_product_name( $product_object ) ),
				);
			}
		}
		$data = apply_filters( 'bwfan_woocommerce_json_search_found_products', $products );
		if ( true === $return ) {
			return $data;
		}

		wp_send_json( $data );
	}

	/**
	 * Check nonce.
	 */
	public static function check_nonce() {
		$nonce     = ( isset( $_REQUEST['_wpnonce'] ) ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification
		$bwf_nonce = ( isset( $_REQUEST['bwf_nonce'] ) ) ? sanitize_text_field( $_REQUEST['bwf_nonce'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification

		if ( wp_verify_nonce( $bwf_nonce, 'bwf_secure_key' ) || wp_verify_nonce( $nonce, 'bwfan-action-admin' ) ) {
			return;
		}
		// This nonce is not valid.
		$resp = array(
			'msg'    => __( 'Invalid request, security validation failed.', 'wp-marketing-automations' ),
			'status' => false,
		);
		wp_send_json( $resp );
	}

	/**
	 * Get wc products by searched term.
	 *
	 * @param $term
	 * @param bool $include_variations
	 *
	 * @return array
	 */
	public static function search_products( $term, $include_variations = false ) {
		self::check_nonce();
		global $wpdb;
		$like_term     = '%' . $wpdb->esc_like( $term ) . '%';
		$post_statuses = current_user_can( 'edit_private_products' ) ? array(
			'private',
			'publish',
		) : array( 'publish' );
		$type_join     = '';
		$type_where    = '';
		$product_ids   = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT posts.ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id {$type_join} WHERE (posts.post_title LIKE %sOR (postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s)) AND posts.post_status IN ('" . implode( "','", $post_statuses ) . "') {$type_where} ORDER BY posts.post_parent ASC, posts.post_title ASC", $like_term, $like_term ) ); //phpcs:ignore WordPress.DB.PreparedSQL,WordPress.DB.PreparedSQLPlaceholders

		if ( is_numeric( $term ) ) {
			$post_id   = absint( $term );
			$post_type = get_post_type( $post_id );

			if ( 'product_variation' === $post_type && $include_variations ) {
				$product_ids[] = $post_id;
			} elseif ( 'product' === $post_type ) {
				$product_ids[] = $post_id;
			}

			$product_ids[] = wp_get_post_parent_id( $post_id );
		}

		return wp_parse_id_list( $product_ids );
	}

	public static function get_formatted_product_name( $product ) {
		$formatted_variation_list = self::get_variation_attribute( $product );
		$arguments                = array();

		if ( ! empty( $formatted_variation_list ) && count( $formatted_variation_list ) > 0 ) {
			foreach ( $formatted_variation_list as $att => $att_val ) {
				if ( '' === $att_val ) {
					$att_val = __( 'any' );
				}
				$att         = strtolower( $att );
				$att_val     = strtolower( $att_val );
				$arguments[] = "$att: $att_val";
			}
		}

		return sprintf( '%s (#%d) %s', $product->get_title(), $product->get_id(), ( count( $arguments ) > 0 ) ? '(' . implode( ',', $arguments ) . ')' : '' );
	}

	public static function get_variation_attribute( $variation ) {
		$variation_attributes = array();
		if ( is_a( $variation, 'WC_Product_Variation' ) ) {
			$variation_attributes = $variation->get_attributes();

		} else {
			if ( is_array( $variation ) ) {
				foreach ( $variation as $key => $value ) {
					$variation_attributes[ str_replace( 'attribute_', '', $key ) ] = $value;
				}
			}
		}

		return ( $variation_attributes );
	}

	public static function array_equal( $a, $b ) {
		return ( is_array( $a ) && is_array( $b ) && count( $a ) === count( $b ) && array_diff( $a, $b ) === array_diff( $b, $a ) ); //phpcs:ignore WordPress.PHP.StrictComparisons
	}

	public static function validate_string_multi( $actual_values, $compare_type, $expected_value ) {
		if ( empty( $expected_value ) ) {
			return false;
		}

		// look for at least one item that validates the text match
		foreach ( $actual_values as $coupon_code ) {
			if ( self::validate_string( $coupon_code, $compare_type, $expected_value ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $actual_value
	 * @param $compare_type
	 * @param $expected_value
	 *
	 * @return bool
	 */
	public static function validate_string( $actual_value, $compare_type, $expected_value ) {
		// case insensitive
		$actual_value   = strtolower( (string) $actual_value );
		$expected_value = strtolower( (string) $expected_value );

		$return_status = false;
		switch ( $compare_type ) {

			case 'is':
				$return_status = ( $actual_value === $expected_value );//phpcs:ignore WordPress.PHP.StrictComparisons
				break;

			case 'is_not':
				$return_status = ( $actual_value !== $expected_value );//phpcs:ignore WordPress.PHP.StrictComparisons
				break;

			case 'contains':
				$return_status = strstr( $actual_value, $expected_value ) !== false;
				break;

			case 'not_contains':
				$return_status = strstr( $actual_value, $expected_value ) === false;
				break;

			case 'starts_with':
				$length = strlen( $expected_value );

				$return_status = substr( $actual_value, 0, $length ) === $expected_value;
				break;

			case 'ends_with':
				$length = strlen( $expected_value );

				if ( 0 === $length ) {
					$return_status = true;
				} else {
					$return_status = substr( $actual_value, - $length ) === $expected_value;
				}
				break;

			case 'blank':
				$return_status = empty( $actual_value );
				break;

			case 'not_blank':
				$return_status = ! empty( $actual_value );
				break;
		}

		return $return_status;
	}

	public static function get_bwf_customer( $email, $wpid ) {
		if ( function_exists( 'bwf_get_contact' ) ) {
			$get_contact = bwf_get_contact( $wpid, $email );

			return bwf_get_customer( $get_contact );
		}

		return null;
	}

	/**
	 * Run the check and update the status.
	 */
	public static function bwfan_run_cron_test( $forced = false ) {
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {

			$message                    = __( 'The DISABLE_WP_CRON constant is set to true . WP-Cron is disabled and will not run on it\'s own.', 'wp-marketing-automations' );
			$url                        = rest_url( '/woofunnels/v1/worker' ) . '?' . time();
			$message                    .= '<br>' . __( 'Copy following URL and paste it in your Cpanel' );
			$message                    .= '<br><i>' . $url . '</i>';
			$current_version            = BWFAN_VERSION;
			$current_ver                = str_replace( '.', '_', $current_version );
			$version_key                = 'bwfan_version_' . $current_ver;
			$versionArr                 = array();
			$versionArr[ $version_key ] = array(
				'html' => $message,
				'type' => 'wf_error',
			);
			$versionStatus              = WooFunnels_Notifications::get_instance()->get_notification( $version_key, 'bwfan' );

			if ( isset( $versionStatus['error'] ) && $versionStatus['error'] == $version_key . ' Key or Notification group may be Not Available.' ) { //phpcs:ignore WordPress.PHP.StrictComparisons
				$notice_check_in_db = WooFunnels_Notifications::get_instance()->get_dismiss_notification_key( 'bwfan' );
				if ( is_array( $notice_check_in_db ) && false === in_array( $version_key, $notice_check_in_db ) ) {//phpcs:ignore WordPress.PHP.StrictInArray
					WooFunnels_Notifications::get_instance()->register_notification( $versionArr, 'bwfan' );
				}
			}
		}
	}

	/**
	 * Returns the timestamp in the blog's time and format.
	 */
	public static function bwfan_get_datestring( $timestamp = '' ) {
		if ( empty( $timestamp ) ) {
			$timestamp = time();
		}

		return get_date_from_gmt( date( 'Y-m-d H:i:s', $timestamp ), get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
	}

	/**
	 * Get unique actions from a single automation.
	 *
	 * @param $automation_details
	 * @param $selected_actions
	 *
	 * @return array
	 */
	public static function get_automation_selected_action_slugs( $automation_details, $selected_actions ) {
		$automation_actions = $automation_details['meta']['actions'];
		$action_slugs       = array();

		foreach ( $selected_actions as $value1 ) {
			$groups = explode( ',', $value1 );
			foreach ( $groups as $group_actions ) {
				$action_indexes = explode( '_', $group_actions );
				$group_index    = $action_indexes[0];
				$action_index   = $action_indexes[1];
				$action         = $automation_actions[ $group_index ][ $action_index ];
				$action_slugs[] = $action['integration_slug'] . ':' . $action['action_slug'];
			}
		}

		$action_slugs = array_unique( $action_slugs );

		return $action_slugs;
	}

	/**
	 * Return migrations count
	 */
	public static function get_sync_records_count( $status = null ) {
		global $wpdb;
		$query = 'SELECT count(ID) as migrations_count FROM {table_name}';
		if ( ! is_null( $status ) ) {
			$query = 'SELECT count(ID) as migrations_count FROM {table_name} WHERE status = %d';
			$query = $wpdb->prepare( $query, $status );  // phpcs:ignore WordPress.DB.PreparedSQL
		}
		$count = BWFAN_Model_Syncrecords::get_results( $query );
		if ( false === is_array( $count ) || count( $count ) === 0 ) {
			return '';
		}
		$count = $count[0]['migrations_count'];

		return $count;
	}

	/**
	 * Replace the duplicate http from a string.
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	public static function bwfan_correct_protocol_url( $string ) {
		$string = str_replace( 'http://https://', 'https://', $string );
		$string = str_replace( 'https://http://', 'http://', $string );
		$string = str_replace( 'https://https://', 'https://', $string );
		$string = str_replace( 'http://http://', 'http://', $string );

		return $string;
	}

	public static function get_actions_filter_data() {
		return self::get_all_actions_names();
	}

	/**
	 * Get all actions readable names with action slug
	 *
	 * @return array
	 */
	public static function get_all_actions_names() {
		global $wpdb;
		$filter_table           = null;
		$filtered_table_actions = null;

		if ( ( isset( $_GET['tab'] ) && 'tasks' === sanitize_text_field( $_GET['tab'] ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			$filter_table = $wpdb->prefix . 'bwfan_tasks';
		}
		if ( ( isset( $_GET['tab'] ) && 'logs' === sanitize_text_field( $_GET['tab'] ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			$filter_table = $wpdb->prefix . 'bwfan_logs';
		}

		$task_status = ( isset( $_GET['status'] ) && '' !== $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 't_0'; //phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $task_status, '_' ) !== false ) {
			$task_status = explode( '_', $task_status );
			$task_status = intval( $task_status[1] );
		} else {
			$task_status = 0;
		}

		$params = array();

		if ( ! is_null( $filter_table ) ) {
			$query = 'SELECT DISTINCT(integration_action) as actions FROM ' . $filter_table;
			if ( ! is_null( $task_status ) ) {
				$query    .= ' WHERE `status` = %d';
				$params[] = $task_status;
			}
			$parsed_query     = $wpdb->prepare( $query, $params ); // phpcs:ignore WordPress.DB.PreparedSQL
			$distinct_actions = $wpdb->get_results( $parsed_query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL
			if ( is_array( $distinct_actions ) && count( $distinct_actions ) > 0 ) {
				foreach ( $distinct_actions as $values ) {
					$filtered_table_actions[ $values['actions'] ] = $values['actions'];
				}
			}
		}

		$result      = array();
		$all_sources = BWFAN_Core()->integration->get_integrations();
		$all_actions = BWFAN_Load_Integrations::get_all_integrations();
		if ( is_array( $all_actions ) && count( $all_actions ) > 0 ) {
			foreach ( $all_actions as $source_slug => $source_actions ) {
				if ( ! is_array( $source_actions ) || 0 === count( $source_actions ) ) {
					break;
				}
				foreach ( $source_actions as $actions_slug => $action_object ) {
					if ( ! is_null( $filtered_table_actions ) && in_array( $actions_slug, $filtered_table_actions, true ) ) {
						$result[ $actions_slug ] = $all_sources[ $source_slug ]->get_name() . ': ' . $action_object->get_name();
						continue;
					} elseif ( is_null( $filtered_table_actions ) ) {
						$result[ $actions_slug ] = $all_sources[ $source_slug ]->get_name() . ': ' . $action_object->get_name();
					}
				}
			}
		}
		ksort( $result );

		return $result;
	}

	public static function modify_display_numbers( $value = false ) {
		if ( false === $value ) {
			return 0;
		}
		if ( 1000 > $value ) {
			return $value;
		}

		return intval( $value / 1000 ) . 'k';
	}

	/**
	 * Get automations with title
	 *
	 * @param null $only_active_automations
	 *
	 * @return array
	 */
	public static function get_automations_filter_data() {
		$result = array();
		global $wpdb;
		$automation_table      = $wpdb->prefix . 'bwfan_automations';
		$automation_meta_table = $wpdb->prefix . 'bwfan_automationmeta';
		$params                = array();
		$query                 = 'SELECT am.`bwfan_automation_id`, am.`meta_value` ';
		$query                 .= 'FROM ' . $automation_meta_table . ' AS am';
		$query                 .= ' INNER JOIN ' . $automation_table . ' AS aut ON am.`bwfan_automation_id` = aut.`ID`';
		$query                 .= ' WHERE 1=1';
		$query                 .= ' AND am.`meta_key` = %s';
		$params[]              = 'title';
		$parsed_query          = $wpdb->prepare( $query, $params ); // phpcs:ignore WordPress.DB.PreparedSQL
		$all_automations       = $wpdb->get_results( $parsed_query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL

		if ( false === is_array( $all_automations ) || 0 === count( $all_automations ) ) {
			return $result;
		}
		foreach ( $all_automations as $details ) {
			$result[ $details['bwfan_automation_id'] ] = $details['meta_value'];
		}

		return $result;
	}

	/**
	 * Increase the queue batch size while processing actions. default is 5.
	 *
	 * @param $batch_size
	 *
	 * @return mixed
	 */
	public static function ac_increase_queue_batch_size( $batch_size ) {
		$global_settings = self::get_global_settings();
		if ( isset( $global_settings['bwfan_ac_b_s'] ) && $global_settings['bwfan_ac_b_s'] > 0 ) {
			$batch_size = intval( $global_settings['bwfan_ac_b_s'] );
		}

		return $batch_size;
	}

	public static function get_global_settings() {
		$global_settings = get_option( 'bwfan_global_settings', array() );
		$global_settings = self::override_non_changeable_settings( $global_settings );
		$global_settings = wp_parse_args( $global_settings, self::get_default_global_settings() );

		return apply_filters( 'bwfan_get_global_settings', $global_settings );
	}

	/** Deleting from in-db settings, as those were old Shortcodes saved before the update */
	public static function override_non_changeable_settings( $global_settings ) {
		if ( ! is_array( $global_settings ) ) {
			return $global_settings;
		}
		unset( $global_settings['bwfan_unsubscribe_button'] );
		unset( $global_settings['bwfan_subscriber_recipient'] );
		unset( $global_settings['bwfan_subscriber_name'] );

		return $global_settings;
	}

	public static function get_default_sms_provider() {
		$default   = 'bwfco_twilio';
		$providers = self::get_sms_services();
		foreach ( $providers as $provider => $name ) {
			if ( 'bwfco_twilio' === $provider ) {
				return 'bwfco_twilio';
			}

			$default = $provider;
		}

		return $default;
	}

	public static function get_default_email_provider() {
		$default   = 'wp';
		$providers = self::get_email_services();
		foreach ( $providers as $provider => $name ) {
			if ( 'wp' === $provider ) {
				return 'wp';
			}

			$default = $provider;
		}

		return $default;
	}

	public static function get_default_global_settings() {
		$email_settings = self::get_global_email_settings();

		$defaults = array_replace( array(
			'bwfan_ac_b_s'                                 => 25,
			'bwfan_ac_t_l'                                 => 30,
			'bwfan_unsubscribe_button'                     => "[wfan_unsubscribe_button label='Update my preference']",
			'bwfan_subscriber_recipient'                   => '[wfan_contact_email]',
			'bwfan_subscriber_name'                        => '[wfan_contact_name]',
			'bwfan_unsubscribe_email_label'                => __( 'Unsubscribe', 'wp-marketing-automations' ),
			'bwfan_unsubscribe_data_success'               => __( 'Your subscription preference has been updated.', 'wp-marketing-automations' ),
			'bwfan_email_service'                          => self::get_default_email_provider(),
			'bwfan_sms_service'                            => self::get_default_sms_provider(),
			'bwfan_sandbox_mode'                           => 0,
			'bwfan_make_logs'                              => 0,
			'bwfan_ab_enable'                              => 0,
			'bwfan_ab_exclude_users_cart_tracking'         => 0,
			'bwfan_ab_exclude_emails'                      => '',
			'bwfan_ab_exclude_roles'                       => array(),
			'bwfan_ab_init_wait_time'                      => 15,
			'bwfan_disable_abandonment_days'               => 15,
			'bwfan_ab_track_on_add_to_cart'                => 0,
			'bwfan_ab_email_consent'                       => 0,
			'bwfan_ab_mark_lost_cart'                      => 15,
			'bwfan_order_tracking_conversion'              => 15,
			'bwfan_ab_restore_cart_message_success'        => '',
			'bwfan_ab_restore_cart_message_failure'        => __( 'Your cart could not be restored, it may have expired.', 'wp-marketing-automations' ),
			'bwfan_ab_email_consent_message'               => __( 'Your email and cart are saved so we can send you email reminders about this order. {{no_thanks label="No Thanks"}}', 'wp-marketing-automations' ),
			'bwfan_user_consent'                           => 0,
			'bwfan_user_consent_message'                   => __( 'Keep me up to date on news and exclusive offers.', 'wp-marketing-automations' ),
			'bwfan_user_consent_eu'                        => '1',
			'bwfan_user_consent_non_eu'                    => '0',
			'bwfan_delete_autonami_generated_coupons_time' => 1,
			'bwfan_user_consent_position'                  => 'below_term',
			'bwfan_email_footer_setting'                   => '<p>{{business_name}}, {{business_address}}</p>
			<p>Don\'t want to stay in the loop? We\'ll be sad to see you go, but you can click here to <a href="{{unsubscribe_link}}">unsubscribe</a></p>',
			'bwfan_sms_unsubscribe_text'                   => 'Reply STOP to unsubscribe',
			'bwfan_bounce_select'                          => '',
			'bwfan_unsubscribe_page'                       => '',
			'bwfan_unsubscribe_from_all_label'             => __( 'Unsubscribe from all Email Lists', 'wp-marketing-automations-crm' ),
			'bwfan_unsubscribe_from_all_description'       => __( 'You will still receive important billing and transactional emails', 'wp-marketing-automations-crm' ),
		), $email_settings );

		if ( self::is_whatsapp_services_enabled() ) {
			$defaults['bwfan_whatsapp_gap_btw_message'] = 1;
			$services                                   = BWFCRM_Core()->conversation->get_whatsapp_services();
			if ( ! empty( $services ) ) {
				$defaults['bwfan_primary_whats_app_service'] = [
					[
						'key'   => $services[0]['value'],
						'label' => $services[0]['label']
					]
				];
			}
		}

		if ( true === apply_filters( 'bwfan_ab_delete_inactive_carts', false ) ) {
			$defaults['bwfan_ab_remove_inactive_cart_time'] = 30;
		}

		return $defaults;
	}

	/**
	 * Increase the maximum execution time while processing actions. default is 30 seconds.
	 *
	 * @param $max_timeout
	 *
	 * @return mixed
	 */
	public static function ac_increase_max_execution_time( $max_timeout ) {
		$global_settings = self::get_global_settings();
		if ( isset( $global_settings['bwfan_ac_t_l'] ) && $global_settings['bwfan_ac_t_l'] > 0 ) {
			$max_timeout = intval( $global_settings['bwfan_ac_t_l'] );
		}

		return $max_timeout;
	}

	/**
	 * Make custom cron times for autonami events.
	 *
	 * @param $schedules
	 *
	 * @return mixed
	 */
	public static function make_custom_events_time( $schedules ) {
		$schedules['bwfan_once_in_day']         = array(
			'interval' => 86400,
			'display'  => __( 'Once in a day', 'wp-marketing-automations' ),
		);
		$schedules['bwfan_once_in_two_minutes'] = array(
			'interval' => 120,
			'display'  => __( 'Once in 2 minutes', 'wp-marketing-automations' ),
		);
		$schedules['bwfan_every_minute']        = array(
			'interval' => 60,
			'display'  => __( 'Every minute', 'wp-marketing-automations' ),
		);
		$schedules['bwfan_once_in_week']        = array(
			'interval' => 604800,
			'display'  => __( 'Once in a week', 'wp-marketing-automations' ),
		);

		return $schedules;
	}

	/**
	 * Make a new endpoint which will receive the event data
	 */
	public static function add_plugin_endpoint() {
		register_rest_route( 'autonami/v1', '/events', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'capture_async_events' ),
			'permission_callback' => '__return_true',
		) );
		register_rest_route( 'autonami/v1', '/worker', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'run_worker_tasks' ),
			'permission_callback' => '__return_true',
		) );
		register_rest_route( 'autonami/v1', '/autonami-cron', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'run_autonami_cron_events' ),
			'permission_callback' => '__return_true',
		) );
		register_rest_route( 'autonami/v1', '/delete-tasks', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'delete_automation_tasks_by_unique_action_ids' ),
			'permission_callback' => '__return_true',
		) );
		register_rest_route( 'autonami/v1', '/update-contact-automation', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'update_contact_meta' ),
			'permission_callback' => '__return_true',
		) );
		register_rest_route( 'autonami/v1', '/update-generated-increment', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'update_generated_increment' ),
			'permission_callback' => '__return_true',
		) );
		register_rest_route( 'autonami/v1', '/wc-add-to-cart', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'wc_add_to_cart' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * Callback function for receiving the event data
	 *
	 * @param WP_REST_Request $request
	 */
	public static function capture_async_events( WP_REST_Request $request ) {
		$resp            = array();
		$post_parameters = $request->get_body_params();
		if ( empty( $post_parameters ) ) {
			return;
		}

		/**
		 * Check Unique key security
		 */
		$unique_key = get_option( 'bwfan_u_key', false );
		if ( false === $unique_key || ! isset( $post_parameters['unique_key'] ) || $post_parameters['unique_key'] !== $unique_key ) {
			return;
		}

		if ( ( isset( $post_parameters['source'] ) && isset( $post_parameters['event'] ) ) && ( ! isset( $post_parameters['automation_id'] ) ) ) {
			self::$events_async_data = $post_parameters;

			if ( isset( $post_parameters['is_form_submission'] ) && 1 === absint( $post_parameters['is_form_submission'] ) ) {
				do_action( 'bwfan_capture_async_form_submission' );
			}

			$event_slug  = $post_parameters['event'];
			$event       = BWFAN_Core()->sources->get_event( $event_slug );
			$resp['msg'] = 'success';

			/** Check if the Event has active automations, used this check again for cases like Form Submissions trigger */
			if ( ! is_null( $event ) && false !== $event->get_current_event_automations() ) {
				try {
					if ( isset( $post_parameters['aid'] ) ) {
						BWFAN_Core()->automations->current_lifecycle_automation_id = absint( $post_parameters['aid'] );
					}
					$event->capture_async_data();
				} catch ( Exception $exception ) {
					$resp['msg'] = $exception->getMessage();
				}
			}
			wp_send_json( $resp );
		}

		BWFAN_Core()->logger->log( 'Automation Source Or Event data is not available, Data - ' . print_r( self::$events_async_data, true ), 'event_lifecycle' ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions
		wp_send_json( array(
			'msg'  => '',
			'time' => time(),
		) );
	}

	/**
	 * Callback function for running autonami tasks
	 *
	 * @param WP_REST_Request $request
	 */
	public static function run_worker_tasks( WP_REST_Request $request ) {
		$post_parameters = $request->get_body_params();
		if ( ! is_array( $post_parameters ) || ! isset( $post_parameters['worker'] ) ) {
			return;
		}

		/**
		 * Check Unique key security
		 */
		$unique_key = get_option( 'bwfan_u_key', false );
		if ( false === $unique_key || ! isset( $post_parameters['unique_key'] ) || $post_parameters['unique_key'] !== $unique_key ) {
			return;
		}

		self::worker_as_run();
		$resp        = array();
		$resp['msg'] = 'success';
		wp_send_json( $resp );
	}

	public static function worker_as_run() {
		if ( ! class_exists( 'ActionScheduler_QueueRunner' ) ) {
			return;
		}

		$global_settings = self::get_global_settings();
		if ( 1 === intval( $global_settings['bwfan_sandbox_mode'] ) || ( defined( 'BWFAN_SANDBOX_MODE' ) && true === BWFAN_SANDBOX_MODE ) ) {
			return;
		}

		/** Modify Action Scheduler filters */
		self::modify_as_filters();

		$as_ins = ActionScheduler_QueueRunner::instance();

		/** Run Action Scheduler worker */
		$as_ins->run();
	}

	/**
	 * action_scheduler_pre_init action hook
	 */
	public static function as_pre_init_cb() {

		if ( ( ! isset( $_GET['rest_route'] ) || '/autonami/v1/worker' !== sanitize_text_field( $_GET['rest_route'] ) ) && false === strpos( $_SERVER['REQUEST_URI'], '/autonami/v1/worker' ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		if ( ! class_exists( 'BWFAN_AS_CT' ) ) {
			return;
		}

		/** BWFAN_AS_CT instance */
		$as_ct_ins = BWFAN_AS_CT::instance();

		/** Set new AS CT data store */
		$as_ct_ins->change_data_store();
	}

	/**
	 * action_scheduler_pre_init action hook for autonami cli
	 */
	public static function as_pre_init_cli_cb() {

		global $argv;

		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		/**
		 * $argv holds arguments passed to script
		 * https://www.php.net/manual/en/reserved.variables.argv.php
		 */

		if ( empty( $argv ) ) {
			WP_CLI::log( 'Autonami WP CLI arguments not found.' );

			return;
		}

		if ( ! isset( $argv[1] ) || 'autonami-tasks' !== $argv[1] ) {
			return;
		}
		if ( ! isset( $argv[2] ) || 'run' !== $argv[2] ) {
			return;
		}

		if ( ! class_exists( 'BWFAN_AS_CT' ) ) {
			WP_CLI::log( 'BWFAN_AS_CT class not found.' );
		}

		/** BWFAN_AS_CT instance */
		$as_ct_ins = BWFAN_AS_CT::instance();

		/** Set new AS CT data store */
		$as_ct_ins->change_data_store();
	}

	/**
	 * This function is called when rest endpoint of cron is hit.
	 *
	 * @param WP_REST_Request $request
	 */
	public static function run_autonami_cron_events( WP_REST_Request $request ) {
		$resp        = array();
		$resp['msg'] = 'success';

		if ( isset( $_GET['debug'] ) && 'yes' === $_GET['debug'] ) {
			$resp['msg'] = 'connection established';
			wp_send_json( $resp );
		}

		self::run_as_ct_worker();

		wp_send_json( $resp );
	}

	/**
	 * 1 min worker callback
	 */
	public static function run_as_ct_worker() {
		$url       = rest_url( '/autonami/v1/worker' ) . '?' . time();
		$body_data = array(
			'worker'     => true,
			'unique_key' => get_option( 'bwfan_u_key', false ),
		);
		$args      = bwf_get_remote_rest_args( $body_data );
		wp_remote_post( $url, $args );
	}

	public static function modify_as_filters() {
		/** Remove all existing filters */
		remove_all_filters( 'action_scheduler_queue_runner_time_limit' );
		remove_all_filters( 'action_scheduler_queue_runner_batch_size' );
		remove_all_filters( 'action_scheduler_queue_runner_concurrent_batches' );
		remove_all_filters( 'action_scheduler_timeout_period' );
		remove_all_filters( 'action_scheduler_cleanup_batch_size' );

		/** Adding all filters for Autonami Action Scheduler only */
		add_filter( 'action_scheduler_queue_runner_time_limit', function () {
			return 30;
		}, 999 );
		add_filter( 'action_scheduler_queue_runner_batch_size', function () {
			return 30;
		}, 999 );
		add_filter( 'action_scheduler_queue_runner_concurrent_batches', function () {
			return 5;
		}, 999 );
		add_filter( 'action_scheduler_timeout_period', function () {
			return 300;
		}, 999 );
		add_filter( 'action_scheduler_cleanup_batch_size', function () {
			return 20;
		}, 999 );
	}

	/**
	 * phpcs:ignore WordPress.Security.NonceVerification
	 * Return the html for tasks links on tasks listing page.
	 *
	 * @return string
	 */
	public static function get_link_options_for_tasks() {
		$scheduled_count = BWFAN_Core()->tasks->fetch_tasks_count( 0, 0 );
		$scheduled       = sprintf( __( 'Scheduled (%d)', 'wp-marketing-automations' ), $scheduled_count );

		$paused_count          = BWFAN_Core()->tasks->fetch_tasks_count( 0, 1 );
		$paused                = sprintf( __( 'Paused (%d)', 'wp-marketing-automations' ), $paused_count );
		$completed_count       = BWFAN_Core()->logs->fetch_logs_count( 1 );
		$completed             = sprintf( __( 'Completed (%d)', 'wp-marketing-automations' ), $completed_count );
		$failed_count          = BWFAN_Core()->logs->fetch_logs_count( 0 );
		$failed                = sprintf( __( 'Failed (%d)', 'wp-marketing-automations' ), $failed_count );
		$get_campaign_statuses = apply_filters( 'bwfan_admin_trigger_nav', array(
			't_0' => $scheduled,
			't_1' => $paused,
			'l_1' => $completed,
			'l_0' => $failed,
		) );
		$html                  = '<ul class="subsubsub subsubsub_bwfan">';
		$html_inside           = array();
		$current_status        = 't_0';

		if ( isset( $_GET['status'] ) && '' !== $_GET['status'] ) { //phpcs:ignore WordPress.Security.NonceVerification
			$current_status = sanitize_text_field( $_GET['status'] );//phpcs:ignore WordPress.Security.NonceVerification
		}

		// For listing screen
		$all_statuses = array(
			't_0' => array(
				'tab' => 'tasks',
			),
			't_1' => array(
				'tab' => 'tasks',
			),
			'l_0' => array(
				'tab' => 'logs',
			),
			'l_1' => array(
				'tab' => 'logs',
			),
		);

		foreach ( $get_campaign_statuses as $slug => $status ) {
			$need_class = '';
			if ( $slug === $current_status ) {
				$need_class = 'current';
			}

			$args = array(
				'status' => $slug,
			);

			$args['tab']   = $all_statuses[ $slug ]['tab'];
			$url           = add_query_arg( $args, admin_url( 'admin.php?page=autonami-automations' ) );
			$html_inside[] = sprintf( '<li><a href="%s" class="%s">%s</a> </li>', $url, $need_class, $status );
		}

		if ( is_array( $html_inside ) && count( $html_inside ) > 0 ) {
			$html .= implode( '', $html_inside );
		}
		$html .= '</ul>';

		return $html;
	}

	public static function get_logging_status() {
		$global_settings = self::get_global_settings();

		return ( isset( $global_settings['bwfan_make_logs'] ) && 1 === intval( $global_settings['bwfan_make_logs'] ) ) ? true : false;
	}

	/**
	 * Capture all the action ids of an automation and delete all its tasks except for completed tasks.
	 *
	 * @param WP_REST_Request $request
	 */
	public static function delete_automation_tasks_by_unique_action_ids( WP_REST_Request $request ) {
		$post_parameters = $request->get_body_params();

		if ( false === is_array( $post_parameters ) || 0 === count( $post_parameters ) ) {
			return;
		}
		if ( ! isset( $post_parameters['automation_id'] ) || ! isset( $post_parameters['a_track_id'] ) || ! isset( $post_parameters['t_to_delete'] ) ) {
			return;
		}
		/**
		 * Check Unique key security
		 */
		$unique_key = get_option( 'bwfan_u_key', false );
		if ( false === $unique_key || ! isset( $post_parameters['unique_key'] ) || $post_parameters['unique_key'] !== $unique_key ) {
			return;
		}

		$automation_id = sanitize_text_field( $post_parameters['automation_id'] );
		$a_track_id    = sanitize_text_field( $post_parameters['a_track_id'] );
		$t_to_delete   = $post_parameters['t_to_delete'];
		$t_to_delete   = self::is_json( $t_to_delete ) ? json_decode( $t_to_delete, true ) : $t_to_delete;

		if ( false === is_array( $t_to_delete ) || 0 === count( $t_to_delete ) ) {
			return;
		}
		foreach ( $t_to_delete as $key1 => $action_index ) {
			$t_to_delete[ $key1 ] = $a_track_id . '_' . $action_index;
		}

		BWFAN_Core()->tasks->delete_by_index_ids( $automation_id, $t_to_delete );
		BWFAN_Core()->logs->delete_by_index_ids( $automation_id, $t_to_delete );
	}

	public static function is_json( $string ) {
		json_decode( $string );

		return ( json_last_error() === JSON_ERROR_NONE );
	}

	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 *
	 * @param string|array $var Data to sanitize.
	 *
	 * @return string|array
	 */
	public static function bwfan_clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'self::bwfan_clean', $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}

	/**
	 * Update contact automation table for current automation.
	 *
	 * @param WP_REST_Request $request
	 */
	public static function update_contact_meta( WP_REST_Request $request ) {
		$post_parameters = $request->get_body_params();

		if ( false === is_array( $post_parameters ) || 0 === count( $post_parameters ) ) {
			return;
		}
		if ( ! isset( $post_parameters['automation_id'] ) || ! isset( $post_parameters['email'] ) || ! isset( $post_parameters['user_id'] ) ) {
			return;
		}
		/**
		 * Check Unique key security
		 */
		$unique_key = get_option( 'bwfan_u_key', false );
		if ( false === $unique_key || ! isset( $post_parameters['unique_key'] ) || $post_parameters['unique_key'] !== $unique_key ) {
			return;
		}

		$automation_id = sanitize_text_field( $post_parameters['automation_id'] );
		$email         = sanitize_text_field( $post_parameters['email'] );
		$user_id       = sanitize_text_field( $post_parameters['user_id'] );
		$contact_obj   = bwf_get_contact( $user_id, $email );
		$contact_id    = $contact_obj->id;

		if ( ! isset( $contact_id ) || empty( $contact_id ) ) {
			return;
		}

		$data = array(
			'contact_id'    => $contact_id,
			'automation_id' => $automation_id,
			'time'          => time(),
		);

		BWFAN_Model_Contact_Automations::insert( $data );
	}

	public static function update_generated_increment( WP_REST_Request $request ) {
		$post_parameters = $request->get_body_params();
		if ( false === is_array( $post_parameters ) || 0 === count( $post_parameters ) ) {
			return;
		}
		if ( ! isset( $post_parameters['id'] ) ) {
			return;
		}
		/**
		 * Check Unique key security
		 */
		$unique_key = get_option( 'bwfan_u_key', false );
		if ( false === $unique_key || ! isset( $post_parameters['unique_key'] ) || $post_parameters['unique_key'] !== $unique_key ) {
			return;
		}

		$date = date( 'Y-m-d' );
		/**
		 * All calculations are done via this row and the stats displayed are fetched from this row only.
		 */
		WFCO_Model_Report_views::update_data( $date, 0, 1 );
		/**
		 * If AeroCheckout page
		 *
		 * This row is saved for future use
		 */
		if ( ! empty( $post_parameters['id'] ) ) {
			WFCO_Model_Report_views::update_data( $date, $post_parameters['id'], 1 );
		}
	}

	public static function wc_add_to_cart( WP_REST_Request $request ) {
		$post_parameters = $request->get_body_params();
		if ( false === is_array( $post_parameters ) || 0 === count( $post_parameters ) ) {
			return;
		}
		if ( ! isset( $post_parameters['id'] ) || ! isset( $post_parameters['coupon_data'] ) || ! isset( $post_parameters['items'] ) || ! isset( $post_parameters['fees'] ) ) {
			return;
		}
		/**
		 * Check Unique key security
		 */
		$unique_key = get_option( 'bwfan_u_key', false );
		if ( false === $unique_key || ! isset( $post_parameters['unique_key'] ) || $post_parameters['unique_key'] !== $unique_key ) {
			return;
		}

		$abandoned_obj = BWFAN_Abandoned_Cart::get_instance();
		$user_id       = $post_parameters['id'];
		$user_data     = get_userdata( $user_id );
		$email         = $user_data->user_email;
		$coupon_data   = $post_parameters['coupon_data'];
		$items         = $post_parameters['items'];
		$fees          = $post_parameters['fees'];
		$cart_details  = $abandoned_obj->get_cart_by_key( 'email', $email, '%s' );

		if ( false === $cart_details ) {
			self::create_abandoned_cart( array(
				'user_id' => $user_id,
				'email'   => $email,
				'coupons' => $coupon_data,
				'items'   => $items,
				'fees'    => $fees,
			) );

			return;
		}

		$cart_details['coupons'] = $coupon_data;
		$cart_details['items']   = $items;
		$cart_details['fees']    = $fees;
		$data                    = self::get_abandoned_totals( $cart_details );
		$data['user_id']         = $user_id;
		$data['last_modified']   = current_time( 'mysql', 1 );

		$where = array(
			'ID' => $cart_details['ID'],
		);

		BWFAN_Model_Abandonedcarts::update( $data, $where );
	}

	private static function create_abandoned_cart( $data ) {
		$customer      = new WC_Customer( $data['user_id'] );
		$checkout_data = array(
			'fields' => array(
				'billing_first_name'  => $customer->get_billing_first_name(),
				'billing_last_name'   => $customer->get_billing_last_name(),
				'billing_company'     => $customer->get_billing_company(),
				'billing_country'     => $customer->get_billing_country(),
				'billing_address_1'   => $customer->get_billing_address_1(),
				'billing_address_2'   => $customer->get_billing_address_2(),
				'billing_city'        => $customer->get_billing_city(),
				'billing_state'       => $customer->get_billing_state(),
				'billing_postcode'    => $customer->get_billing_postcode(),
				'billing_phone'       => $customer->get_billing_phone(),
				'billing_email'       => $customer->get_billing_email(),
				'shipping_first_name' => $customer->get_shipping_first_name(),
				'shipping_last_name'  => $customer->get_shipping_last_name(),
				'shipping_company'    => $customer->get_shipping_company(),
				'shipping_country'    => $customer->get_shipping_country(),
				'shipping_address_1'  => $customer->get_shipping_address_1(),
				'shipping_address_2'  => $customer->get_shipping_address_2(),
				'shipping_city'       => $customer->get_shipping_city(),
				'shipping_state'      => $customer->get_shipping_state(),
				'shipping_postcode'   => $customer->get_shipping_postcode(),
			),
		);

		$data['status']        = 0;
		$data['created_time']  = current_time( 'mysql', 1 );
		$data['last_modified'] = current_time( 'mysql', 1 );
		$data['token']         = self::create_token( 32 );
		$data['cookie_key']    = '';
		$data['checkout_data'] = wp_json_encode( $checkout_data );
		$data['currency']      = get_woocommerce_currency();
		$data                  = self::get_abandoned_totals( $data );

		BWFAN_Model_Abandonedcarts::insert( $data );
	}

	public static function create_token( $length = 25, $case_sensitive = true, $more_numbers = false ) {
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';

		if ( $case_sensitive ) {
			$chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		}
		if ( $more_numbers ) {
			$chars .= '01234567890123456789';
		}

		$password     = '';
		$chars_length = strlen( $chars );

		for ( $i = 0; $i < $length; $i ++ ) {
			$password .= substr( $chars, wp_rand( 0, $chars_length - 1 ), 1 );
		}

		return $password;
	}

	private static function get_abandoned_totals( $data ) {
		$coupon_data          = $data['coupons'];
		$items                = $data['items'];
		$fees                 = $data['fees'];
		$calculated_subtotal  = 0;
		$calculated_tax_total = 0;
		$calculated_total     = 0;
		$tax_display          = get_option( 'woocommerce_tax_display_cart' );

		foreach ( maybe_unserialize( $items ) as $item ) {
			$line_subtotal_tax    = isset( $item['line_subtotal_tax'] ) ? floatval( $item['line_subtotal_tax'] ) : 0;
			$line_subtotal        = isset( $item['line_subtotal'] ) ? floatval( $item['line_subtotal'] ) : 0;
			$calculated_tax_total += $line_subtotal_tax;
			$calculated_total     += $line_subtotal + $line_subtotal_tax;
			$calculated_subtotal  += 'excl' === $tax_display ? $line_subtotal : $line_subtotal + $line_subtotal_tax;
		}
		foreach ( maybe_unserialize( $coupon_data ) as $coupon ) {
			$calculated_total     -= $coupon['discount_incl_tax'];
			$calculated_tax_total -= $coupon['discount_tax'];
		}
		foreach ( maybe_unserialize( $fees ) as $fee ) {
			$calculated_total     += ( $fee->total + $fee->tax );
			$calculated_tax_total += $fee->tax;
		}

		$calculated_total   = wc_format_decimal( $calculated_total, wc_get_price_decimals() );
		$data['total']      = $calculated_total;
		$data['total_base'] = apply_filters( 'bwfan_ab_cart_total_base', $calculated_total );

		return $data;
	}

	/**
	 * Get human readable time format like 18 minutes 47 seconds ago
	 *
	 * @param $timestamp
	 * @param $date
	 *
	 * @return string
	 */
	public static function get_human_readable_time( $timestamp, $date ) {
		$current_timestamp = gmdate( 'U' );
		if ( $current_timestamp > $timestamp ) {
			$schedule_display_string = '<time title="' . $date . '">' . self::human_interval( gmdate( 'U' ) - $timestamp ) . __( ' ago', 'wp-marketing-automations' ) . '</time>';
		} else {
			$schedule_display_string = '<time title="' . $date . '">' . __( 'in ', 'wp-marketing-automations' ) . self::human_interval( $timestamp - gmdate( 'U' ) ) . '</time>';
		}

		return $schedule_display_string;
	}

	public static function human_interval( $interval, $periods_to_include = 2 ) {

		self::$time_periods = array(
			array(
				'seconds' => YEAR_IN_SECONDS,
				'names'   => _n_noop( '%s year', '%s years', 'action-scheduler' ),
			),
			array(
				'seconds' => MONTH_IN_SECONDS,
				'names'   => _n_noop( '%s month', '%s months', 'action-scheduler' ),
			),
			array(
				'seconds' => WEEK_IN_SECONDS,
				'names'   => _n_noop( '%s week', '%s weeks', 'action-scheduler' ),
			),
			array(
				'seconds' => DAY_IN_SECONDS,
				'names'   => _n_noop( '%s day', '%s days', 'action-scheduler' ),
			),
			array(
				'seconds' => HOUR_IN_SECONDS,
				'names'   => _n_noop( '%s hour', '%s hours', 'action-scheduler' ),
			),
			array(
				'seconds' => MINUTE_IN_SECONDS,
				'names'   => _n_noop( '%s minute', '%s minutes', 'action-scheduler' ),
			),
			array(
				'seconds' => 1,
				'names'   => _n_noop( '%s second', '%s seconds', 'action-scheduler' ),
			),
		);

		if ( $interval <= 0 ) {
			return __( 'Now!', 'action-scheduler' );
		}

		$output = '';

		for ( $time_period_index = 0, $periods_included = 0, $seconds_remaining = $interval; $time_period_index < count( self::$time_periods ) && $seconds_remaining > 0 && $periods_included < $periods_to_include; $time_period_index ++ ) {

			$periods_in_interval = floor( $seconds_remaining / self::$time_periods[ $time_period_index ]['seconds'] );
			if ( $periods_in_interval > 0 ) {
				if ( ! empty( $output ) ) {
					$output .= ' ';
				}
				$output            .= sprintf( _n( self::$time_periods[ $time_period_index ]['names'][0], self::$time_periods[ $time_period_index ]['names'][1], $periods_in_interval, 'action-scheduler' ), $periods_in_interval );
				$seconds_remaining -= $periods_in_interval * self::$time_periods[ $time_period_index ]['seconds'];
				$periods_included ++;
			}
		}

		return $output;
	}

	/**
	 * Return seconds from 24 hr format.
	 *
	 * @param $str_time
	 *
	 * @return float|int
	 */
	public static function get_seconds_from_time_format( $str_time ) {
		$hours   = '';
		$minutes = '';
		$seconds = '';

		sscanf( $str_time, '%d:%d:%d', $hours, $minutes, $seconds );
		$time_seconds = ( isset( $hours ) && ! empty( $hours ) ) ? $hours * 3600 + $minutes * 60 + $seconds : $minutes * 60 + $seconds;

		return $time_seconds;
	}

	/**
	 * Get the nearest date.
	 *
	 * @param $actual_timestamp
	 * @param $days_selected
	 *
	 * @return false|int
	 */
	public static function get_nearest_date( $actual_timestamp, $days_selected ) {
		$days_of_week = array(
			1 => 'monday',
			2 => 'tuesday',
			3 => 'wednesday',
			4 => 'thursday',
			5 => 'friday',
			6 => 'saturday',
			7 => 'sunday',
		);

		$actual_day_of_week    = date( 'N', $actual_timestamp );
		$actual_timestamp_date = date( 'Y-m-d H:i:s', $actual_timestamp );
		$dates                 = array();
		foreach ( $days_selected as $day_number ) {
			$next_day        = $days_of_week[ $day_number ];
			$next_day_string = 'next ' . $next_day;
			$next_day        = date( 'Y-m-d H:i:s', strtotime( $next_day_string, $actual_timestamp ) );
			if ( absint( $actual_day_of_week ) === absint( $day_number ) ) {
				$dates[] = date( 'Y-m-d H:i:s', $actual_timestamp );
			} else {
				$dates[] = $next_day;
			}
		}

		$closest_date      = self::find_closest( $dates, $actual_timestamp_date );
		$closest_timestamp = strtotime( $closest_date );

		return $closest_timestamp;
	}

	/**
	 * Find the closest matching date.
	 *
	 * @param $array
	 * @param $date
	 *
	 * @return mixed
	 */
	public static function find_closest( $array, $date ) {
		$interval = array();
		foreach ( $array as $day ) {
			$interval[] = abs( strtotime( $date ) - strtotime( $day ) );
		}

		asort( $interval );
		$closest = key( $interval );

		return $array[ $closest ];
	}

	/**
	 * @param $ids
	 * @param $status
	 */
	public static function update_abandoned_rows( $ids, $status ) {
		global $wpdb;
		$automationCount        = count( $ids );
		$stringPlaceholders     = array_fill( 0, $automationCount, '%s' );
		$placeholdersautomation = implode( ', ', $stringPlaceholders );
		$sql_query              = "Update {table_name} Set status = $status WHERE ID IN ($placeholdersautomation)";
		$sql_query              = $wpdb->prepare( $sql_query, $ids ); // WPCS: unprepared SQL OK

		BWFAN_Model_Abandonedcarts::get_results( $sql_query );
	}

	/**
	 * Delete all the tasks related to an abandoned cart row
	 *
	 * @param $abandoned_cart_id
	 */
	public static function delete_abandoned_cart_tasks( $abandoned_cart_id ) {
		global $wpdb;
		$meta_key = 'c_a_id';
		$query    = $wpdb->prepare( 'SELECT `bwfan_task_id` FROM {table_name} WHERE meta_key = %s AND meta_value = %s', $meta_key, $abandoned_cart_id );
		$result   = BWFAN_Model_Taskmeta::get_results( $query );

		if ( ! is_array( $result ) || count( $result ) === 0 ) {
			return;
		}

		$task_ids = array();
		foreach ( $result as $value1 ) {
			$task_ids[] = $value1['bwfan_task_id'];
		}

		BWFAN_Core()->tasks->delete_tasks( $task_ids );
	}

	/**
	 * This function checks for all the active carts. If last modified time of active carts exceeds the global cart timeout setting,
	 * then those carts will me made as abandoned.
	 */
	public static function check_for_abandoned_carts() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		$global_settings = self::get_global_settings();
		if ( empty( $global_settings['bwfan_ab_enable'] ) ) {
			return;
		}

		$all_sources = BWFAN_Load_Sources::get_all_sources_obj();
		$all_sources['wc']['ab_cart_abandoned']->load_hooks();
		$all_sources['wc']['ab_cart_abandoned']->get_eligible_abandoned_rows();
	}

	/**
	 * Delete all the old abandoned rows from db table. This function runs once in a week.
	 */
	public static function delete_old_abandoned_carts() {
		if ( false === apply_filters( 'bwfan_ab_delete_inactive_carts', false ) ) {
			return;
		}

		global $wpdb;
		$global_settings        = self::get_global_settings();
		$abandoned_time_in_days = absint( $global_settings['bwfan_ab_remove_inactive_cart_time'] ) * 1440;
		$query                  = $wpdb->prepare( 'select T.ID from {table_name} T where TIMESTAMPDIFF(MINUTE,T.last_modified,UTC_TIMESTAMP) > %d', $abandoned_time_in_days );
		$abandoned_carts        = BWFAN_Model_Abandonedcarts::get_results( $query );

		if ( ! is_array( $abandoned_carts ) || count( $abandoned_carts ) === 0 ) {
			return;
		}

		$abandoned_cart_ids = array();
		foreach ( $abandoned_carts as $value1 ) {
			$abandoned_cart_ids[] = $value1['ID'];
		}

		$automationCount        = count( $abandoned_cart_ids );
		$stringPlaceholders     = array_fill( 0, $automationCount, '%s' );
		$placeholdersautomation = implode( ', ', $stringPlaceholders );
		$sql_query              = "Delete FROM {table_name} WHERE ID IN ($placeholdersautomation)";
		$sql_query              = $wpdb->prepare( $sql_query, $abandoned_cart_ids ); // WPCS: unprepared SQL OK

		BWFAN_Model_Abandonedcarts::delete_multiple( $sql_query );
	}

	public static function mark_abandoned_lost_cart() {
		global $wpdb;
		$global_settings        = self::get_global_settings();
		$abandoned_time_in_days = absint( $global_settings['bwfan_ab_mark_lost_cart'] ) * 1440;

		$wpdb->query( $wpdb->prepare( "
							UPDATE {$wpdb->prefix}bwfan_abandonedcarts
							SET `status` = %d
							WHERE TIMESTAMPDIFF(MINUTE,last_modified,UTC_TIMESTAMP) > %d
							", 2, $abandoned_time_in_days ) );
	}

	/**
	 * Delete all the old abandoned rows from db table. This function runs once in a week.
	 */
	public static function delete_expired_autonami_coupons() {
		global $wpdb;

		$global_settings = self::get_global_settings();

		/**
		 * 1 day = 1440 minutes
		 */

		$coupon_time_in_days = absint( $global_settings['bwfan_delete_autonami_generated_coupons_time'] ) * 1440;
		if ( ( 30 * 1440 ) < $coupon_time_in_days ) {
			$coupon_time_in_days = 30 * 1440;
		}

		$coupons = $wpdb->get_results( $wpdb->prepare( "
														SELECT m1.post_id as id
														FROM {$wpdb->prefix}postmeta as m1
														LEFT JOIN {$wpdb->prefix}postmeta as m2
														ON m1.post_id = m2.post_id
														LEFT JOIN {$wpdb->prefix}postmeta as m3
														ON m1.post_id = m3.post_id
														WHERE m1.meta_key = %s
														AND m1.meta_value = %d
														AND m2.meta_key = %s
														AND TIMESTAMPDIFF(MINUTE,FROM_UNIXTIME(m2.meta_value),UTC_TIMESTAMP) > %d
														AND m3.meta_key = %s
														AND m3.meta_value = 0
														", '_is_bwfan_coupon', 1, 'date_expires', $coupon_time_in_days, 'usage_count' ) );

		if ( empty( $coupons ) ) {
			return;
		}

		foreach ( $coupons as $coupon ) {
			wp_delete_post( $coupon->id, true );
		}
	}

	/**
	 * Get all the scheduled task of given automation ids and the contact email
	 *
	 * @param array $winback_automations
	 * @param string $email
	 *
	 * @return array
	 */
	public static function get_schedule_task_by_email( $winback_automations, $email ) {
		global $wpdb;
		$task_table_name      = $wpdb->prefix . 'bwfan_tasks';
		$task_meta_table_name = $wpdb->prefix . 'bwfan_taskmeta';
		$tasks_results        = array();

		if ( empty( $winback_automations ) || empty( $email ) ) {
			return $tasks_results;
		}

		foreach ( $winback_automations as $automation_id ) {
			$tasks_results[ $automation_id ] = $wpdb->get_results( "SELECT t.ID FROM $task_table_name AS t JOIN $task_meta_table_name AS tm ON t.ID=tm.bwfan_task_id WHERE t.automation_id='$automation_id' AND tm.meta_key='integration_data' AND tm.meta_value like '%$email%'", ARRAY_A );
		}

		return $tasks_results;
	}

	/**
	 * Get all the scheduled tasks of given automation ids and the contact phone number
	 *
	 * @param $winback_automations
	 * @param $phone
	 *
	 * @return array
	 */
	public static function get_schedule_task_by_phone( $winback_automations, $phone ) {
		global $wpdb;
		$task_table_name      = $wpdb->prefix . 'bwfan_tasks';
		$task_meta_table_name = $wpdb->prefix . 'bwfan_taskmeta';
		$tasks_results        = array();

		if ( empty( $winback_automations ) || empty( $phone ) ) {
			return $tasks_results;
		}

		foreach ( $winback_automations as $automation_id ) {
			$tasks_results[ $automation_id ] = $wpdb->get_results( "SELECT t.ID FROM $task_table_name AS t JOIN $task_meta_table_name AS tm ON t.ID=tm.bwfan_task_id WHERE t.automation_id='$automation_id' AND tm.meta_key='integration_data' AND tm.meta_value like '%$phone%'", ARRAY_A );
		}

		return $tasks_results;
	}

	public static function wc_get_cart_recovery_url( $token, $coupon = '', $lang = '' ) {

		$checkout_id = get_option( 'woocommerce_checkout_page_id' );

		/**
		 * Making checkout page compatible with the WPML
		 * Trying & getting the base language translation post to validate the checkout page
		 */
		$url = self::get_permalink_by_language( $checkout_id, $lang );

		if ( empty( $url ) ) {
			$url = home_url();
		}

		$url = add_query_arg( array(
			'bwfan-ab-id' => $token,
		), $url );

		if ( ! empty( $coupon ) ) {
			$url = add_query_arg( array(
				'bwfan-coupon' => preg_replace( "/&#?[a-z0-9]{2,8};/i", "", $coupon ),
			), $url );
		}

		return apply_filters( 'bwfan_abandoned_cart_restore_link', $url, $token, $coupon );
	}

	/**
	 * Get all the abandoned carts by email with status 1 and 2
	 *
	 * @param $email
	 *
	 * @return array|null|object|void
	 */
	public static function get_email_abandoned( $email ) {
		if ( empty( $email ) ) {
			return;
		}
		global $wpdb;
		$abandoned_table = $wpdb->prefix . 'bwfan_abandonedcarts';
		$abandoned_data  = $wpdb->get_results( "select ID,last_modified from $abandoned_table where status in(1,2) and email='" . $email . "' order by last_modified limit 0,3", ARRAY_A );

		return $abandoned_data;
	}

	/**
	 * Set the status 3 i.e. aborted of the abandoned cart
	 *
	 * @param $abandoned_id
	 */
	public static function set_email_cart_aborted( $abandoned_id ) {
		if ( empty( $abandoned_id ) ) {
			return;
		}

		$data['status'] = 3;
		$where          = array(
			'ID' => $abandoned_id,
		);

		BWFAN_Model_Abandonedcarts::update( $data, $where );
	}

	/**
	 * @param $post_id
	 * @param string $lang
	 * get permalink by language
	 *
	 * @return false|mixed|string|void
	 */

	public static function get_permalink_by_language( $post_id, $lang = '' ) {

		$url = get_permalink( $post_id );

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			global $sitepress;

			$language_code = $sitepress->get_default_language();

			if ( ! empty( $lang ) ) {
				$language_code = $lang;
			}
			if ( version_compare( ICL_SITEPRESS_VERSION, '3.2' ) > 0 ) {
				$post_id = apply_filters( 'wpml_object_id', $post_id, 'page', false, $language_code );
			} else {
				$post_id = wpml_object_id_filter( $post_id, 'page', false, $language_code );
			}

			$url = apply_filters( 'wpml_permalink', $url, $language_code );
		}

		/** in case of translatepress */
		if ( bwfan_is_translatepress_active() ) {
			global $TRP_LANGUAGE;
			$trp_settings  = get_option( 'trp_settings' );
			$language_code = $trp_settings['default-language'];
			if ( ! empty( $lang ) ) {
				$language_code = $lang;
			}
			$trp           = TRP_Translate_Press::get_trp_instance();
			$url_converter = $trp->get_component( 'url_converter' );
			$url           = $url_converter->get_url_for_language( $language_code, $url, '' );
		}

		/** for polylang language */
		if ( function_exists( 'pll_current_language' ) ) {
			$language_code = pll_default_language();

			if ( ! empty( $lang ) ) {
				$language_code = $lang;
			}

			$url = add_query_arg( array(
				'lang' => $language_code,
			), $url );
		}

		if ( function_exists( 'bwfan_is_weglot_active' ) && bwfan_is_weglot_active() ) {
			$language_code = weglot_get_original_language();

			if ( ! empty( $lang ) ) {
				$language_code = $lang;
			}

			$site_url = home_url();
			$url      = str_replace( $site_url, $site_url . '/' . $language_code, $url );
		}

		return $url;
	}

	public static function merge_default_actions() {
		$all_automations = self::get_default_connector();
		$integrations    = self::get_default_actions();
		$default_data    = array();

		foreach ( $all_automations as $a_slug => $automation ) {
			$nice_name = $automation['nice_name'];
			if ( isset( $integrations[ $a_slug ] ) ) {
				$actions = $integrations[ $a_slug ];
				foreach ( $actions as $slug => $action ) {
					if ( ! class_exists( 'bwfan_' . $slug ) || ! bwfan_is_autonami_pro_active() ) {
						$default_data[ $nice_name ][ $slug ] = $action;
					}
				}
			}
		}

		return empty( $default_data ) ? new stdClass() : $default_data;
	}

	public static function get_default_connector() {
		return array(
			'wc'             => array(
				'nice_name'          => __( 'WooCommerce', 'wp-marketing-automations' ),
				'slug'               => 'wp_adv',
				'connector_slug'     => '',
				'native_integration' => true,
			),
			'wp_adv'         => array(
				'nice_name'          => __( 'WordPress Advanced', 'wp-marketing-automations' ),
				'slug'               => 'wp_adv',
				'connector_slug'     => '',
				'native_integration' => true,
			),
			'activecampaign' => array(
				'nice_name'          => __( 'ActiveCampaign', 'wp-marketing-automations' ),
				'slug'               => 'activecampaign',
				'connector_slug'     => 'bwfco_activecampaign',
				'native_integration' => false,
			),
			'drip'           => array(
				'nice_name'          => __( 'Drip', 'wp-marketing-automations' ),
				'slug'               => 'drip',
				'connector_slug'     => 'bwfco_drip',
				'native_integration' => false,
			),
			'google_sheets'  => array(
				'nice_name'          => __( 'Google Sheets', 'wp-marketing-automations' ),
				'slug'               => 'google_sheets',
				'connector_slug'     => 'bwfco_google_sheets',
				'native_integration' => false,
			),
			'slack'          => array(
				'nice_name'          => __( 'Slack', 'wp-marketing-automations' ),
				'slug'               => 'slack',
				'connector_slug'     => 'bwfco_slack',
				'native_integration' => false,
			),
			'zapier'         => array(
				'nice_name'          => __( 'Zapier', 'wp-marketing-automations' ),
				'slug'               => 'zapier',
				'connector_slug'     => '',
				'native_integration' => false,
			),
		);
	}

	public static function get_default_actions() {

		return array(
			'wc'             => array(
				'wc_change_order_status' => __( 'Change Order Status', 'wp-marketing-automations' ),
				'wc_add_order_note'      => __( 'Add Order Note', 'wp-marketing-automations' ),
				'wc_remove_coupon'       => __( 'Delete Coupon', 'wp-marketing-automations' ),
			),
			'activecampaign' => array(
				'ac_create_contact'        => __( 'Create Contact', 'wp-marketing-automations' ),
				'ac_add_tag'               => __( 'Add Tags', 'wp-marketing-automations' ),
				'ac_rmv_tag'               => __( 'Remove Tags', 'wp-marketing-automations' ),
				'ac_add_to_automation'     => __( 'Add Contact To Automation', 'wp-marketing-automations' ),
				'ac_rmv_from_automation'   => __( 'Remove Contact From Automation', 'wp-marketing-automations' ),
				'ac_add_to_list'           => __( 'Add Contact To List', 'wp-marketing-automations' ),
				'ac_rmv_from_list'         => __( 'Remove Contact From List', 'wp-marketing-automations' ),
				'ac_create_abandoned_cart' => __( 'Create Abandoned Cart', 'wp-marketing-automations' ),
				'ac_create_order'          => __( 'Create Order', 'wp-marketing-automations' ),
				'ac_create_deal'           => __( 'Create Deal', 'wp-marketing-automations' ),
				'ac_create_deal_note'      => __( 'Create Deal Note', 'wp-marketing-automations' ),
				'ac_update_deal'           => __( 'Update Deal', 'wp-marketing-automations' ),
				'ac_update_customfields'   => __( 'Update Fields', 'wp-marketing-automations' ),
			),
			'drip'           => array(
				'dr_create_subscriber' => __( 'Create / Update Subscriber', 'wp-marketing-automations' ),
				'dr_add_tags'          => __( 'Add Tags', 'wp-marketing-automations' ),
				'dr_rmv_tags'          => __( 'Remove Tags', 'wp-marketing-automations' ),
				'dr_add_to_campaign'   => __( 'Add Subscriber to Campaign', 'wp-marketing-automations' ),
				'dr_rmv_from_campaign' => __( 'Remove Subscriber from Campaign', 'wp-marketing-automations' ),
				'dr_add_to_workflow'   => __( 'Add Subscriber to Workflow', 'wp-marketing-automations' ),
				'dr_rmv_from_workflow' => __( 'Remove Subscriber from Workflow', 'wp-marketing-automations' ),
				'dr_add_cart'          => __( 'Cart Activity', 'wp-marketing-automations' ),
				'dr_add_order'         => __( 'Add A New Order', 'wp-marketing-automations' ),
				'dr_add_customfields'  => __( 'Update Custom fields of Subscriber', 'wp-marketing-automations' ),
			),
			'convertkit'     => array(
				'ck_add_customfields'  => __( 'Update Custom Fields', 'wp-marketing-automations' ),
				'ck_add_tags'          => __( 'Add Tags', 'wp-marketing-automations' ),
				'ck_rmv_tags'          => __( 'Remove Tags', 'wp-marketing-automations' ),
				'ck_add_to_sequence'   => __( 'Add Subscriber To Sequence', 'wp-marketing-automations' ),
				'ck_rmv_from_sequence' => __( 'Remove Subscriber from Sequence', 'wp-marketing-automations' ),
				'ck_add_order'         => __( 'Create A New Purchase', 'wp-marketing-automations' ),
			),
			'google_sheets'  => array(
				'gs_insert_data' => __( 'Insert Row', 'wp-marketing-automations' ),
				'gs_update_data' => __( 'Update Row', 'wp-marketing-automations' ),
			),
			'slack'          => array(
				'sl_message_user' => __( 'Sends a message to a user', 'wp-marketing-automations' ),
				'sl_message'      => __( 'Sends a message to a channel', 'wp-marketing-automations' ),
			),
			'zapier'         => array(
				'za_send_data' => __( 'Send data to zapier', 'wp-marketing-automations' ),
			),
			'twilio'         => array(
				'twilio_send_sms' => __( 'Send SMS', 'wp-marketing-automations' ),
			),
			'wp_adv'         => array(
				'wp_createuser'       => __( 'Create User', 'wp-marketing-automations' ),
				'wp_update_user_meta' => __( 'Update User Meta', 'wp-marketing-automations' ),
				'wp_http_post'        => __( 'HTTP Post', 'wp-marketing-automations' ),
				'wp_custom_callback'  => __( 'Custom Callback', 'wp-marketing-automations' ),
				'wp_debug'            => __( 'Debug', 'wp-marketing-automations' ),
			),
		);
	}

	public static function merge_pro_events( $events ) {

		$default = self::default_events();
		foreach ( $default as $slug => $data ) {
			if ( ! class_exists( 'BWFAN_' . $slug ) || ! bwfan_is_autonami_pro_active() ) {
				$source_type                                           = $data['source_type'];
				$events[ $source_type ]['events']['Upstroke'][ $slug ] = array(
					'name'      => $data['event_name'],
					'available' => 'no',
				);
			}
		}

		return $events;
	}

	public static function default_events() {
		return array();

		return array(
			'wf_funnel_started'       => array(
				'source_type'         => 'wc',
				'event_name'          => __( 'Funnel Started', 'wp-marketing-automations' ),
				'event_desc'          => 'This automation would trigger when a funnel is started.',
				'slug'                => 'wf_funnel_started',
				'is_time_independent' => false,
				'excluded_actions'    => array(),
				'event_saved_data'    => array(),
			),
			'wf_funnel_ended'         => array(
				'source_type'         => 'wc',
				'event_name'          => __( 'Funnel Ended', 'wp-marketing-automations' ),
				'event_desc'          => 'This automation would trigger when a funnel is ended.',
				'slug'                => 'wf_funnel_ended',
				'is_time_independent' => false,
				'excluded_actions'    => array(),
				'event_saved_data'    => array(),
				'available'           => 'no',
			),
			'wf_offer_viewed'         => array(
				'source_type'         => 'wc',
				'event_name'          => __( 'Offer Viewed', 'wp-marketing-automations' ),
				'event_desc'          => 'This automation would trigger when an offer is viewed by the user.',
				'slug'                => 'wf_offer_viewed',
				'is_time_independent' => false,
				'excluded_actions'    => array(),
				'event_saved_data'    => array(),
				'available'           => 'no',
			),
			'wf_product_accepted'     => array(
				'source_type'         => 'wc',
				'event_name'          => __( 'Offer Accepted', 'wp-marketing-automations' ),
				'event_desc'          => 'This automation would trigger when an upstroke offer is accepted by the user.',
				'slug'                => 'wf_product_accepted',
				'is_time_independent' => false,
				'excluded_actions'    => array(),
				'event_saved_data'    => array(),
				'available'           => 'no',
			),
			'wf_offer_payment_failed' => array(
				'source_type'         => 'wc',
				'event_name'          => __( 'Offer Payment Failed', 'wp-marketing-automations' ),
				'event_desc'          => 'This automation would trigger when the payment is failed while accepting an offer.',
				'slug'                => 'wf_offer_payment_failed',
				'is_time_independent' => false,
				'excluded_actions'    => array(),
				'event_saved_data'    => array(),
				'available'           => 'no',
			),
			'wf_offer_rejected'       => array(
				'source_type'         => 'wc',
				'event_name'          => __( 'Offer Rejected', 'wp-marketing-automations' ),
				'event_desc'          => 'This automation would trigger when an offer is rejected by the user.',
				'slug'                => 'wf_offer_rejected',
				'is_time_independent' => false,
				'excluded_actions'    => array(),
				'event_saved_data'    => array(),
				'available'           => 'no',
			),
		);
	}

	/**
	 * Get Autonami notifications only
	 *
	 * @return array
	 */
	public static function get_autonami_notifications() {
		if ( ! class_exists( 'WooFunnels_Notifications' ) ) {
			return array();
		}
		$notifications_list = WooFunnels_Notifications::get_instance()->get_all_notifications();
		if ( ! is_array( $notifications_list ) || ! isset( $notifications_list['bwfan'] ) ) {
			return array();
		}
		if ( ! is_array( $notifications_list['bwfan'] ) || count( $notifications_list['bwfan'] ) === 0 ) {
			return array();
		}

		return array(
			'bwfan' => $notifications_list['bwfan'],
		);
	}

	/**
	 * Used as fallback to make sure all the tasks run on the thank you page
	 */
	public static function hit_cron_to_run_tasks() {
		$url  = rest_url( '/autonami/v1/autonami-cron' );
		$args = bwf_get_remote_rest_args( array(), 'GET' );
		wp_remote_post( $url, $args );
	}

	public static function add_ordinal_number_suffix( $num ) {
		if ( ! in_array( ( $num % 100 ), array( 11, 12, 13 ), true ) ) {
			switch ( $num % 10 ) {
				// Handle 1st, 2nd, 3rd
				case 1:
					return $num . 'st';
				case 2:
					return $num . 'nd';
				case 3:
					return $num . 'rd';
			}
		}

		return $num . 'th';
	}

	public static function bwfan_recipe_list_template() {
		ob_start();

		include plugin_dir_path( __FILE__ ) . 'bwfan-recipe-list-template.php';

		return ob_get_clean();
	}

	public static function auto_apply_wc_coupon() {
		if ( ! isset( $_GET['bwfan-coupon'] ) || empty( $_GET['bwfan-coupon'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			return;
		}
		$coupon_code = $_GET['bwfan-coupon']; //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput

		if ( WC()->cart instanceof WC_Cart && ! empty( $coupon_code ) && ! WC()->cart->has_discount( $coupon_code ) ) {
			/** Keep existing wc notices */
			$wc_notices = wc_get_notices();

			WC()->cart->add_discount( $coupon_code );

			/** Add all old wc notices back */
			WC()->session->set( 'wc_notices', $wc_notices );
		}
	}

	public static function get_entity_nice_name( $key = 'source', $slug = 'wc' ) {
		$nice_names = self::get_default_event_action_names();
		if ( empty( $key ) || empty( $slug ) ) {
			return '';
		}
		if ( ! isset( $nice_names[ $key ] ) || ! isset( $nice_names[ $key ][ $slug ] ) ) {
			return '';
		}

		return $nice_names[ $key ][ $slug ];
	}

	/**
	 * Return Sources, Events, Integrations, Actions nice names.
	 * Useful when respective entity not exist but data as task or automation available.
	 *
	 * @return array
	 */
	public static function get_default_event_action_names() {
		return array(
			'source'      => array(
				'wc'             => __( 'WooCommerce', 'wp-marketing-automations' ),
				'wp'             => __( 'WordPress', 'wp-marketing-automations' ),
				'wcs'            => __( 'WooCommerce Subscription', 'wp-marketing-automations' ),
				'upstroke'       => __( 'UpStroke', 'wp-marketing-automations' ),
				'activecampaign' => __( 'Active Campaign', 'wp-marketing-automations' ),
				'drip'           => __( 'Drip', 'wp-marketing-automations' ),
				'affwp'          => __( 'AffiliateWp', 'wp-marketing-automations' ),
				'gf'             => __( 'Gravity Forms', 'wp-marketing-automations' ),
			),
			'event'       => array(
				'wc_comment_post'          => __( 'New Review', 'wp-marketing-automations' ),
				'wc_new_order'             => __( 'Order Created', 'wp-marketing-automations' ),
				'wc_order_note_added'      => __( 'Order Note Added', 'wp-marketing-automations' ),
				'wc_order_status_change'   => __( 'Order Status Changed', 'wp-marketing-automations' ),
				'wc_product_purchased'     => __( 'Order Created - Per Item', 'wp-marketing-automations' ),
				'wc_product_refunded'      => __( 'Order Item Refunded', 'wp-marketing-automations' ),
				'wc_product_stock_reduced' => __( 'Order Item Stock Reduced', 'wp-marketing-automations' ),
				'wc_customer_win_back'     => __( 'Customer Win Back', 'wp-marketing-automations' ),
				'ab_cart_abandoned'        => __( 'Cart Abandoned', 'wp-marketing-automations' ),
				'ab_cart_recovered'        => __( 'Cart Recovered', 'wp-marketing-automations' ),
				'wp_user_creation'         => __( 'User Creation', 'wp-marketing-automations' ),
				'wp_user_login'            => __( 'User Login', 'wp-marketing-automations' ),

				'ac_webhook_received'   => __( 'Webhook Received', 'wp-marketing-automations' ),
				'drip_webhook_received' => __( 'Webhook Received', 'wp-marketing-automations' ),

				'upstroke_funnel_started'       => __( 'Funnel Started', 'wp-marketing-automations' ),
				'upstroke_funnel_ended'         => __( 'Funnel Ended', 'wp-marketing-automations' ),
				'upstroke_offer_viewed'         => __( 'Offer Viewed', 'wp-marketing-automations' ),
				'upstroke_product_accepted'     => __( 'Offer Accepted', 'wp-marketing-automations' ),
				'upstroke_offer_rejected'       => __( 'Offer Rejected', 'wp-marketing-automations' ),
				'upstroke_offer_payment_failed' => __( 'Offer Payment Failed', 'wp-marketing-automations' ),

				'wcs_created'                  => __( 'Subscriptions Created', 'wp-marketing-automations' ),
				'wcs_status_changed'           => __( 'Subscriptions Status Changed', 'wp-marketing-automations' ),
				'wcs_trial_end'                => __( 'Subscriptions Trial End', 'wp-marketing-automations' ),
				'wcs_before_renewal'           => __( 'Subscriptions Before Renewal', 'wp-marketing-automations' ),
				'wcs_before_end'               => __( 'Subscriptions Before End', 'wp-marketing-automations' ),
				'wcs_renewal_payment_complete' => __( 'Subscriptions Renewal Payment Complete', 'wp-marketing-automations' ),
				'wcs_renewal_payment_failed'   => __( 'Subscriptions Renewal Payment Failed', 'wp-marketing-automations' ),
				'wcs_card_expiry'              => __( 'Customer Before Card Expiry', 'wp-marketing-automations' ),

				'affwp_affiliate_report'     => __( 'Affiliate Digests', 'wp-marketing-automations' ),
				'affwp_application_approved' => __( 'Application Approved', 'wp-marketing-automations' ),
				'affwp_application_rejected' => __( 'Application Rejected', 'wp-marketing-automations' ),
				'affwp_makes_sale'           => __( 'Affiliate Makes A Sale', 'wp-marketing-automations' ),
				'affwp_referral_rejected'    => __( 'Referral Rejected', 'wp-marketing-automations' ),
				'affwp_signup'               => __( 'Application Sign Up', 'wp-marketing-automations' ),

				'gf_form_submit' => __( 'Form Submits', 'wp-marketing-automations' ),

			),
			'integration' => array(
				'wc'             => __( 'WooCommerce', 'wp-marketing-automations' ),
				'wp'             => __( 'WordPress', 'wp-marketing-automations' ),
				'wp_adv'         => __( 'WordPress Advanced', 'wp-marketing-automations' ),
				'zapier'         => __( 'Zapier', 'wp-marketing-automations' ),
				'activecampaign' => __( 'ActiveCampaign', 'wp-marketing-automations' ),
				'convertkit'     => __( 'ConvertKit', 'wp-marketing-automations' ),
				'drip'           => __( 'Drip', 'wp-marketing-automations' ),
				'slack'          => __( 'Slack', 'wp-marketing-automations' ),
				'twilio'         => __( 'Twilio', 'wp-marketing-automations' ),
				'google_sheets'  => __( 'Google Sheets', 'wp-marketing-automations' ),
			),
			'action'      => array(
				'wc_create_coupon'       => __( 'Create Coupon', 'wp-marketing-automations' ),
				'wc_add_order_note'      => __( 'Add Order Note', 'wp-marketing-automations' ),
				'wc_change_order_status' => __( 'Change Order Status', 'wp-marketing-automations' ),
				'wc_remove_coupon'       => __( 'Delete Coupon', 'wp-marketing-automations' ),

				'wp_sendemail' => __( 'Send Email', 'wp-marketing-automations' ),

				'za_send_data' => __( 'Send Data To Zapier', 'wp-marketing-automations' ),

				'wp_custom_callback'  => __( 'Custom Callback', 'wp-marketing-automations' ),
				'wp_debug'            => __( 'Debug', 'wp-marketing-automations' ),
				'wp_http_post'        => __( 'HTTP Post', 'wp-marketing-automations' ),
				'wp_createuser'       => __( 'Create User', 'wp-marketing-automations' ),
				'wp_update_user_meta' => __( 'Update User Meta', 'wp-marketing-automations' ),

				'ac_add_tag'               => __( 'Add Tags', 'wp-marketing-automations' ),
				'ac_add_to_automation'     => __( 'Add Contact To Automation', 'wp-marketing-automations' ),
				'ac_add_to_list'           => __( 'Add Contact To List', 'wp-marketing-automations' ),
				'ac_create_abandoned_cart' => __( 'Create Abandoned Cart', 'wp-marketing-automations' ),
				'ac_create_deal_note'      => __( 'Create Deal Note', 'wp-marketing-automations' ),
				'ac_create_deal'           => __( 'Create Deal', 'wp-marketing-automations' ),
				'ac_create_order'          => __( 'Create Order', 'wp-marketing-automations' ),
				'ac_rmv_from_automation'   => __( 'End Automation', 'wp-marketing-automations' ),
				'ac_rmv_from_list'         => __( 'Remove Contact From List', 'wp-marketing-automations' ),
				'ac_rmv_tag'               => __( 'Remove Tags', 'wp-marketing-automations' ),
				'ac_update_customfields'   => __( 'Update Fields', 'wp-marketing-automations' ),
				'ac_update_deal'           => __( 'Update Deal', 'wp-marketing-automations' ),

				'ck_add_customfields'  => __( 'Update Custom Fields', 'wp-marketing-automations' ),
				'ck_add_order'         => __( 'Create A New Purchase', 'wp-marketing-automations' ),
				'ck_add_tags'          => __( 'Add Tags', 'wp-marketing-automations' ),
				'ck_add_to_sequence'   => __( 'Add Subscriber To Sequence', 'wp-marketing-automations' ),
				'ck_rmv_from_sequence' => __( 'Remove Subscriber from Sequence', 'wp-marketing-automations' ),
				'ck_rmv_tags'          => __( 'Remove Tags', 'wp-marketing-automations' ),

				'dr_add_cart'          => __( 'Cart Activity', 'wp-marketing-automations' ),
				'dr_add_customfields'  => __( 'Update Custom fields of Subscriber', 'wp-marketing-automations' ),
				'dr_add_order'         => __( 'Add A New Order', 'wp-marketing-automations' ),
				'dr_add_tags'          => __( 'Add Tags', 'wp-marketing-automations' ),
				'dr_add_to_campaign'   => __( 'Add Subscriber to Campaign', 'wp-marketing-automations' ),
				'dr_add_to_workflow'   => __( 'Add Subscriber to Workflow', 'wp-marketing-automations' ),
				'dr_rmv_from_campaign' => __( 'Remove Subscriber from Campaign', 'wp-marketing-automations' ),
				'dr_rmv_from_workflow' => __( 'Remove Subscriber from Workflow', 'wp-marketing-automations' ),
				'dr_rmv_tags'          => __( 'Remove Tags', 'wp-marketing-automations' ),

				'sl_message_user' => __( 'Sends a message to a user', 'wp-marketing-automations' ),
				'sl_message'      => __( 'Sends a message to a channel', 'wp-marketing-automations' ),

				'twilio_send_sms' => __( 'Send SMS', 'wp-marketing-automations' ),

				'gs_insert_data' => __( 'Insert Row', 'wp-marketing-automations' ),
				'gs_update_data' => __( 'Update Row', 'wp-marketing-automations' ),

				'wcs_change_subscription_status' => __( 'Change Subscription Status', 'wp-marketing-automations' ),
				'wcs_send_subscription_invoice'  => __( 'Send Subscription Invoice', 'wp-marketing-automations' ),

				'affwp_change_affiliate_rate'  => __( 'Change Affiliate Rate', 'wp-marketing-automations' ),
				'affwp_change_referral_status' => __( 'Change Referral Status', 'wp-marketing-automations' ),
			),
		);
	}

	public static function mark_automation_require_update( $automation_id, $state = true ) {
		if ( empty( $automation_id ) ) {
			return;
		}

		$meta_data = array(
			'meta_value' => ( true === $state ) ? 1 : 0,
		);
		$where     = array(
			'bwfan_automation_id' => $automation_id,
			'meta_key'            => 'requires_update',
		);
		BWFAN_Model_Automationmeta::update( $meta_data, $where );
	}

	/**
	 * checking plugin dependency
	 *
	 * @param $plugin_depend
	 *
	 * @return array|bool
	 */
	public static function plugin_dependency_check( $plugin_depend ) {
		if ( empty( $plugin_depend ) ) {
			return true;
		}
		$plugin_error = array();
		foreach ( $plugin_depend as $plugins ) {
			$function_name = 'bwfan_is_' . $plugins . '_active';

			/** checking if function exists */
			if ( ! function_exists( $function_name ) ) {
				continue;
			}

			if ( false === $function_name() ) {
				$nice_name      = self::plugin_dependency_nice_names( $plugins );
				$plugin_error[] = "{$nice_name} plugin is not active.";
			}
		}

		return empty( $plugin_error ) ? true : $plugin_error;
	}

	public static function plugin_dependency_nice_names( $slug ) {
		switch ( $slug ) {
			case 'woocommerce':
				$slug = 'WooCommerce';
				break;
			case 'edd':
				$slug = 'Easy Digital Downloads';
				break;
			case 'woocommerce_subscriptions':
				$slug = 'WooCommerce Subscriptions';
				break;
			case 'woocommerce_membership':
				$slug = 'WooCommerce Membership';
				break;
			case 'woofunnels_upstroke':
				$slug = 'UpStroke: WooCommerce One Click Upsells';
				break;
			case 'autonami_pro':
				$slug = 'Autonami Marketing Automations Pro';
				break;
			case 'autonami_connector':
				$slug = 'Autonami Marketing Automations Connectors';
				break;
			case 'affiliatewp':
				$slug = 'AffiliateWP';
				break;
		}

		return $slug;
	}

	public static function create_automation_on_activation( $json_files ) {
		global $wpdb;
		$automation_table      = $wpdb->prefix . 'bwfan_automations';
		$automation_meta_table = $wpdb->prefix . 'bwfan_automationmeta';
		$json_file_path        = plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'admin/json/';

		foreach ( $json_files as $file ) {
			// check file type
			$file_path = $json_file_path . '' . $file;
			if ( 'json' !== pathinfo( $file_path, PATHINFO_EXTENSION ) ) {
				continue;
			}

			$import_file_data = json_decode( file_get_contents( $file_path ), true );

			foreach ( $import_file_data as $import_data ) {
				if ( empty( $import_data['data'] ) || ! isset( $import_data['meta']['title'] ) || '' === $import_data['meta']['title'] ) {
					continue;
				}

				$post             = array();
				$post['status']   = 2;
				$post['source']   = isset( $import_data['data']['source'] ) ? $import_data['data']['source'] : '';
				$post['event']    = isset( $import_data['data']['event'] ) ? $import_data['data']['event'] : '';
				$post['priority'] = 0;

				$wpdb->insert( $automation_table, $post );
				$automation_id = $wpdb->insert_id;
				if ( 0 === $automation_id || is_wp_error( $automation_id ) ) {
					continue;
				}

				if ( ! empty( $import_data['meta'] ) ) {
					foreach ( $import_data['meta'] as $key => $auto_meta ) {
						if ( is_array( $auto_meta ) ) {
							$auto_meta = maybe_serialize( $auto_meta );
						}

						$meta = array(
							'bwfan_automation_id' => $automation_id,
							'meta_key'            => $key,
							'meta_value'          => $auto_meta,
						);
						$wpdb->insert( $automation_meta_table, $meta );
					}
				}

				$meta = array(
					'bwfan_automation_id' => $automation_id,
					'meta_key'            => 'c_date',
					'meta_value'          => current_time( 'mysql', 1 ),
				);
				$wpdb->insert( $automation_meta_table, $meta );

				$meta = array(
					'bwfan_automation_id' => $automation_id,
					'meta_key'            => 'm_date',
					'meta_value'          => current_time( 'mysql', 1 ),
				);
				$wpdb->insert( $automation_meta_table, $meta );

				do_action( 'bwfan_automation_saved', $automation_id );
			}
		}
	}

	/**
	 * Load Hooks after Action Scheduler is loaded
	 */
	public static function bwf_after_action_scheduler_load() {
		/** Schedule WP cron event */
		add_action( 'admin_init', array( __CLASS__, 'maybe_set_bwf_ct_worker' ) );
	}

	/**
	 * Scheduling event with core callback
	 */
	public static function maybe_set_bwf_ct_worker() {
		if ( ! wp_next_scheduled( 'bwf_as_run_queue' ) ) {
			wp_schedule_event( time(), 'bwf_every_minute', 'bwf_as_run_queue' );
		}
	}

	public static function hide_free_products_cart_order_items() {
		return apply_filters( 'bwfan_items_display_hide_free_products', false );
	}

	public static function get_global_email_settings() {
		$global_settings = array();

		/** Email Settings */

		if ( bwfan_is_woocommerce_active() ) {
			$global_settings['bwfan_email_from'] = get_option( 'woocommerce_email_from_address' );
			$global_settings['bwfan_email_from'] = sanitize_email( $global_settings['bwfan_email_from'] );
		} else {
			$global_settings['bwfan_email_from'] = sanitize_email( get_bloginfo( 'admin_email' ) );
		}

		if ( bwfan_is_woocommerce_active() ) {
			$global_settings['bwfan_email_from_name'] = get_option( 'woocommerce_email_from_name' );
			$global_settings['bwfan_email_from_name'] = wp_specialchars_decode( esc_html( $global_settings['bwfan_email_from_name'] ), ENT_QUOTES );
		} else {
			$global_settings['bwfan_email_from_name'] = wp_specialchars_decode( esc_html( get_bloginfo( 'name' ) ), ENT_QUOTES );
		}

		$global_settings['bwfan_email_reply_to']         = $global_settings['bwfan_email_from'];
		$global_settings['bwfan_email_per_second_limit'] = 15;
		$global_settings['bwfan_email_daily_limit']      = 10000;

		return array(
			'bwfan_email_from'             => $global_settings['bwfan_email_from'],
			'bwfan_email_from_name'        => $global_settings['bwfan_email_from_name'],
			'bwfan_email_reply_to'         => $global_settings['bwfan_email_reply_to'],
			'bwfan_email_per_second_limit' => $global_settings['bwfan_email_per_second_limit'],
			'bwfan_email_daily_limit'      => $global_settings['bwfan_email_daily_limit'],
		);
	}

	/**
	 * Return if emogrifier library is supported.
	 *
	 * @return bool
	 * @since 3.5.0
	 */
	public static function supports_emogrifier() {
		return class_exists( 'DOMDocument' ) && version_compare( PHP_VERSION, '5.5', '>=' );
	}

	public static function color_light_or_dark( $color, $dark = '#000000', $light = '#FFFFFF' ) {
		return self::color_hex_is_light( $color ) ? $dark : $light;
	}

	public static function color_hex_is_light( $color ) {
		$hex = str_replace( '#', '', $color );

		$c_r = hexdec( substr( $hex, 0, 2 ) );
		$c_g = hexdec( substr( $hex, 2, 2 ) );
		$c_b = hexdec( substr( $hex, 4, 2 ) );

		$brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

		return $brightness > 155;
	}

	public static function get_latest_order_by_email( $email ) {
		if ( ! is_email( $email ) ) {
			return false;
		}
		if ( ! bwfan_is_woocommerce_active() ) {
			return false;
		}

		global $wpdb;

		$last_order = $wpdb->get_var( "SELECT posts.ID
            FROM $wpdb->posts AS posts
            LEFT JOIN {$wpdb->postmeta} AS meta on posts.ID = meta.post_id
            WHERE meta.meta_key = '_billing_email'
            AND   meta.meta_value = '" . $email . "'
            AND   posts.post_type = 'shop_order'
            AND   posts.post_status IN ( '" . implode( "','", array_map( 'esc_sql', array_keys( wc_get_order_statuses() ) ) ) . "' )
            ORDER BY posts.ID DESC" );

		if ( ! $last_order ) {
			return false;
		}

		return wc_get_order( absint( $last_order ) );
	}

	public static function add_user_consent_after_terms_and_conditions( $return = false, $field_priority = '' ) {
		$global_settings = self::get_global_settings();

		$marketing_status = 0;
		if ( empty( $global_settings['bwfan_user_consent'] ) ) {
			$marketing_status = 1;
		}

		if ( empty( $marketing_status ) && is_user_logged_in() ) {
			$user        = wp_get_current_user();
			$bwf_contact = bwf_get_contact( $user->ID, $user->user_email );

			if ( isset( $bwf_contact->meta->marketing_status ) && 1 === absint( $bwf_contact->meta->marketing_status ) ) {
				$marketing_status = 1;
			}
		}

		$country_code            = self::maybe_get_user_country_code();
		$tax_supported_countries = WC()->countries->get_european_union_countries();
		$check                   = in_array( $country_code, $tax_supported_countries, true );

		if ( true === $check ) {
			$checked = 'checked';
			if ( empty( $global_settings['bwfan_user_consent_eu'] ) ) {
				$checked = '';
			}
		} else {
			$checked = 'checked';
			if ( empty( $global_settings['bwfan_user_consent_non_eu'] ) ) {
				$checked = '';
			}
		}

		if ( ! $return ) {
			if ( 1 === $marketing_status ) {
				echo '<input id="bwfan_user_consent" name="bwfan_user_consent" value="1" type="hidden" />';

				return;
			}
			echo '<p class="bwfan_user_consent wfacp-col-full wfacp-consent-term-condition form-row">';
			echo '<label for="bwfan_user_consent" class="bwfan-label-title">';
			echo '<input id="bwfan_user_consent" name="bwfan_user_consent" type="checkbox" value="1" ' . esc_html( $checked ) . ' />';
			echo wp_kses_post( $global_settings['bwfan_user_consent_message'] );
			echo '</label>';
			echo '</p>';
		} else {
			if ( 1 === $marketing_status ) {
				return '<input id="bwfan_user_consent" name="bwfan_user_consent" value="1" type="hidden" />';
			}
			$field_priority = ! empty( $field_priority ) ? 'data-priority="' . ( absint( $field_priority ) + 5 ) . '"' : '';
			$return         = '<p class="bwfan_user_consent wfacp-col-full wfacp-consent-term-condition form-row" ' . $field_priority . '>';
			$return         .= '<label for="bwfan_user_consent" class="bwfan-label-title">';
			$return         .= '<input id="bwfan_user_consent" name="bwfan_user_consent" type="checkbox" value="1" ' . esc_html( $checked ) . ' />';
			$return         .= wp_kses_post( $global_settings['bwfan_user_consent_message'] );
			$return         .= '</label>';
			$return         .= '</p>';

			return $return;
		}
	}

	public static function maybe_clear_cache() {

		/**
		 * Clear WordPress cache
		 */
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		/**
		 * Checking if wp fastest cache installed
		 * Clear cache of wp fastest cache
		 */
		if ( class_exists( 'WpFastestCache' ) ) {
			global $wp_fastest_cache;
			if ( method_exists( $wp_fastest_cache, 'deleteCache' ) ) {
				$wp_fastest_cache->deleteCache();
			}

			// clear all cache
			if ( function_exists( 'wpfc_clear_all_cache' ) ) {
				wpfc_clear_all_cache( true );
			}
		}

		/**
		 * Checking if wp Autoptimize installed
		 * Clear cache of Autoptimize
		 */
		if ( class_exists( 'autoptimizeCache' ) ) {
			autoptimizeCache::clearall();
		}

		/**
		 * Checking if W3Total Cache plugin activated.
		 * Clear cache of W3Total Cache plugin
		 */
		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
		}
	}

	/**
	 * Updating marketing_status in order meta
	 *
	 * @param $order_id
	 * @param $posted_data
	 * @param WC_Order $order
	 */
	public static function bwfan_update_order_user_consent( $order ) {
		$marketing_status = isset( $_POST['bwfan_user_consent'] ) ? absint( $_POST['bwfan_user_consent'] ) : 0;
		$order->update_meta_data( 'marketing_status', $marketing_status );
	}

	/** updating contact marketing status
	 *
	 * @param WooFunnels_Contact $contact
	 * @param int $order_id
	 */
	public static function save_marketing_status_for_user( $contact, $order_id ) {
		$order = wc_get_order( absint( $order_id ) );
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$marketing_status = $order->get_meta( 'marketing_status', true );
		if ( '' === $marketing_status ) {
			return;
		}

		$contact->set_status( absint( $marketing_status ) );
		$contact->save();
	}

	public static function get_form_submit_events() {
		return apply_filters( 'bwfan_get_form_submit_events', array( 'BWFAN_CF7_Form_Submit' ) );
	}

	/** get all automations data for the api
	 *
	 * @param string $status
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function get_all_automations( $search, $status = 'all', $offset = 0, $limit = 25, $only_count = false ) {
		global $wpdb;
		$automations_table = $wpdb->prefix . 'bwfan_automations';
		$base_query        = array();
		$count_query       = array();
		$base_query[]      = "SELECT  distinct a.* FROM $automations_table as a LEFT JOIN {$wpdb->prefix}bwfan_automationmeta as am ON a.ID = am.bwfan_automation_id where 1=1 ";
		$count_query[]     = "SELECT count(DISTINCT a.ID) FROM $automations_table as a LEFT JOIN {$wpdb->prefix}bwfan_automationmeta as am ON a.ID = am.bwfan_automation_id where 1=1 ";
		if ( ! empty( $search ) && $only_count === false ) {
			$search        = "%$search%";
			$search_query  = $wpdb->prepare( " AND am.meta_key='title' AND am.meta_value like %s", $search );
			$base_query[]  = $search_query;
			$count_query[] = $search_query;
		}

		if ( $status !== 'all' ) {
			$status_query  = $wpdb->prepare( 'AND status = %d', $status );
			$base_query[]  = $status_query;
			$count_query[] = $status_query;
		}
		$base_query[] = $wpdb->prepare( ' ORDER BY a.ID DESC LIMIT %d OFFSET %d', $limit, $offset );
		if ( $only_count === false ) {
			$all_automations = $wpdb->get_results( implode( ' ', $base_query ), ARRAY_A );
		}
		$overall_total = $wpdb->get_var( implode( ' ', $count_query ) );
		if ( $only_count === true ) {
			return array(
				'automations'   => array(),
				'total_records' => $overall_total,
			);
		}

		if ( empty( $all_automations ) ) {
			return array(
				'automations'   => array(),
				'total_records' => 0,
			);
		}

		$final_automation_data = array();
		$date_format           = self::get_date_format();

		$automation_ids = array_map( function ( $all_automation ) {
			return isset( $all_automation['ID'] ) ? absint( $all_automation['ID'] ) : false;
		}, $all_automations );
		$ids            = implode( ',', array_filter( $automation_ids ) );

		/** Get all automations revenue total */
		$query           = "SELECT oid, count(ID) as conversions, SUM(wctotal) as revenue FROM {$wpdb->prefix}bwfan_conversions WHERE oid IN ($ids) AND otype=1 GROUP BY oid";
		$conversions     = $wpdb->get_results( $query, ARRAY_A );
		$conversion_data = array();
		foreach ( $conversions as $conversion ) {
			if ( absint( $conversion['oid'] ) ) {
				$conversion_data[ absint( $conversion['oid'] ) ] = $conversion;
			}
		}

		/** Get all scheduled and paused task count */
		$tasks_table = "{$wpdb->prefix}bwfan_tasks";
		$tasks_query = "SELECT  automation_id,count(ID) as total_scheduled,status FROM $tasks_table WHERE automation_id IN ($ids) GROUP BY automation_id,status";
		$tasks       = $wpdb->get_results( $tasks_query, ARRAY_A );
		$total_tasks = array();
		foreach ( $tasks as $automation_tasks ) {
			$status = absint( $automation_tasks['status'] ) === 1 ? 'paused' : 'scheduled';
			if ( absint( $automation_tasks['automation_id'] ) ) {
				$total_tasks[ $status ][ absint( $automation_tasks['automation_id'] ) ] = $automation_tasks['total_scheduled'];
			}
		}

		/** Get completed and failed task count */
		$logs_table = "{$wpdb->prefix}bwfan_logs";
		$logs_query = "SELECT  automation_id,count(ID) total_logs,status FROM $logs_table WHERE automation_id IN ($ids) AND (status = 0 OR status = 1) GROUP BY automation_id,status";
		$logs       = $wpdb->get_results( $logs_query, ARRAY_A );
		$total_logs = array();
		foreach ( $logs as $automation_logs ) {
			$status = absint( $automation_logs['status'] ) === 1 ? 'completed' : 'failed';
			if ( absint( $automation_logs['automation_id'] ) ) {
				$total_logs[ $status ][ absint( $automation_logs['automation_id'] ) ] = $automation_logs;
			}
		}

		foreach ( $all_automations as $automation ) {
			$automation_tasks = array();
			$id               = absint( $automation['ID'] );

			$automation_tasks['scheduled'] = isset( $total_tasks['scheduled'][ $id ] ) ? $total_tasks['scheduled'][ $id ] : 0;
			$automation_tasks['paused']    = isset( $total_tasks['paused'][ $id ] ) && absint( $automation['status'] ) === 2 ? $total_tasks['paused'][ $id ] : 0;
			$automation_tasks['completed'] = isset( $total_logs['completed'][ $id ]['total_logs'] ) ? $total_logs['completed'][ $id ]['total_logs'] : 0;
			$automation_tasks['failed']    = isset( $total_logs['failed'][ $id ]['total_logs'] ) ? $total_logs['failed'][ $id ]['total_logs'] : 0;

			$automation_meta    = BWFAN_Model_Automationmeta::get_automation_meta( $id );
			$automation_actions = isset( $automation_meta['actions'] ) ? self::get_automation_actions( $automation_meta['actions'] ) : array();
			$source             = self::get_automation_source_name( $automation['source'] );
			$event              = self::get_automation_event_name( $automation['event'] );
			$data               = array(
				'id'                 => $id,
				'source'             => empty( $source ) ? __( 'Not Found', 'wp-marketing-automations' ) : $source,
				'last_update'        => isset( $automation_meta['m_date'] ) ? get_date_from_gmt( $automation_meta['m_date'], $date_format ) : '',
				'name'               => isset( $automation_meta['title'] ) ? $automation_meta['title'] : '',
				'event'              => $event,
				'status'             => $automation['status'],
				'priority'           => $automation['priority'],
				'automation_actions' => $automation_actions,
				'run_count'          => ( isset( $automation_meta['run_count'] ) ) ? $automation_meta['run_count'] : 0,
				'tasks_count'        => self::get_automation_task_details( $id, $automation_tasks ),
				'conversions'        => 0,
				'revenue'            => 0,
			);

			if ( isset( $conversion_data[ $id ] ) ) {
				$data = array_replace( $data, $conversion_data[ $id ] );
			}

			$final_automation_data[] = $data;
		}

		return array(
			'automations'   => ! empty( $final_automation_data ) && is_array( $final_automation_data ) ? array_values( $final_automation_data ) : array(),
			'total_records' => ! empty( $overall_total ) ? absint( $overall_total ) : 0,
		);
	}

	/**
	 * @param $automation_actions
	 *
	 * @return array
	 */
	public static function get_automation_actions( $automation_actions ) {
		$actions = array();
		if ( empty( $automation_actions ) ) {
			return array();
		}

		$integration_data = $automation_actions;
		$unique_actions   = BWFAN_Core()->automations->get_unique_automation_actions( $integration_data );

		foreach ( $unique_actions as $action => $integration ) {
			$action_obj      = BWFAN_Core()->integration->get_action( $action );
			$integration_obj = BWFAN_Core()->integration->get_integration( $integration );
			if ( $integration_obj instanceof BWFAN_Integration && $action_obj instanceof BWFAN_Action ) {
				$nice_name               = $integration_obj->get_name();
				$actions[ $nice_name ][] = $action_obj->get_name();
			} else {
				$integration_name = self::get_entity_nice_name( 'integration', $integration );
				$action_name      = self::get_entity_nice_name( 'action', $action );
				if ( ! empty( $integration_name ) && ! empty( $action_name ) ) {
					$actions[ $integration_name ][] = $action_name;
				}
			}
		}

		return $actions;
	}

	/**get automation source name using source_slug
	 *
	 * @param $source
	 *
	 * @return mixed|string
	 */
	public static function get_automation_source_name( $source ) {
		if ( empty( $source ) ) {
			return '';
		}

		$source_name = '';

		$single_source = BWFAN_Core()->sources->get_source( $source );
		if ( $single_source instanceof BWFAN_Source ) {
			$source_name = $single_source->get_name();
		} else {
			$source_name = self::get_entity_nice_name( 'source', $source );
		}

		return $source_name;
	}

	/** get automation event name using event_slug
	 *
	 * @param $event
	 *
	 * @return mixed|string
	 */
	public static function get_automation_event_name( $event ) {
		if ( empty( $event ) ) {
			return '';
		}

		$event_name = '';

		$single_event = BWFAN_Core()->sources->get_event( $event );
		if ( $single_event instanceof BWFAN_Event ) {
			$event_name = $single_event->get_name();
		} else {
			$event_name = self::get_entity_nice_name( 'event', $event );
		}

		return $event_name;
	}

	/**
	 * @param $automation_id
	 * @param $automation_tasks
	 *
	 * @return array
	 */
	public static function get_automation_task_details( $automation_id, $automation_tasks ) {
		$output = array();
		foreach ( $automation_tasks as $key => $count ) {
			$output[] = array(
				'count' => absint( $count ),
				'name'  => ucfirst( $key ),
			);
		}

		return $output;
	}

	/**
	 * @param $search
	 * @param $offset
	 * @param $limit
	 */
	public static function get_unsubscribers( $search, $offset, $limit ) {
		global $wpdb;
		$where = '';
		/** Check for search unsubscriber */
		if ( ! empty( $search ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$where = "WHERE `recipient` LIKE '%" . $search . "%'"; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		/** Query to fetch unsubscribers data from DB */
		$unsubscribers = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bwfan_message_unsubscribe $where ORDER BY ID DESC LIMIT $offset,$limit " );//phpcs:ignore WordPress.DB.PreparedSQL
		if ( empty( $unsubscribers ) ) {
			return array();
		}

		$found_posts = array();
		$items       = array();

		foreach ( $unsubscribers as $unsubscriber ) {
			$c_type = absint( $unsubscriber->c_type );
			$oid    = empty( $unsubscriber->automation_id ) ? 0 : absint( $unsubscriber->automation_id );
			$otype  = '';
			$oname  = '';

			if ( 1 === $c_type ) {
				$automation_meta = BWFAN_Core()->automations->get_automation_data_meta( $oid );
				$oname           = isset( $automation_meta['title'] ) && ! empty( $automation_meta['title'] ) ? $automation_meta['title'] : __( 'No Title', 'wp-marketing-automations' );
				$otype           = __( 'Automation', 'wp-marketing-automations' );
			} elseif ( 2 === $c_type ) {
				$broadcast = bwfan_is_autonami_pro_active() ? BWFAN_Model_Broadcast::get( $oid ) : array();
				$oname     = isset( $broadcast['title'] ) ? $broadcast['title'] : __( 'No Title', 'wp-marketing-automations' );
				$otype     = __( 'Broadcast', 'wp-marketing-automations' );
			} elseif ( $c_type > 2 ) {
				$otype = __( 'Manual', 'wp-marketing-automations' );
			}

			$items[] = array(
				'id'              => $unsubscriber->ID,
				'recipient'       => $unsubscriber->recipient,
				'date'            => date( self::get_date_format(), strtotime( $unsubscriber->c_date ) ),
				'automation_id'   => $oid,
				'automation_name' => empty( $oname ) && empty( $otype ) ? '-' : ( empty( $oname ) ? $otype : "$otype: $oname" ),
				'source_type'     => $c_type,
			);
		}

		$found_posts['found_posts'] = BWFAN_Model_Message_Unsubscribe::count_rows();
		$found_posts['items']       = $items;

		return $found_posts;
	}

	/** run global tools
	 *
	 * @param $tool_type
	 *
	 * @return array
	 */
	public static function run_global_tools( $tool_type ) {
		global $wpdb;
		$result = array();

		switch ( $tool_type ) {
			case 'run_all_tasks':
				$result['msg'] = __( 'All queued tasks have been scheduled to run now', 'wp-marketing-automations' );
				$all_tasks     = BWFAN_Core()->tasks->get_all_tasks();
				if ( ! is_array( $all_tasks ) || 0 === count( $all_tasks ) ) {
					$result['msg']    = __( 'There were no tasks', 'wp-marketing-automations' );
					$result['status'] = false;

					return $result;
				}

				$task_ids = array();
				foreach ( $all_tasks as $task_id => $task_details ) {  //phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis
					$task_ids[] = $task_id;
				}
				BWFAN_Core()->tasks->rescheduled_tasks( true, $task_ids );

				break;
			case 'delete_completed_tasks':
				$logs = $wpdb->get_results( $wpdb->prepare( "
									SELECT ID
									FROM {$wpdb->prefix}bwfan_logs
									WHERE `status` = %d
									", 1 ) );

				if ( ! empty( $logs ) ) {
					$completed_tasks = array();
					foreach ( $logs as $log ) {
						$completed_tasks[] = $log->ID;
					}
					BWFAN_Core()->logs->delete_logs( $completed_tasks );
				}

				$result['msg'] = __( 'All completed tasks successfully deleted.', 'wp-marketing-automations' );

				break;
			case 'delete_failed_tasks':
				$logs = $wpdb->get_results( $wpdb->prepare( "
									SELECT ID
									FROM {$wpdb->prefix}bwfan_logs
									WHERE `status` = %d
									", 0 ) );

				if ( ! empty( $logs ) ) {
					$failed_tasks = array();
					foreach ( $logs as $log ) {
						$failed_tasks[] = $log->ID;
					}
					BWFAN_Core()->logs->delete_logs( $failed_tasks );
				}

				$result['msg'] = __( 'All failed tasks successfully deleted.', 'wp-marketing-automations' );

				break;
			case 'delete_previous_logs_automation':
				if ( false !== bwf_has_action_scheduled( 'bwfan_delete_older_logs' ) ) {
					$result['msg']    = __( 'A process is already scheduled for deleting old logs of this automation', 'wp-marketing-automations' );
					$result['status'] = false;

					return $result;
				}

				$data_inputs           = isset( $_POST['data_inputs'] ) ? json_decode( self::remove_backslashes( $_POST['data_inputs'] ), true ) : array(); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$automation_id         = $data_inputs['automation_id'];
				$result['msg']         = __( 'Process scheduled for deleting old logs of this automation', 'wp-marketing-automations' );
				$data                  = array();
				$data['days']          = apply_filters( 'bwfan_logs_days_deletion_limit', 15 );
				$data['limit']         = apply_filters( 'bwfan_logs_days_deletion_count', 200 );
				$data['automation_id'] = intval( $automation_id );

				bwf_schedule_single_action( time(), 'bwfan_delete_older_logs', $data );

				break;
			case 'delete_expired_coupons':
				if ( false !== bwf_has_action_scheduled( 'bwfan_delete_expired_coupons' ) ) {
					$result['msg']    = __( 'A process is already scheduled for deleting expired autonami generated coupons', 'wp-marketing-automations' );
					$result['status'] = false;

					return $result;
				}

				$result['msg'] = __( 'Process scheduled for deleting expired autonami generated coupons', 'wp-marketing-automations' );

				bwf_schedule_recurring_action( time(), 2, 'bwfan_delete_expired_coupons', array() );

				break;
			case 'delete_lost_carts':
				$wpdb->query( $wpdb->prepare( "
									DELETE
									FROM {$wpdb->prefix}bwfan_abandonedcarts
									WHERE `status` = %d
									", 2 ) );

				$result['msg'] = __( 'All lost carts successfully deleted.', 'wp-marketing-automations' );

				break;
			case 'test_connection':
				$url  = rest_url( '/autonami/v1/autonami-cron' );
				$args = array(
					'method'    => 'GET',
					'sslverify' => false,
					'debug'     => 'yes',
				);

				$response = wp_remote_post( $url, $args );
				if ( isset( $response['response']['code'] ) && 200 === $response['response']['code'] ) {
					$result['msg']    = __( 'Autonami endpoint is successfully working', 'wp-marketing-automations' );
					$result['status'] = true;
				} else {
					$result['msg']    = __( 'Failed to connect with Autonami endpoint. Please contact support.', 'wp-marketing-automations' );
					$result['status'] = false;
				}

				break;
		}

		return $result;
	}

	public static function get_lists_for_preferences() {
		if ( ! bwfan_is_autonami_pro_active() ) {
			return array();
		}

		$lists = BWFAN_Model_Terms::get_all( BWFCRM_Term_Type::$LIST );
		if ( empty( $lists ) ) {
			return array();
		}

		$lists_array = array();
		foreach ( $lists as $list ) {
			$lists_array[ $list['ID'] ] = $list['name'];
		}

		return $lists_array;
	}

	public static function get_lists_preference_schema() {
		if ( ! bwfan_is_autonami_pro_active() ) {
			return array();
		}

		return array(
			array(
				'id'            => 'bwfan_unsubscribe_lists_enable',
				'label'         => __( 'Manage Lists', 'wp-marketing-automations' ),
				'type'          => 'checkbox',
				'checkboxlabel' => __( 'Enable to allow Contacts to manage their lists subscription', 'wp-marketing-automations' ),
				'class'         => 'bwfan_unsubscribe_lists_enable',
				'wrap_before'   => '<br/><h3>Manage Lists</h3>',
				'required'      => false,
				'toggler'       => array(),
			),
			array(
				'id'       => 'bwfan_unsubscribe_public_lists',
				'label'    => __( 'Select Lists', 'wp-marketing-automations' ),
				'type'     => 'checkbox_grid',
				'hint'     => __( 'The selected lists will be shown to contacts for managing subscriptions', 'wp-marketing-automations' ),
				'class'    => 'bwfan_unsubscribe_public_lists',
				'options'  => self::get_lists_for_preferences(),
				'required' => false,
				'toggler'  => array(
					'fields'   => array(
						array(
							'id'    => 'bwfan_unsubscribe_lists_enable',
							'value' => true,
						),
					),
					'relation' => 'OR',
				),
			),
		);
	}

	/** get setting schema
	 *
	 * @return array[]
	 */
	public static function get_setting_schema() {
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}
		$editable_roles = get_editable_roles();
		$user_roles     = array();
		if ( $editable_roles ) {
			foreach ( $editable_roles as $role => $details ) {
				$name                         = translate_user_role( $details['name'] );
				$user_roles[ $role ]['label'] = $name;
				$user_roles[ $role ]['value'] = $role;
			}
		}
		$user_roles = array_values( $user_roles );

		$sms_options = array(
			array(
				'label' => 'Twilio',
				'value' => 'bwfco_twilio',
			),
			array(
				'label' => 'Bulkgate',
				'value' => 'bwfco_bulkgate',
			),
		);

		/** SMS Service Providers */
		$sms_options = self::get_sms_services();
		$sms_options = array_map( function ( $sms, $slug ) {
			return array(
				'label' => $sms,
				'value' => $slug,
			);
		}, $sms_options, array_keys( $sms_options ) );

		/** Email Service Providers */
		$email_options = self::get_email_services();
		$email_options = array_map( function ( $email, $slug ) {
			return array(
				'label' => $email,
				'value' => $slug,
			);
		}, $email_options, array_keys( $email_options ) );

		$show_fields = true;

		if ( function_exists( 'bwfan_is_autonami_pro_active' ) && ! bwfan_is_autonami_pro_active() ) {
			$show_fields = false;
		}

		$bounce_settings_schema = self::get_bounce_settings_schema();

		$email_field = array(
			array(
				'id'          => 'bwfan_email_from_name',
				'label'       => __( 'From Name', 'wp-marketing-automations' ),
				'type'        => 'text',
				'class'       => 'bwfan_email_from_name',
				'placeholder' => 'Enter Name',
				'hint'        => __( 'Name that will be used to send emails', 'wp-marketing-automations' ),
				'required'    => false,
				'toggler'     => array(),
			),
			array(
				'id'          => 'bwfan_email_from',
				'label'       => __( 'From Email', 'wp-marketing-automations' ),
				'type'        => 'text',
				'class'       => 'bwfan_email_from',
				'placeholder' => 'Enter Email',
				'hint'        => __( 'Valid email address that will be used to send emails', 'wp-marketing-automations' ),
				'required'    => false,
				'toggler'     => array(),
			),
			array(
				'id'          => 'bwfan_email_reply_to',
				'label'       => __( 'Reply To Email', 'wp-marketing-automations' ),
				'type'        => 'text',
				'class'       => 'bwfan_email_reply_to',
				'placeholder' => 'Enter Email',
				'hint'        => __( 'Valid email address that will be used to receive replies', 'wp-marketing-automations' ),
				'required'    => false,
				'toggler'     => array(),
			),
			array(
				'id'          => 'bwfan_email_per_second_limit',
				'label'       => __( 'Emails Limit Per Second', 'wp-marketing-automations' ),
				'type'        => 'number',
				'class'       => 'bwfan_email_per_second_limit',
				'placeholder' => '10',
				'hint'        => __( 'Maximum emails sending limit per second', 'wp-marketing-automations' ),
				'required'    => false,
				'show'        => $show_fields,
				'wrap_before' => '<h3>Sending Limit</h3>',
				'toggler'     => array(),
			),
			array(
				'id'          => 'bwfan_email_daily_limit',
				'label'       => __( 'Email Limit Per Day', 'wp-marketing-automations' ),
				'type'        => 'number',
				'class'       => 'bwfan_email_daily_limit ',
				'placeholder' => '15000',
				'hint'        => __( 'Maximum emails sending limit in last 24 hours', 'wp-marketing-automations' ),
				'required'    => false,
				'show'        => $show_fields,
				'toggler'     => array(),
			),
			array(
				'id'          => 'bwfan_email_footer_setting',
				'label'       => __( 'Unsubscribe Text', 'wp-marketing-automations' ),
				'type'        => 'richeditor',
				'class'       => 'bwfan_setting_business_name',
				'hint'        => '',
				'required'    => false,
				'wrap_before' => '<h3>Footer</h3>',
				'toggler'     => array(),
				'hint'        => __( 'Anti-spam laws require you to put a physical address and an unsubscribe link at the bottom of every email. Use dynamic tags <b>{{business_name}}</b> for Business Name', 'wp-marketing-automations' ) . ', ' . __( '<b>{{business_address}}</b> for Business Address', 'wp-marketing-automations' ) . ' and ' . __( '<b>{{unsubscribe_link}}</b> for Unsubscribe page link', 'wp-marketing-automations' ),
			),
		);

		if ( ! empty( $bounce_settings_schema ) ) {
			$email_field = array_merge( $email_field, $bounce_settings_schema );
		}

		/** added email provider setting in case of multiple provider */
		if ( count( $email_options ) > 1 ) {
			$email_provider_schema = array(
				array(
					'id'       => 'bwfan_email_service',
					'label'    => __( 'Default Email Provider', 'wp-marketing-automations' ),
					'type'     => 'select',
					'class'    => '',
					'options'  => $email_options,
					'required' => false,
					'multiple' => false,
					'hint'     => __( 'Select default provider for sending emails', 'wp-marketing-automations' ),
					'toggler'  => array(),
				),
			);
			$email_field           = array_merge( $email_field, $email_provider_schema );
		}

		$sms_fields = array(
			array(
				'id'          => 'bwfan_sms_unsubscribe_text',
				'label'       => __( 'Text', 'wp-marketing-automations' ),
				'type'        => 'text',
				'class'       => 'bwfan_sms_unsubscribe_text',
				'placeholder' => __( 'Enter Unsubscribe Text', 'wp-marketing-automations' ),
				'required'    => true,
				'wrap_before' => '<h3>Unsubscribe Text</h3>',
			),
		);

		/** added email provider setting in case of multiple provider */
		if ( count( $sms_options ) > 1 ) {
			$sms_provider_schema = array(
				array(
					'id'       => 'bwfan_sms_service',
					'label'    => __( 'Default SMS Provider', 'wp-marketing-automations' ),
					'type'     => 'select',
					'class'    => '',
					'options'  => $sms_options,
					'required' => false,
					'multiple' => false,
					'hint'     => __( 'Select default provider for sending SMS', 'wp-marketing-automations' ),
					'toggler'  => array(),
				),
			);
			$sms_fields          = array_merge( $sms_fields, $sms_provider_schema );
		};

		$settings = array(
			array(
				'key'     => 'general',
				'label'   => 'General', // label is used for left side tab item
				'heading' => 'General Settings',
				'tabs'    => array(
					array(
						'key'     => 'general',
						'label'   => 'General', // label is used for left side tab item
						'heading' => 'General',
						'fields'  => array(
							array(
								'id'          => 'autonami_pro',
								'label'       => __( 'Autonami Pro', 'wp-marketing-automations' ),
								'type'        => 'license',
								'license'     => self::get_pro_license( false ),
								'wrap_before' => '<h3 style="margin-bottom: 0;">License</h3>',
								'toggler'     => array(),
							),
							array(
								'id'               => 'autonami_connector',
								'label'            => __( 'Autonami Connectors', 'wp-marketing-automations' ),
								'type'             => 'license',
								'isConnectorField' => true,
								'license'          => self::get_connector_license( false ),
								'toggler'          => array(),
							),
							array(
								'id'          => 'bwfan_setting_business_name',
								'label'       => __( 'Business Name', 'wp-marketing-automations' ),
								'type'        => 'text',
								'class'       => 'bwfan_setting_business_name',
								'placeholder' => 'Enter Business Name',
								'hint'        => '',
								'required'    => false,
								'wrap_before' => '<h3>Business Details</h3><p><i>Anti-spam laws require you to put a physical address at the bottom of every email where you can be reached.</i></p>',
								'toggler'     => array(),
							),
							array(
								'id'          => 'bwfan_setting_business_address',
								'label'       => __( 'Business Address', 'wp-marketing-automations' ),
								'type'        => 'text',
								'class'       => 'bwfan_setting_business_address',
								'placeholder' => 'Enter Business Address',
								'hint'        => '',
								'required'    => false,
								'toggler'     => array(),
							),
						),
					),
					array(
						'key'     => 'emails',
						'label'   => 'Email', // label is used for left side tab item
						'heading' => 'Email Settings',
						'fields'  => $email_field,
					),
					array(
						'key'     => 'sms',
						'label'   => 'SMS', // label is used for left side tab item
						'heading' => 'SMS Settings',
						'fields'  => $sms_fields,
					),
					array(
						"key"         => 'whatsapp',
						"label"       => 'WhatsApp', // label is used for left side tab item
						"heading"     => 'WhatsApp',
						"showSection" => bwfan_is_autonami_pro_active() ? BWFCRM_Core()->conversation->is_whatsapp_service_available() : false,
						'fields'      => self::get_whatsapp_services_fields(),
					),
					array(
						"key"          => 'abandonment',
						"label"        => 'Cart', // label is used for left side tab item
						"heading"      => 'Cart',
						"isWooSection" => true,
						"fields"       => [
							array(
								'id'            => 'bwfan_ab_enable',
								'label'         => __( 'Enable Cart Tracking', 'wp-marketing-automations' ),
								'type'          => 'checkbox',
								'checkboxlabel' => __( "Enable to live capture buyer's email & cart details", 'wp-marketing-automations' ),
								'class'         => 'bwfan_ab_enable',
								'required'      => false,
								'toggler'       => array(),
							),
							array(
								'id'          => 'bwfan_ab_init_wait_time',
								'label'       => __( 'Wait Period (minutes)', 'wp-marketing-automations' ),
								'type'        => 'number',
								'class'       => '',
								'placeholder' => '15',
								'hint'        => __( 'Wait for a given time before the cart is marked as Recoverable', 'wp-marketing-automations' ),
								'required'    => false,
								'toggler'     => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_ab_enable',
											'value' => true,
										),
									),
									'relation' => 'OR',
								),
							),
							array(
								'id'          => 'bwfan_disable_abandonment_days',
								'label'       => __( 'Cool Off Period (days)', 'wp-marketing-automations' ),
								'type'        => 'number',
								'class'       => '',
								'placeholder' => '15',
								'required'    => false,
								'hint'        => __( 'Exclude customers from cart abandonment tracking if the order was placed days ago (recommended 15 days)', 'wp-marketing-automations' ),
								'toggler'     => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_ab_enable',
											'value' => true,
										),
									),
									'relation' => 'OR',
								),
							),
							array(
								'id'          => 'bwfan_ab_mark_lost_cart',
								'label'       => __( 'Lost Cart (days)', 'wp-marketing-automations' ),
								'type'        => 'number',
								'class'       => '',
								'placeholder' => '15',
								'required'    => false,
								'hint'        => __( 'Mark the user as Lost if the order is not made within the given days', 'wp-marketing-automations' ),
								'toggler'     => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_ab_enable',
											'value' => true,
										),
									),
									'relation' => 'OR',
								),
							),
							array(
								'id'            => 'bwfan_ab_email_consent',
								'label'         => __( 'Notice', 'wp-marketing-automations' ),
								'type'          => 'checkbox',
								'checkboxlabel' => __( 'When entering email addresses, inform customers that their email and cart data are saved to send abandonment reminders', 'wp-marketing-automations' ),
								'class'         => 'bwfan_ab_email_consent',
								'required'      => false,
								'wrap_before'   => '<h3>GDPR Consent</h3>',
								'toggler'       => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_ab_enable',
											'value' => true,
										),
									),
									'relation' => 'OR',
								),
							),
							array(
								'id'       => 'bwfan_ab_email_consent_message',
								'type'     => 'textarea',
								'class'    => 'bwfan_ab_email_consent_message',
								'required' => false,
								'hint'     => __( "Use merge tag {{no_thanks label='No Thanks'}} to let users opt out of cart tracking.", 'wp-marketing-automations' ),
								'toggler'  => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_ab_enable',
											'value' => true,
										),
										array(
											'id'    => 'bwfan_ab_email_consent',
											'value' => true,
										),
									),
									'relation' => 'AND',
								),
							),
							array(
								'id'          => 'bwfan_ab_tag_selector',
								'label'       => __( 'Add Tag', 'wp-marketing-automations' ),
								'type'        => 'tagselector',
								'class'       => '',
								'placeholder' => '',
								'required'    => false,
								'isProField'  => true,
								'wrap_before' => '<br/><h3>Contact</h3>',
								'hint'        => __( 'The selected tag(s) will be added when cart is abandoned. The tag(s) will be automatically removed when cart recovers', 'wp-marketing-automations' ),
								'toggler'     => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_ab_enable',
											'value' => true,
										),
									),
									'relation' => 'AND',
								),
							),
							array(
								'id'            => 'bwfan_ab_track_on_add_to_cart',
								'label'         => __( 'Track on Add to Cart', 'wp-marketing-automations' ),
								'type'          => 'checkbox',
								'checkboxlabel' => __( 'Track carts when a product is added to the cart for logged-in users', 'wp-marketing-automations' ),
								'class'         => 'bwfan_ab_track_on_add_to_cart',
								'required'      => false,
								'wrap_before'   => '<h3>User</h3>',
								'toggler'       => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_ab_enable',
											'value' => true,
										),
									),
									'relation' => 'OR',
								),
							),
							array(
								'id'            => 'bwfan_ab_exclude_users_cart_tracking',
								'label'         => __( 'Exclude User Roles', 'wp-marketing-automations' ),
								'type'          => 'checkbox',
								'checkboxlabel' => __( 'Exclude user roles from cart tracking', 'wp-marketing-automations' ),
								'class'         => 'bwfan_ab_exclude_users_cart_tracking',
								'required'      => false,
								'toggler'       => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_ab_enable',
											'value' => true,
										),
									),
									'relation' => 'OR',
								),
							),
							array(
								'id'       => 'bwfan_ab_exclude_roles',
								'type'     => 'select',
								'class'    => '',
								'options'  => $user_roles,
								'required' => false,
								'multiple' => true,
								'hint'     => __( 'Carts for selected user roles will not be tracked', 'wp-marketing-automations' ),
								'toggler'  => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_ab_enable',
											'value' => true,
										),
										array(
											'id'    => 'bwfan_ab_exclude_users_cart_tracking',
											'value' => true,
										),
									),
									'relation' => 'AND',
								),
							),
							array(
								'id'          => 'bwfan_ab_exclude_emails',
								'label'       => __( 'Emails', 'wp-marketing-automations' ),
								'type'        => 'textarea',
								'class'       => '',
								'required'    => false,
								'wrap_before' => '<br/><h3>Blacklist Emails</h3>',
								'hint'        => __( 'Enter emails, domains or partials to exclude from cart  abandonment tracking separated by comma(,) or in new line
                                            <br>You can add full emails (i.e. foo@example.com) or domains (i.e. domain.com), or partials (i.e. john)', 'wp-marketing-automations' ),
								'toggler'     => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_ab_enable',
											'value' => true,
										),
									),
									'relation' => 'AND',
								),
							),
							array(
								'id'          => 'bwfan_ab_restore_cart_message_success',
								'label'       => __( 'Cart Success Notice', 'wp-marketing-automations' ),
								'type'        => 'text',
								'class'       => '',
								'placeholder' => '',
								'wrap_before' => '<h3>Checkout Notice</h3>',
								'required'    => false,
								'hint'        => __( "Notice when cart is successfully restored. Leave blank in case you don't want to show a notice.", 'wp-marketing-automations' ),
								'toggler'     => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_ab_enable',
											'value' => true,
										),
									),
									'relation' => 'OR',
								),
							),
							array(
								'id'          => 'bwfan_ab_restore_cart_message_failure',
								'label'       => __( 'Cart Failure Notice', 'wp-marketing-automations' ),
								'type'        => 'text',
								'class'       => '',
								'placeholder' => '',
								'required'    => false,
								'hint'        => __( "Notice when cart fails to restore. Leave blank in case you don't want to show a notice.", 'wp-marketing-automations' ),
								'toggler'     => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_ab_enable',
											'value' => true,
										),
									),
									'relation' => 'OR',
								),
							),
						],
					),
					/*
					array(
						"key"     => 'conversion',
						"label"   => 'Conversions', // label is used for left side tab item
						"heading" => 'Conversions',
						"fields"  => [
							array(
								"id"          => 'bwfan_order_tracking_conversion',
								"label"       => __( 'Order Tracking Conversion', 'wp-marketing-automations' ),
								"type"        => 'number',
								"class"       => 'bwfan_order_tracking_conversion',
								"placeholder" => "Days to track order",
								"hint"        => __( "Days to Track order details for conversion.", 'wp-marketing-automations' ),
								"required"    => false,
								"toggler"     => array(),
							),
						],
					),*/
					array(
						'key'          => 'optin',
						'label'        => __( 'Checkout Consent', 'wp-marketing-automations' ),
						'heading'      => __( 'Checkout Consent', 'wp-marketing-automations' ),
						'isWooSection' => true,
						'fields'       => array(
							array(
								'id'            => 'bwfan_user_consent',
								'label'         => __( 'Enable Marketing Consent', 'wp-marketing-automations' ),
								'type'          => 'checkbox',
								'checkboxlabel' => __( 'Enable an optin on checkout to ask for the consent of marketing emails.', 'wp-marketing-automations' ),
								'hint'          => __( 'Note: For logged in users, this field would not be visible to Contacts if they are subscribed', 'wp-marketing-automations' ),
								'class'         => 'bwfan_user_consent',
								'required'      => false,
								'wrap_before'   => '',
								'toggler'       => array(),
							),
							array(
								'id'       => 'bwfan_user_consent_message',
								'label'    => __( 'Text', 'wp-marketing-automations' ),
								'type'     => 'textarea',
								'class'    => '',
								'required' => false,
								'toggler'  => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_user_consent',
											'value' => true,
										),
									),
									'relation' => 'OR',
								),
							),
							array(
								'id'       => 'bwfan_user_consent_position',
								'label'    => __( 'Consent Field Position', 'wp-marketing-automations' ),
								'type'     => 'select',
								'multiple' => false,
								'class'    => 'bwfan_user_consent_position',
								'options'  => array(
									array(
										'value' => 'below_term',
										'label' => __( 'Below Terms & Condition', 'wp-marketing-automations' ),
									),
									array(
										'value' => 'below_email',
										'label' => __( 'Below Email Field', 'wp-marketing-automations' ),
									),
									array(
										'value' => 'below_phone',
										'label' => __( 'Below Phone Field', 'wp-marketing-automations' ),
									),
								),
								'required' => false,
								'toggler'  => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_user_consent',
											'value' => true,
										),
									),
									'relation' => 'OR',
								),
							),
							array(
								'id'          => 'bwfan_user_consent_eu',
								'label'       => __( 'EU Contacts', 'wp-marketing-automations' ),
								'type'        => 'select',
								'multiple'    => false,
								'class'       => 'bwfan_user_consent_eu',
								'options'     => array(
									array(
										'value' => '1',
										'label' => __( 'Checked', 'wp-marketing-automations' ),
									),
									array(
										'value' => '0',
										'label' => __( 'Unchecked', 'wp-marketing-automations' ),
									),
								),
								'required'    => false,
								'hint'        => __( 'EU contacts are determined by their IP address. To respect GDPR, keep it unchecked.', 'wp-marketing-automations' ),
								'wrap_before' => '<br/><h3>Consent Checked Behaviour</h3>',
								'toggler'     => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_user_consent',
											'value' => true,
										),
									),
									'relation' => 'OR',
								),
							),
							array(
								'id'       => 'bwfan_user_consent_non_eu',
								'label'    => __( 'Non-EU Contacts', 'wp-marketing-automations' ),
								'type'     => 'select',
								'multiple' => false,
								'class'    => 'bwfan_user_consent_non_eu',
								'options'  => array(
									array(
										'value' => '1',
										'label' => __( 'Checked', 'wp-marketing-automations' ),
									),
									array(
										'value' => '0',
										'label' => __( 'Unchecked', 'wp-marketing-automations' ),
									),
								),
								'required' => false,
								'toggler'  => array(
									'fields'   => array(
										array(
											'id'    => 'bwfan_user_consent',
											'value' => true,
										),
									),
									'relation' => 'OR',
								),
							),
						),
					),
					array(
						'key'     => 'preference',
						'label'   => 'Subscribe Page', // label is used for left side tab item
						'heading' => 'Subscribe Page',
						'fields'  => array_merge( array(
							array(
								'id'       => 'bwfan_unsubscribe_page',
								'label'    => __( 'Page', 'wp-marketing-automations' ),
								'type'     => 'select',
								'multiple' => false,
								'class'    => 'bwfan-unsubscribe-page',
								'options'  => array(),
								'required' => false,
								'hint'     => self::get_unsubscribe_page_hint_text(),
								'ajax_cb'  => 'bwfan_select_unsubscribe_page',
								'toggler'  => array(),
							),
						), self::get_lists_preference_schema(), array(
							array(
								'id'          => 'bwfan_unsubscribe_from_all_label',
								'label'       => __( 'Unsubscribe All Lists Label', 'wp-marketing-automations' ),
								'type'        => 'text',
								'class'       => 'bwfan_unsubscribe_from_all_label',
								'placeholder' => '',
								'required'    => false,
								'hint'        => __( 'Label for Unsubscribe from all list option', 'wp-marketing-automations' ),
								'toggler'     => array(),
							),
							array(
								'id'          => 'bwfan_unsubscribe_from_all_description',
								'label'       => __( 'Unsubscribe All Lists Description', 'wp-marketing-automations' ),
								'type'        => 'text',
								'class'       => 'bwfan_unsubscribe_from_all_description',
								'placeholder' => '',
								'required'    => false,
								'hint'        => __( 'Description for Unsubscribe from all list option', 'wp-marketing-automations' ),
								'toggler'     => array(),
							),
							array(
								'id'          => 'bwfan_unsubscribe_data_success',
								'label'       => __( 'Text', 'wp-marketing-automations' ),
								'type'        => 'text',
								'class'       => 'bwfan_unsubscribe_data_success',
								'placeholder' => '',
								'required'    => false,
								'wrap_before' => '<br/><h3>Confirmation Message</h3>',
								'hint'        => __( 'Confirmation message when lists subscription is updated', 'wp-marketing-automations' ),
								'toggler'     => array(),
							),
						) ),
					),
					array(
						'key'     => 'advanced',
						'label'   => 'Advanced', // label is used for left side tab item
						'heading' => 'Advanced',
						'fields'  => array(
							array(
								'id'            => 'bwfan_sandbox_mode',
								'label'         => __( 'Sandbox Mode', 'wp-marketing-automations' ),
								'type'          => 'checkbox',
								'checkboxlabel' => __( 'Enable sandbox mode', 'wp-marketing-automations' ),
								'hint'          => __( "When sandbox mode is enabled, Automations won't create new tasks, and existing tasks won't run", 'wp-marketing-automations' ),
								'class'         => 'bwfan_sandbox_mode',
								'required'      => false,
								'wrap_before'   => '',
								'toggler'       => array(),
							),
							array(
								'id'            => 'bwfan_make_logs',
								'label'         => __( 'Logging', 'wp-marketing-automations' ),
								'type'          => 'checkbox',
								'checkboxlabel' => __( 'Enable logs creation, for debugging', 'wp-marketing-automations' ),
								'hint'          => '',
								'class'         => 'bwfan_make_logs',
								'required'      => false,
								'wrap_before'   => '',
								'toggler'       => array(),
							),
							array(
								'id'          => 'bwfan_delete_autonami_generated_coupons_time',
								'label'       => __( 'Delete Expired Coupons', 'wp-marketing-automations' ),
								'type'        => 'number',
								'class'       => 'bwfan_delete_autonami_generated_coupons_time',
								'placeholder' => '1',
								"isWooField"  => true,
								'wrap_before' => '<br/><h3>WooCommerce Coupons</h3>',
								'hint'        => __( 'Delete personalized coupons after expiry', 'wp-marketing-automations' ),
								'required'    => false,
								'toggler'     => array(),
							),
						),
					),
				),
			),
		);

		return apply_filters( 'bwfan_admin_settings_schema', $settings );
	}

	/**
	 * Get settings fields for whatsapp services
	 *
	 * @return bool
	 */
	public static function get_whatsapp_services_fields() {
		$fields = array();
		if ( bwfan_is_autonami_pro_active() && class_exists( 'WFCO_Autonami_Connectors_Core' ) ) {
			$services = BWFCRM_Core()->conversation->get_whatsapp_services();
			if ( count( $services ) > 0 ) {
				$fields = array(
					array(
						"id"           => 'bwfan_whatsapp_gap_btw_message',
						"label"        => __( 'Time Between Each Message (secs)', 'wp-marketing-automations' ),
						"type"         => 'number',
						"class"        => 'bwfan_whatsapp_gap_btw_message',
						"placeholder"  => '1',
						"hint"         => __( "The time gap between messages in seconds", 'wp-marketing-automations' ),
						"required"     => false,
						"autocomplete" => 'off',
						"toggler"      => array(),
					)
				);

				if ( count( $services ) > 1 ) {
					$fields[] = array(
						"id"       => 'bwfan_primary_whats_app_service',
						"label"    => __( 'Select Service', 'wp-marketing-automations' ),
						"type"     => 'select',
						"class"    => '',
						"options"  => $services,
						"required" => false,
						'multiple' => false,
						"toggler"  => array(),
					);
				}
			} else {
				$fields = array(
					array(
						'id'       => 'whatsapp_notice',
						'type'     => 'notice',
						'class'    => '',
						'status'   => 'error',
						'message'  => 'WhatsApp service is not configured yet.',
						'dismiss'  => false,
						'required' => false,
						'toggler'  => array(),
					),
					array(
						'id'           => 'redirect_button',
						'label'        => __( 'Click to configure Whatsapp Connector', 'wp-marketing-automations-connectors' ),
						'type'         => 'redirect_button',
						'redirect_url' => ( 'admin.php?page=autonami&path=/connectors' ),
						'class'        => '',
						'newtab'       => '_self',
						'btntype'      => 'secondary',
						'required'     => false,
						'toggler'      => array(),
					)
				);
			}
		}

		return $fields;
	}

	/**
	 * Check for whatsapp services
	 *
	 * @return bool
	 */
	public static function is_whatsapp_services_enabled() {
		$response = false;
		if ( bwfan_is_autonami_pro_active() && class_exists( 'WFCO_Autonami_Connectors_Core' ) ) {
			if ( class_exists( 'BWFCO_Wabot' ) && BWFAN_Core()->connectors->is_connected( 'bwfco_wabot' ) ) {
				$response = true;
			}
			if ( ! $response && class_exists( 'BWFCO_Waapi' ) && BWFAN_Core()->connectors->is_connected( 'bwfco_waapi' ) ) {
				$response = true;
			}
		}

		return $response;
	}

	public static function get_email_services() {
		$services = apply_filters( 'bwfan_email_services', array() );

		return is_array( $services ) ? $services : array();
	}

	public static function get_sms_services() {
		$services = apply_filters( 'bwfan_sms_services', array() );

		return is_array( $services ) ? $services : array();
	}

	/** return all autonami tables
	 *
	 * @return mixed|void
	 */
	public static function get_tables_array() {
		global $wpdb;
		$automations_table_array = apply_filters( 'bwfan_automation_tables', array(
			$wpdb->prefix . 'bwfan_automations',
			$wpdb->prefix . 'bwfan_automationmeta',
			$wpdb->prefix . 'bwfan_tasks',
			$wpdb->prefix . 'bwfan_taskmeta',
			$wpdb->prefix . 'bwfan_task_claim',
			$wpdb->prefix . 'bwfan_logs',
			$wpdb->prefix . 'bwfan_logmeta',
			$wpdb->prefix . 'bwfan_syncrecords',
			$wpdb->prefix . 'bwfan_message_unsubscribe',
			$wpdb->prefix . 'bwfan_contact_automations',
			$wpdb->prefix . 'bwfan_abandonedcarts',
		) );

		sort( $automations_table_array );

		return $automations_table_array;
	}

	/**
	 * checking if all table created or not
	 */
	public static function checking_all_tables_exists() {
		global $wpdb;
		$result                  = true;
		$not_created_tables      = array();
		$mytables                = $wpdb->get_results( 'SHOW TABLES', ARRAY_A );
		$tables_array            = array_column( $mytables, 'Tables_in_' . $wpdb->dbname );
		$automations_table_array = self::get_tables_array();

		foreach ( $automations_table_array as $table ) {
			if ( ! in_array( $table, $tables_array, true ) ) {
				$not_created_tables[] = $table;
			}
		}

		if ( ! empty( $not_created_tables ) ) {
			return $not_created_tables;
		}

		return $result;
	}

	/**
	 * Get Format for Success Response
	 *
	 * @param $result_array
	 * @param string $message
	 * @param int $response_code
	 *
	 * @return array
	 */
	public static function format_success_response( $result_array, $message = '', $response_code = 200 ) {
		return array(
			'code'    => $response_code,
			'message' => $message,
			'result'  => $result_array,
		);
	}

	public static function get_bounce_settings_schema() {
		if ( ! bwfan_is_autonami_pro_active() ) {
			return array(
				array(
					'id'          => 'bwfan_enable_bounce_handling',
					'type'        => '',
					'required'    => false,
					'wrap_before' => '<h3>Bounce Tracking</h3><p>This is a Pro feature, to capture bounced emails from email service provider.</p>',
				),
			);
		}
		$bounce_settings        = bwfan_is_autonami_pro_active() ? BWFCRM_Core()->email_webhooks->get_webhooks() : array();
		$bounce_options         = array(
			array(
				'label' => 'Select Email Service',
				'value' => '',
			),
		);
		$bounce_settings_schema = ! empty( $bounce_settings ) ? array_map( function ( $webhook, $slug ) use ( &$bounce_options ) {
			if ( empty( $slug ) || empty( $webhook ) || ! is_array( $webhook ) ) {
				return false;
			}
			$bounce_options[] = array(
				'label' => $webhook['name'],
				'value' => strtolower( $webhook['name'] ),
			);

			return array(
				'id'        => 'bwfan_email_webhook_' . $slug,
				// "label"    => $webhook['name'],
				'type'      => 'copier',
				'class'     => 'bwfan_email_webhook',
				'hint'      => __( "Paste this URL into your {$webhook['name']}'s Webhook settings to enable Bounce Handling", 'wp-marketing-automations' ),
				'required'  => false,
				'copy_text' => $webhook['link'],
				'toggler'   => array(
					'fields'   => array(
						array(
							'id'    => 'bwfan_bounce_select',
							'value' => strtolower( $webhook['name'] ),
						),
						array(
							'id'    => 'bwfan_enable_bounce_handling',
							'value' => true,
						),
					),
					'relation' => 'AND',
				),
			);
		}, $bounce_settings, array_keys( $bounce_settings ) ) : array();

		$bounce_settings_schema = array_merge( array(
			array(
				'id'            => 'bwfan_enable_bounce_handling',
				'label'         => __( 'Enable', 'wp-marketing-automations' ),
				'type'          => 'checkbox',
				'checkboxlabel' => __( 'Enable to capture bounced emails from the email service and mark Contact as Bounced', 'wp-marketing-automations' ),
				'class'         => 'bwfan_user_consent',
				'required'      => false,
				'wrap_before'   => '<h3>Bounce Tracking</h3>',
			),
			array(
				'id'       => 'bwfan_bounce_select',
				'type'     => 'select',
				'class'    => '',
				'options'  => $bounce_options,
				'required' => false,
				'multiple' => false,
				'toggler'  => array(
					'fields'   => array(
						array(
							'id'    => 'bwfan_enable_bounce_handling',
							'value' => true,
						),
					),
					'relation' => 'AND',
				),
			),
		), $bounce_settings_schema );

		return is_array( $bounce_settings_schema ) ? array_filter( $bounce_settings_schema ) : array();
	}

	public static function get_pro_license( $onlyKey = true ) {
		$bwf_licenses = get_option( 'woofunnels_plugins_info', false );
		if ( empty( $bwf_licenses ) || ! is_array( $bwf_licenses ) ) {
			return false;
		}
		$plugin_name = self::plugin_dependency_nice_names( 'autonami_pro' );

		foreach ( $bwf_licenses as $bwf_license ) {
			if ( $bwf_license['data_extra']['software_title'] === $plugin_name ) {
				if ( $onlyKey ) {
					return $bwf_license['data_extra']['api_key'];
				} else {
					return $bwf_license['data_extra'];
				}
			}
		}

		return false;
	}

	public static function get_connector_license( $onlyKey = true ) {
		$bwf_licenses = get_option( 'woofunnels_plugins_info', false );
		if ( empty( $bwf_licenses ) || ! is_array( $bwf_licenses ) ) {
			return false;
		}
		$plugin_name = self::plugin_dependency_nice_names( 'autonami_connector' );

		foreach ( $bwf_licenses as $bwf_license ) {
			if ( $bwf_license['data_extra']['software_title'] === $plugin_name ) {
				if ( $onlyKey ) {
					return $bwf_license['data_extra']['api_key'];
				} else {
					return $bwf_license['data_extra'];
				}
			}
		}

		return false;
	}

	/** maybe create abandoned cart if enable
	 *
	 * @param $active_abandoned_cart
	 */
	public static function maybe_create_abandoned_contact( $active_abandoned_cart ) {
		$global_settings = self::get_global_settings();
		$abandoned_tag   = json_decode( $global_settings['bwfan_ab_tag_selector'], true );

		if ( ! isset( $active_abandoned_cart['ID'] ) || empty( $active_abandoned_cart['ID'] ) ) {
			return;
		}

		$abandoned_id   = $active_abandoned_cart['ID'];
		$abandoned_data = BWFAN_Model_Abandonedcarts::get( $abandoned_id );
		if ( ! is_array( $abandoned_data ) ) {
			return;
		}

		$abandoned_user_id = $active_abandoned_cart['user_id'];
		$abandoned_email   = $abandoned_data['email'];
		if ( empty( $abandoned_email ) ) {
			return;
		}

		if ( empty( $abandoned_user_id ) ) {
			$abandoned_user_id = 0;
		}

		$contact = bwf_get_contact( $abandoned_user_id, $abandoned_email );
		if ( $contact instanceof WooFunnels_Contact && $contact->get_id() > 0 ) {
			if ( ! bwfan_is_autonami_pro_active() || ! class_exists( 'BWFCRM_Contact' ) ) {
				return;
			}
			$bwfcrm_contact = new BWFCRM_Contact( $contact ); // getting bwfcrm_contact object to add tags
			if ( ! empty( $abandoned_tag ) ) {
				$bwfcrm_contact->add_tags( $abandoned_tag );
			}

			return;
		}

		if ( $abandoned_user_id > 0 ) {
			$wp_user         = get_user_by( 'id', $abandoned_user_id );
			$abandoned_email = $wp_user->user_email;
		}

		$abandoned_checkout_data = json_decode( $abandoned_data['checkout_data'], true );

		$f_name      = is_array( $abandoned_checkout_data ) && isset( $abandoned_checkout_data['fields'] ) && isset( $abandoned_checkout_data['fields']['billing_first_name'] ) ? $abandoned_checkout_data['fields']['billing_first_name'] : '';
		$l_name      = is_array( $abandoned_checkout_data ) && isset( $abandoned_checkout_data['fields'] ) && isset( $abandoned_checkout_data['fields']['billing_last_name'] ) ? $abandoned_checkout_data['fields']['billing_last_name'] : '';
		$contact_no  = is_array( $abandoned_checkout_data ) && isset( $abandoned_checkout_data['fields'] ) && isset( $abandoned_checkout_data['fields']['billing_phone'] ) ? $abandoned_checkout_data['fields']['billing_phone'] : '';
		$bwf_contact = new WooFunnels_Contact( $abandoned_user_id, $abandoned_email );

		$bwf_contact->set_email( $abandoned_email );
		$bwf_contact->set_f_name( $f_name );
		$bwf_contact->set_l_name( $l_name );
		$bwf_contact->set_contact_no( $contact_no );

		if ( bwfan_is_autonami_pro_active() && class_exists( 'BWFCRM_Contact' ) ) {
			$bwfcrm_contact = new BWFCRM_Contact( $bwf_contact );  // getting bwfcrm_contact object to add tags
			/** add tag in case of available in cart settings */
			if ( ! empty( $abandoned_tag ) ) {
				$bwfcrm_contact->set_tags( $abandoned_tag );
			}
		}
		
		$bwf_contact->save();
	}

	/**
	 * remove abandoned cart tags on cart recovered
	 *
	 * @param $cart_details
	 * @param $order_id
	 * @param $order
	 */
	public static function bwfan_remove_abandoned_cart_tags( $cart_details, $order_id, $order ) {
		$global_settings        = self::get_global_settings();
		$removed_abandoned_tags = json_decode( $global_settings['bwfan_ab_tag_selector'], true );

		if ( empty( $removed_abandoned_tags ) ) {
			return;
		}
		$remove_tag_data = array();
		foreach ( $removed_abandoned_tags as $remove_tag ) {
			$remove_tag_data[] = $remove_tag['id'];
		}

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$email = $order->get_billing_email();
		$user  = $order->get_user();

		$user_id     = ! $user instanceof WP_User ? null : $user->ID;
		$bwf_contact = bwf_get_contact( $user_id, $email );
		$contact_id  = $bwf_contact->id;

		if ( ! isset( $contact_id ) || empty( $contact_id ) ) {
			return;
		}

		if ( ! bwfan_is_autonami_pro_active() || ! class_exists( 'BWFCRM_Contact' ) ) {
			return;
		}

		$bwfcrm_contact = new BWFCRM_Contact( $bwf_contact );

		if ( ! $bwfcrm_contact->is_contact_exists() ) {
			return;
		}

		$bwfcrm_contact->remove_tags( $remove_tag_data );
		$bwfcrm_contact->save();

	}

	/**
	 * Returns unsubscribe page hint text
	 *
	 * @return string
	 */
	public static function get_unsubscribe_page_hint_text() {
		$page_url = '';
		$html     = '';
		$setting  = self::get_global_settings();
		if ( isset( $setting['bwfan_unsubscribe_page'] ) && ! empty( $setting['bwfan_unsubscribe_page'] ) ) {
			$page_url = get_edit_post_link( $setting['bwfan_unsubscribe_page'] );
		}
		if ( $page_url ) {
			$html = '<a href="' . $page_url . '" target="_blank">' . __( 'Click here.', 'wp-marketing-automations' ) . '</a> ' . __( 'to edit the page.', 'wp-marketing-automations' ) . '<br /> <br />';
		}

		$html .= __( 'Use shortcodes <b>[wfan_contact_name]</b> for contact\'s name', 'wp-marketing-automations' ) . ', ' . __( '<b>[wfan_contact_email]</b> for contact\'s email', 'wp-marketing-automations' ) . ' and ' . __( '<b>[wfan_unsubscribe_button label=\'Update my preference\']</b> for the unsubscribe button.', 'wp-marketing-automations' );

		return $html;
	}

	public static function get_carts_count() {
		$recoverable_count = BWFAN_Recoverable_Carts::get_abandoned_carts( '', '', '', '', '', true );
		$recovered_count   = BWFAN_Recoverable_Carts::get_recovered_carts( '', '', '', true );
		$lost_carts        = BWFAN_Recoverable_Carts::get_abandoned_carts( '', '', '', '', 2, true );

		return [
			'recoverable' => $recoverable_count['total_count'],
			'recovered'   => $recovered_count['total_count'],
			'lost'        => $lost_carts['total_count'],
		];
	}

	public static function get_automation_data_count() {
		$automation_count   = self::get_all_automations( '', 'all', 0, 0, true );
		$active             = self::get_all_automations( '', '1', 0, 0, true );
		$inactive           = absint( $automation_count['total_records'] ) - absint( $active['total_records'] );
		$scheduled_count    = BWFAN_Core()->tasks->fetch_tasks_count( 0, 0 );
		$paused_count       = BWFAN_Core()->tasks->fetch_tasks_count( 0, 1 );
		$completed_count    = BWFAN_Core()->logs->fetch_logs_count( 1 );
		$failed_count       = BWFAN_Core()->logs->fetch_logs_count( 0 );
		$task_history_count = $scheduled_count + $paused_count + $completed_count + $failed_count;

		return [
			'automations'  => absint( $automation_count['total_records'] ),
			'status_count' => [
				'active'   => absint( $active['total_records'] ),
				'inactive' => $inactive,
			],
			'task_history' => $task_history_count
		];
	}

	/**
	 * Skip child order
	 *
	 * @param $id int order id
	 *
	 * @return bool
	 */
	public static function bwf_check_to_skip_child_order( $id ) {
		$skip = false;
		if ( apply_filters( 'bwf_skip_sub_order', false ) && wp_get_post_parent_id( $id ) ) {
			$skip = true;
		}

		return $skip;
	}

	/**
	 * may be get user country from woocommerce session
	 * @return array|mixed|string
	 */
	public static function maybe_get_user_country_code() {

		if ( ! is_null( WC()->session ) ) {
			$country_code = WC()->session->get( 'bwfan_user_checkout_country', '' );
			if ( ! empty( $country_code ) ) {
				return $country_code;
			}

			$country_code = self::get_user_country();
			WC()->session->set( 'bwfan_user_checkout_country', $country_code );

			return $country_code;

		}

		$country_code = self::get_user_country();

		return $country_code;
	}

	/** get user country
	 * @return mixed
	 */
	public static function get_user_country() {
		$user_location = WC_Geolocation::geolocate_ip();
		$country_code  = $user_location['country'];

		return $country_code;
	}
}
