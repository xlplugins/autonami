<?php

/**
 * OptIn manager class is to handle all scenarios occurs for opting the user
 * @author: WooFunnels
 * @since 0.0.1
 * @package WooFunnels
 *
 */
class WooFunnels_OptIn_Manager {

    public static $optIn_state;
    public static $should_show_optin = true;

    /**
     * Initialization to execute several hooks
     */
    public static function init() {
        //push notification for optin

        add_action( 'admin_init', array( __CLASS__, 'maybe_push_optin_notice' ), 15 );
        add_action( 'admin_init', array( __CLASS__, 'maybe_clear_optin' ), 15 );
        // track usage user callback
        add_action( 'bwf_maybe_track_usage_scheduled', array( __CLASS__, 'maybe_track_usage' ) );

        //initializing schedules
        add_action( 'wp', array( __CLASS__, 'initiate_schedules' ) );

        // For testing license notices, uncomment this line to force checks on every page load

        /** optin ajax call */
        add_action( 'wp_ajax_woofunnelso_optin_call', array( __CLASS__, 'woofunnelso_optin_call' ) );

        // optin yes track callback
        add_action( 'woofunnels_optin_success_track_scheduled', array( __CLASS__, 'optin_track_usage' ), 10 );

        add_filter( 'cron_schedules', array( __CLASS__, 'register_weekly_schedule' ), 10 );
    }

    /**
     * Set function to allow
     */
    public static function Allow_optin() {
        update_option( 'bwf_is_opted', 'yes', true );

        //try to push data for once
        $data = self::collect_data();

        //posting data to api
        WooFunnels_API::post_tracking_data( $data );
    }

    /**
     * Collect some data and let the hook left for our other plugins to add some more info that can be tracked down
     * <br/>
     * @return array data to track
     */
    public static function collect_data() {
        global $wpdb, $woocommerce;

        $installed_plugs     = WooFunnels_addons::get_installed_plugins();
        $active_plugins      = get_option( 'active_plugins' );
        $licenses            = WooFunnels_licenses::get_instance()->get_data();
        $theme               = array();
        $get_theme_info      = wp_get_theme();
        $theme['name']       = $get_theme_info->get( 'Name' );
        $theme['uri']        = $get_theme_info->get( 'ThemeURI' );
        $theme['version']    = $get_theme_info->get( 'Version' );
        $theme['author']     = $get_theme_info->get( 'Author' );
        $theme['author_uri'] = $get_theme_info->get( 'AuthorURI' );
        $ref                 = get_option( 'woofunnels_optin_ref', '' );
        $sections            = array();

        if ( class_exists( 'WooCommerce' ) ) {
            $payment_gateways = WC()->payment_gateways->payment_gateways();

            foreach ( $payment_gateways as $gateway_key => $gateway ) {
                if ( 'yes' === $gateway->enabled ) {
                    $sections[] = esc_html( $gateway_key );
                }
            }
            /* WordPress information. */
        }

        /** Product Count */
        $product_count          = array();
        $product_count_data     = wp_count_posts( 'product' );
        $product_count['total'] = $product_count_data->publish;

        $product_statuses = get_terms( 'product_type', array(
            'hide_empty' => 0,
        ) );
        foreach ( $product_statuses as $product_status ) {
            $product_count[ $product_status->name ] = $product_status->count;
        }

        /** Order Count */
        $order_count = array();
        if ( class_exists( 'WooCommerce' ) ) {
            $order_count_data       = wp_count_posts( 'shop_order' );
            $get_order_status       = wc_get_order_statuses();
            $get_order_status_slugs = array_keys( $get_order_status );
            foreach ( $get_order_status_slugs as $status_slug ) {
                $order_count[ $status_slug ] = $order_count_data->{$status_slug};
            }
        }

        $base_country = get_option( 'woocommerce_default_country', false );
        if ( false !== $base_country ) {
            $base_country = substr( $base_country, 0, 2 );
        }

        $return = array(
            'url'                    => home_url(),
            'email'                  => get_option( 'admin_email' ),
            'installed'              => $installed_plugs,
            'active_plugins'         => $active_plugins,
            'license_info'           => $licenses,
            'theme_info'             => $theme,
            'users_count'            => self::get_user_counts(),
            'locale'                 => get_locale(),
            'country'                => $base_country,
            'is_mu'                  => is_multisite() ? 'yes' : 'no',
            'wp'                     => get_bloginfo( 'version' ),
            'php'                    => phpversion(),
            'mysql'                  => $wpdb->db_version(),
            'WooFunnels_version'     => WooFunnel_Loader::$version,
            'notification_ref'       => $ref,
            'date'                   => date( 'd.m.Y H:i:s' ),
            'bwf_order_index_status' => get_option( '_bwf_db_upgrade', '0' ),
            'bwf_version'            => defined( 'BWF_VERSION' ) ? BWF_VERSION : '0.0.0',
        );

        if ( class_exists( 'WooCommerce' ) ) {
            $return['currency']       = get_woocommerce_currency();
            $return['wc']             = $woocommerce->version;
            $return['calc_taxes']     = get_option( 'woocommerce_calc_taxes' );
            $return['guest_checkout'] = get_option( 'woocommerce_enable_guest_checkout' );
            $return['product_count']  = $product_count;
            $return['order_count']    = $order_count;
            $return['wc_gateways']    = $sections;
        }

        return apply_filters( 'woofunnels_global_tracking_data', $return );
    }

    /**
     * Get user totals based on user role.
     * @return array
     */
    private static function get_user_counts() {
        $user_count          = array();
        $user_count_data     = count_users();
        $user_count['total'] = $user_count_data['total_users'];

        // Get user count based on user role
        foreach ( $user_count_data['avail_roles'] as $role => $count ) {
            $user_count[ $role ] = $count;
        }

        return $user_count;
    }

    /**
     * Set function to block
     */
    public static function block_optin() {
        update_option( 'bwf_is_opted', 'no', true );
    }

    public static function maybe_clear_optin() {
        if ( wp_verify_nonce( filter_input( INPUT_GET, '_nonce', FILTER_SANITIZE_STRING ), 'bwf_tools_action' ) && isset( $_GET['woofunnels_tracking'] ) && ( 'reset' === sanitize_text_field( $_GET['woofunnels_tracking'] ) )) {
            self::reset_optin();
            wp_safe_redirect( admin_url( 'admin.php?page=woofunnels&tab=tools' ) );
            exit;
        }
    }

    /**
     * Reset optin
     */
    public static function reset_optin() {


        $get_action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
        if ( 'yes' === $get_action ) {
            self::Allow_optin();
        } else {
            delete_option( 'bwf_is_opted' );
        }
        if ( false !== wp_next_scheduled( 'bwf_maybe_track_usage_scheduled' ) ) {
            wp_clear_scheduled_hook( 'bwf_maybe_track_usage_scheduled' );
        }

    }

    public static function update_optIn_referer( $referer ) {
        update_option( 'woofunnels_optin_ref', $referer, false );
    }

    /**
     * Checking the opt-in state and if we have scope for notification then push it
     */
    public static function maybe_push_optin_notice() {
        if ( self::get_optIn_state() === false && apply_filters( 'woofunnels_optin_notif_show', self::$should_show_optin ) ) {
            do_action( 'bwf_maybe_push_optin_notice_state_action' );

        }
    }

    /**
     * Get current optin status from database
     *
     * @return mixed|void
     */
    public static function get_optIn_state() {
        if ( self::$optIn_state !== null ) {
            return self::$optIn_state;
        }

        return self::$optIn_state = get_option( 'bwf_is_opted' );
    }

    /**
     * Callback function to run on schedule hook
     */
    public static function maybe_track_usage() {
        //checking optin state
        if ( 'yes' === self::get_optIn_state() ) {

            $data = self::collect_data();

            //posting data to api
            WooFunnels_API::post_tracking_data( $data );
        }
    }

    /**
     * Initiate schedules in order to start tracking data regularly
     */
    public static function initiate_schedules() {
        /** Clearing scheduled hook */
        if ( wp_next_scheduled( 'woofunnels_maybe_track_usage_scheduled' ) ) {
            wp_clear_scheduled_hook( 'woofunnels_maybe_track_usage_scheduled' );
        }

        if ( true === self::is_optin_allowed() && 'yes' === self::get_optIn_state() && ! wp_next_scheduled( 'bwf_maybe_track_usage_scheduled' ) ) {
            wp_schedule_event( current_time( 'timestamp' ), 'weekly_bwf', 'bwf_maybe_track_usage_scheduled' );
        }
    }

    public static function woofunnelso_optin_call() {
        check_ajax_referer('bwf_secure_key' );
        if ( is_array( $_POST ) && count( $_POST ) > 0 ) {
            $_POST['domain'] = home_url();
            $_POST['ip']     = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';
            WooFunnels_API::post_optin_data( $_POST );

            /** scheduling track call when success */
            if ( isset( $_POST['status'] ) && 'yes' === sanitize_text_field( $_POST['status'] ) ) {
                wp_schedule_single_event( time() + 2, 'woofunnels_optin_success_track_scheduled' );
            }
        }
        wp_send_json( array(
            'status' => 'success',
        ) );
        exit;
    }

    /**
     * Callback function to run on schedule hook
     */
    public static function optin_track_usage() {
        /** update week day for tracking */
        $track_week_day = date( 'w' );
        update_option( 'woofunnels_track_day', $track_week_day, false );

        $data = self::collect_data();

        //posting data to api
        WooFunnels_API::post_tracking_data( $data );
    }

    public static function maybe_default_optin() {
        return;
    }

    public static function register_weekly_schedule( $schedules ) {
        $schedules['weekly_bwf'] = array(
            'interval' => WEEK_IN_SECONDS,
            'display'  => __( 'Weekly BWF' ),
        );

        return $schedules;
    }

    public static function is_optin_allowed() {
        return apply_filters( 'buildwoofunnels_optin_allowed', true );
    }

}

// Initialization
WooFunnels_optIn_Manager::init();
