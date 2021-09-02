<?php
/**
 * Admin related functions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WooFunnels_DB_Updater
 *
 */
class WooFunnels_DB_Updater {
	/**
	 * @var $ins
	 */
	public static $ins;
	/**
	 * @var null Used when order indexing is running
	 */
	public static $indexing = null;
	/**
	 * @var WooFunnels_Background_Updater $updater
	 */
	public $updater;
	/**
	 * @var WooFunnels_Contacts_Background_Updater $contacts_updater
	 */
	public $contacts_updater;
	/**
	 * @var WooFunnels_Background_Updater $updater
	 */
	public $order_id_in_process;
	public $contact_wp_user_address_fields = array(
		'address-1' => 'billing_address_1',
		'address-2' => 'billing_address_2',
		'city'      => 'billing_city',
		'state'     => 'billing_state',
		'postcode'  => 'billing_postcode',
		'country'   => 'billing_country',
	);
	private $_user_address_meta_updated = array();

	/**
	 * WooFunnels_DB_Updater constructor.
	 */
	public function __construct() {

		/** Showing notice to admin to allow upgrading tokens */
		add_action( 'admin_notices', array( $this, 'woofunnels_show_contact_processing_notice' ) );

		add_action( 'admin_init', array( $this, 'woofunnels_handle_db_upgrade_actions' ), 100 );

		/** Initiate Background Database tables customer and customer on clicking 'Allow' button from tools */
		add_action( 'init', array( $this, 'woofunnels_init_background_updater' ), 110 );
		add_action( 'init', array( $this, 'woofunnels_init_background_contacts_updater' ), 110 );
		add_action( 'admin_head', array( $this, 'maybe_update_database_update_contacts' ) );
		add_action( 'admin_init', array( $this, 'woofunnels_maybe_update_customer_database' ), 120 );

		/** Creating contact for new orders */
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'woofunnels_wc_order_create_contact' ), 10, 3 );

		/** Creating updating customer on order statuses paid */
		add_action( 'woocommerce_order_status_changed', array( $this, 'woofunnels_status_change_create_update_contact_customer' ), 10, 3 );

		/** Updating customer and customer meta on accepting offer */
		add_action( 'wfocu_offer_accepted_and_processed', array( $this, 'woofunnels_offer_accept_create_update_customer' ), 1, 4 );

		/** Attempt to update customer on WP profile update*/
		add_action( 'profile_update', array( $this, 'bwf_update_contact_on_user_update' ), 10, 2 );
		add_action( 'woocommerce_save_account_details', array( $this, 'bwf_update_contact_on_user_update' ), 10, 1 );

		add_action( 'updated_user_meta', array( $this, 'mark_updated_address_fields' ), 10, 4 );

		/** Attempt to update customer On WP user profile login*/
		add_action( 'wp_login', [ $this, 'bwf_index_orders_on_login' ], 10, 2 );

		add_action( 'bwf_order_index_completed', [ $this, 'maybe_change_state_on_success' ] );

		add_action( 'woocommerce_refund_created', [ $this, 'bwf_update_refunded_amount' ], 10, 2 );

		add_action( 'rest_api_init', [ $this, 'rest_init_register_async_request' ] );

		add_action( 'woofunnels_tools_add_tables_row_start', [ $this, 'bwf_add_indexing_consent_button' ], 10, 1 );

		add_action( 'shutdown', [ $this, 'maybe_clean_indexing' ] );

		add_action( 'admin_footer', [ $this, 'maybe_re_dispatch_background_process' ] );
	}

	/**
	 * @return WooFunnels_DB_Updater
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	/**
	 * Creating/updating contacts and  customers on offer accepted
	 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
	 */
	public static function capture_offer_accepted_event( $request ) {
		$posted_data = $request->get_body_params();
		$order_id    = isset( $posted_data['order_id'] ) ? $posted_data['order_id'] : 0;
		$products    = ( isset( $posted_data['products'] ) && count( $posted_data['products'] ) > 0 ) ? $posted_data['products'] : array();
		$total       = isset( $posted_data['total'] ) ? $posted_data['total'] : 0;

		try {
			bwf_create_update_contact( $order_id, $products, $total, false );
		} catch ( Error $r ) {
			BWF_Logger::get_instance()->log( print_R( $r->getMessage(), true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	/** Creating/updating contacts and  customers on order status change */
	public static function capture_order_status_change_event( $request ) {
		$posted_data = $request->get_body_params();
		$order_id    = isset( $posted_data['order_id'] ) ? $posted_data['order_id'] : 0;
		try {
			bwf_create_update_contact( $order_id, array(), 0, true );
		} catch ( Error $r ) {
			BWF_Logger::get_instance()->log( print_R( $r->getMessage(), true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	/**
	 * @param $request
	 */
	public static function capture_wp_user_login_event( $request ) {

		$posted_data = $request->get_body_params();
		$user_id     = isset( $posted_data['user_id'] ) ? $posted_data['user_id'] : 0;
		try {
			bwf_update_contact_on_login( $user_id );
		} catch ( Error $r ) {
			BWF_Logger::get_instance()->log( print_R( $r->getMessage(), true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	public function needs_upgrade() {
		return apply_filters( 'bwf_init_db_upgrade', false );
	}

	public function woofunnels_handle_db_upgrade_actions() {
		if ( isset( $_GET['_bwf_remove_updated_db_notice'] ) && isset( $_GET['_bwf_updated_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_bwf_updated_nonce'] ) ), '_bwf_hide_updated_nonce' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woofunnels' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'woofunnels' ) );
			}

			$hide_notice = sanitize_text_field( wp_unslash( $_GET['_bwf_remove_updated_db_notice'] ) );

			if ( 'yes' === $hide_notice ) {
				$this->set_upgrade_state( '5' );
			}
		}

		if ( isset( $_GET['bwf_update_db'] ) && isset( $_GET['_bwf_update_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_bwf_update_nonce'] ) ), '_bwf_start_update_nonce' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woofunnels' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'woofunnels' ) );
			}

			$update_db = sanitize_text_field( wp_unslash( $_GET['bwf_update_db'] ) );

			if ( 'yes' === $update_db && '0' === $this->get_upgrade_state() ) {
				$this->set_upgrade_state( '2' );
			}
		}

		if ( isset( $_GET['_bwf_remove_declined_notice'] ) && isset( $_GET['_bwf_declined_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_bwf_declined_nonce'] ) ), '_bwf_hide_declined_nonce' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woofunnels' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'woofunnels' ) );
			}

			$hide_notice = sanitize_text_field( wp_unslash( $_GET['_bwf_remove_declined_notice'] ) );

			if ( 'yes' === $hide_notice ) {
				$this->set_upgrade_state( '6' );
			}
		}
	}

	public function set_upgrade_state( $stage ) {
		update_option( '_bwf_db_upgrade', $stage, true );
	}

	public function get_upgrade_state() {

		/**
		 * 0: upgrade is allowed, optin message should show
		 * 1: Upgrade is declined.
		 * 2: Upgrade is accepted but not dispatched
		 * 3: Upgrade is accepted & dispatched (show notice)
		 * 4: Upgrade is completed (show notice)
		 * 5: Upgrade is completed and notice dismissed
		 * 6: Upgrade is declined and dismissed
		 */
		return get_option( '_bwf_db_upgrade', '0' );
	}

	/**
	 * Contact processing notice to notify admin about the state
	 */
	public function woofunnels_show_contact_processing_notice() {

		$db_state = $this->get_upgrade_state();

		if ( '3' === $db_state ) { ?>
            <div class="bwf-notice notice notice-success">
                <div class="bwf-logo-wrapper">
                    <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) ) . 'assets/img/bwf-icon-white-bg.svg'; ?>" width="60" height="40">
                </div>

                <div class="bwf-message-content">
                    <strong><?php esc_html_e( 'Indexing of orders has started', 'woofunnels' ); ?></strong>
                    <p><?php esc_html_e( 'It may take sometime to finish the process. We will update this notice once the process completes.', 'woofunnels' ); ?>
                </div>
            </div>
			<?php
		} elseif ( '4' === $db_state ) {
			?>
            <div class="bwf-notice notice notice-success">
                <div class="bwf-logo-wrapper">
                    <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) ) . 'assets/img/bwf-icon-white-bg.svg'; ?>" width="60" height="40">
                </div>

                <div class="bwf-message-content">
                    <strong><?php esc_html_e( 'Success', 'woofunnels' ); ?></strong>
                    <p><?php esc_html_e( 'Order indexing completed successfully.', 'woofunnels' ); ?></p>
                </div>

                <div class="bwf-message-action">
                    <a class="button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( '_bwf_remove_updated_db_notice', 'yes' ), '_bwf_hide_updated_nonce', '_bwf_updated_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'woofunnels' ); ?></a>
                </div>
            </div>
			<?php
		} elseif ( '1' === $db_state ) {
			?>

            <div class="bwf-notice notice notice-error">
                <div class="bwf-logo-wrapper">
                    <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) ) . 'assets/img/bwf-icon-white-bg.svg'; ?>" width="60" height="40">
                </div>

                <div class="bwf-message-content">
                    <strong><?php esc_html_e( 'WooFunnels Notice', 'woofunnels' ); ?></strong>
                    <p><?php echo sprintf( wp_kses_post( __( 'Unable to complete indexing of orders. Please <a target="_blank" href="%s">contact support</a> to get the issue resolved.', 'woofunnels' ) ), esc_url( 'https://buildwoofunnels.com/support/' ) ); ?></p>
                </div>

                <div class="bwf-message-action">
                    <a class="button-secondary" target="_blank" href="<?php echo esc_url( 'https://buildwoofunnels.com/support/' ); ?>"><?php esc_html_e( 'Contact Support', 'woofunnels' ); ?></a>
                    <a class="button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( '_bwf_remove_declined_notice', 'yes' ), '_bwf_hide_declined_nonce', '_bwf_declined_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'woofunnels' ); ?></a>
                </div>
            </div>
			<?php
		}
		?>
        <style>

            .wp-admin .bwf-notice.notice, .wp-admin.toplevel_page_woofunnels .bwf-notice.notice, .wp-admin.woofunnels_page_upstroke .bwf-notice.notice {
                display: -webkit-box;
                display: -webkit-flex;
                display: -ms-flexbox;
                display: flex !important;
                -webkit-box-align: center;
                -webkit-align-items: center;
                -ms-flex-align: center;
                align-items: center;
                padding: 12px;
                height: auto;
            }

            .wp-admin .bwf-message-content, .wp-admin.toplevel_page_woofunnels .bwf-message-content, .wp-admin.woofunnels_page_upstroke .bwf-message-content {
                padding: 0 13px;
            }

            .wp-admin .bwf-message-action, .wp-admin.toplevel_page_woofunnels .bwf-message-action, .wp-admin.woofunnels_page_upstroke .bwf-message-action {
                text-align: center;
                display: -webkit-box;
                display: -webkit-inline-flex;
                display: -ms-flexbox;
                display: inline;
                -webkit-box-orient: vertical;
                -webkit-box-direction: normal;
                -webkit-flex-direction: column;
                -ms-flex-direction: column;
                flex-direction: column;
                margin-left: auto;
            }

            .wp-admin .bwf-message-content p, .wp-admin.toplevel_page_woofunnels .bwf-message-content p, .wp-admin.woofunnels_page_upstroke .bwf-message-content p {
                margin: 0;
                padding: 0;
            }

            .wp-admin .bwf-logo-wrapper, .wp-admin.toplevel_page_woofunnels .bwf-logo-wrapper, .wp-admin.woofunnels_page_upstroke .bwf-logo-wrapper {
                /* height: 51px; */
            }

            .wp-admin .bwf-notice.notice.notice-success {
                border-left-color: #1daafc;
            }
        </style>
		<?php
	}

	// Register offer accepted and processed

	/**
	 * Initiate WooFunnels_Background_Updater class
	 * @see woofunnels_maybe_update_customer_database()
	 */
	public function woofunnels_init_background_updater() {
		if ( class_exists( 'WooFunnels_Background_Updater' ) ) {
			$this->updater = new WooFunnels_Background_Updater();
		}
	}

	/**
	 * Initiate WooFunnels_Background_Updater class
	 * @see woofunnels_maybe_update_customer_database()
	 */
	public function woofunnels_init_background_contacts_updater() {
		if ( class_exists( 'WooFunnels_Contacts_Background_Updater' ) ) {
			$this->contacts_updater = new WooFunnels_Contacts_Background_Updater();
		}
	}

	/**
	 * @hooked over `woocommerce_checkout_order_processed`
	 * Creating BWF contact if not exist on WC new order
	 * sync call
	 *
	 * @param $order_id
	 * @param $posted_data
	 * @param $order WC_Order
	 */
	public function woofunnels_wc_order_create_contact( $order_id, $posted_data, $order ) {

		$wp_id = $order->get_customer_id();
		$email = $order->get_billing_email();

		/** If no email then return */
		if ( empty( $email ) ) {
			return;
		}

		/** Assigning wp id 0 if not available */
		if ( empty( $wp_id ) ) {
			$wp_id = 0;
		}

		$bwf_contact = bwf_get_contact( $wp_id, $email );

		/** If contact exists then directly add meta */
		if ( $bwf_contact->get_id() > 0 ) {
			$bwf_contact = bwf_create_update_contact_object( $bwf_contact, $order );
			$bwf_contact->save();
			BWF_Logger::get_instance()->log( "Order #" . $order->get_id() . ": Processed against contact ID" . $bwf_contact->get_id(), 'woofunnels_indexing' );

			$order->update_meta_data( '_woofunnel_cid', $bwf_contact->get_id() );
			$order->save_meta_data();

			return;
		}

		/** Need to create a contact */
		if ( $wp_id > 0 ) {
			$wp_user = get_user_by( 'id', $wp_id );
			$email   = $wp_user->user_email;
		}
		$bwf_contact->set_email( $email );

		$bwf_contact = bwf_create_update_contact_object( $bwf_contact, $order );
		bwf_contact_maybe_update_creation_date( $bwf_contact, $order );

		$bwf_contact->save();
		BWF_Logger::get_instance()->log( "Order #" . $order->get_id() . ": Processed against contact ID" . $bwf_contact->get_id(), 'woofunnels_indexing' );

		$order->update_meta_data( '_woofunnel_cid', $bwf_contact->get_id() );
		$order->save_meta_data();
	}

	/**
	 * Creating or updating contact and customer on order status changed to paid statuses
	 *
	 * @param $order_id
	 * @param $from
	 * @param $to
	 */
	public function woofunnels_status_change_create_update_contact_customer( $order_id, $from, $to ) {

		if ( apply_filters( 'bwf_woofunnel_skip_sub_order', true ) && wp_get_post_parent_id( $order_id ) ) {
			return;
		}
		$order            = wc_get_order( $order_id );
		$paid_status      = $order->has_status( wc_get_is_paid_statuses() );
		$woofunnel_custid = $order->get_meta( '_woofunnel_custid' );
		if ( $paid_status && empty( $woofunnel_custid ) ) {
			$data = array( 'order_id' => $order_id );
			$url  = home_url() . '/?rest_route=/woofunnel_customer/v1/order_status_changed';
			$args = bwf_get_remote_rest_args( $data );

			wp_remote_post( $url, $args );
		}

		//Reducing total_value with remaining order total (if partial earlier refund made)
		if ( 'cancelled' === $to ) {
			BWF_Logger::get_instance()->log( "Order status changes from $from to $to for order id: $order_id", 'woofunnels_indexing' );
			bwf_reduce_customer_total_on_cancel( $order_id );
		}
	}

	public function rest_init_register_async_request() {
		//Posting data to async request for processing package product and total for indexing
		register_rest_route( 'woofunnel_customer/v1', '/offer_accepted', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'capture_offer_accepted_event' ),
			'permission_callback' => '__return_true',
		) );

		/** Posting data to async request for processing new order product on order status change */
		register_rest_route( 'woofunnel_customer/v1', '/order_status_changed', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'capture_order_status_change_event' ),
			'permission_callback' => '__return_true',
		) );

		/** Posting data to async request for indexing on user login */
		register_rest_route( 'woofunnel_customer/v1', '/wp_user_login', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'capture_wp_user_login_event' ),
			'permission_callback' => '__return_true',
		) );

		/** Profile Update Async Call */
		register_rest_route( 'woofunnel_customer/v1', '/wp_profile_update', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'capture_profile_update_event' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * Updating contact and customer on accepting offer
	 *
	 * @param $get_offer_id
	 * @param $get_package
	 * @param $get_parent_order
	 * @param $new_order
	 */
	public function woofunnels_offer_accept_create_update_customer( $get_offer_id, $get_package, $get_parent_order, $new_order ) {
		$order_id     = 0;
		$new_order_id = 0;

		if ( $get_parent_order instanceof WC_Order ) {
			$order_id = $get_parent_order->get_id();
		}
		if ( $new_order instanceof WC_Order ) {
			$new_order_id = $new_order->get_id();
		}

		/**
		 *  Updating contact and customer in async REST API request if parent order is already indexed otherwise customer will be updated during parent order status change
		 * If batching is off then customer will be updated during child order status change
		 */
		if ( $order_id && ! empty( get_post_meta( $order_id, '_woofunnel_custid', false ) ) && $new_order_id === 0 ) {
			BWF_Logger::get_instance()->log( "Creating/Updating contact and customer in async request for batching order_id: $order_id and offer id: $get_offer_id ", 'woofunnels_indexing' );
			$product_ids = array();
			if ( is_array( $get_package ) && isset( $get_package['products'] ) && is_array( $get_package['products'] ) ) {

				foreach ( $get_package['products'] as $product_data ) {
					if ( isset( $product_data['id'] ) ) {
						array_push( $product_ids, $product_data['id'] );
					}
					if ( isset( $product_data['_offer_data'] ) && isset( $product_data['_offer_data']->id ) && isset( $product_data['id'] ) && $product_data['id'] !== $product_data['_offer_data']->id ) {
						array_push( $product_ids, $product_data['_offer_data']->id );
					}
				}
			}

			$total       = isset( $get_package['total'] ) ? $get_package['total'] : 0;
			$product_ids = array_unique( $product_ids );

			$data = array(
				'products' => $product_ids,
				'total'    => $total,
				'order_id' => $order_id,
			);
			$url  = home_url() . '/?rest_route=/woofunnel_customer/v1/offer_accepted';
			$args = bwf_get_remote_rest_args( $data );

			wp_remote_post( $url, $args );
		}
	}

	/**
	 * Updating refunded amount in order meta
	 *
	 * @param $refund_id
	 * @param $args
	 */
	public function bwf_update_refunded_amount( $refund_id, $args ) {
		$order_id = isset( $args['order_id'] ) ? $args['order_id'] : 0;
		$amount   = isset( $args['amount'] ) ? $args['amount'] : 0;

		bwf_update_customer_refunded( $order_id, $amount );
	}

	public function maybe_change_state_on_success() {
		delete_option( '_bwf_last_offsets' );
		$this->set_upgrade_state( '4' );
	}

	/**
	 * Adding allow button for db upgrade inside tools
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function bwf_add_indexing_consent_button() {
		$get_threshold_order = get_option( '_bwf_order_threshold', BWF_THRESHOLD_ORDERS );
		$bwf_db_upgrade      = $this->get_upgrade_state();

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( '3' !== $bwf_db_upgrade || $get_threshold_order < 1 ) {
			add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'woofunnels_handle_indexed_orders', 10, 2 );
			$all_order_ids = wc_get_orders( array(
				'return'      => 'ids',
				'numberposts' => '-1',
				'post_type'   => 'shop_order',
				'status'      => wc_get_is_paid_statuses(),
			) );
			remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'woofunnels_handle_indexed_orders', 10 );
			$get_threshold_order = count( $all_order_ids );
		}
		$remaining_text = '';

		if ( 0 === $get_threshold_order && 0 === absint( $bwf_db_upgrade ) ) {
			$this->set_upgrade_state( '5' );
			$bwf_db_upgrade = '5';
		}
		if ( '5' !== $bwf_db_upgrade && '4' !== $bwf_db_upgrade && $get_threshold_order > 0 ) {
			$remaining_text = sprintf( __( 'This store has <strong>%s orders</strong> to index.' ), $get_threshold_order );
		}

		if ( true === apply_filters( 'bwf_needs_order_indexing', false ) ) {
			?>
            <tr>
                <th>
                    <strong class="name"><?php esc_html_e( 'Index Past Orders', 'woofunnels' ); ?></strong>
                    <p class="description"><?php echo wp_kses_post( sprintf( 'This tool will scan all the previous orders and create an optimized index to run efficient queries. %s', $remaining_text ), 'woofunnels' ); ?></p>
					<?php if ( '1' === $bwf_db_upgrade || '6' === $bwf_db_upgrade ) { ?>
                        <span style="width:100%; color: red;"><?php esc_html_e( 'Unable to complete indexing of orders.', 'woofunnels' ); ?></span><br/>
						<?php esc_html_e( 'Please', 'woofunnels' ); ?>
                        <a target="_blank" href="<?php echo esc_url( 'https://buildwoofunnels.com/support/' ); ?>"><?php esc_html_e( 'contact support', 'woofunnels' ); ?></a><?php esc_html_e( ' to get the issue resolved.', 'woofunnels' ); ?>
                        <br/><br/>
					<?php } ?>
                    <a href="https://buildwoofunnels.com/docs/upstroke/miscellaneous/index-past-order/"><?php esc_html_e( 'Learn more about this process', 'woofunnels' ); ?></a>
                </th>
                <td class="run-tool">
					<?php if ( '3' === $bwf_db_upgrade ) { ?>
                        <a href="javascript:void(0);" class="button button-large disabled"><?php esc_html_e( 'Running', 'woofunnels' ); ?></a>
					<?php } elseif ( '4' === $bwf_db_upgrade || '5' === $bwf_db_upgrade ) { ?>
                        <a href="javascript:void(0);" class="button button-large disabled"><?php esc_html_e( 'Completed', 'woofunnels' ); ?></a>

					<?php } elseif ( '1' === $bwf_db_upgrade || '6' === $bwf_db_upgrade ) { ?>
                        <a href="javascript:void(0);" class="button button-large disabled"><?php esc_html_e( 'Start', 'woofunnels' ); ?></a>
						<?php
					} else {
						$start_url = esc_url( wp_nonce_url( add_query_arg( 'bwf_update_db', 'yes' ), '_bwf_start_update_nonce', '_bwf_update_nonce' ) );
						?>
                        <a class="button button-large <?php echo ( $get_threshold_order > 0 ) ? '' : 'disabled'; ?>" href="<?php echo ( $get_threshold_order > 0 ) ? $start_url : 'javascript:void(0);'; ?>"><?php esc_html_e( 'Start', 'woofunnels' ); ?></a>
					<?php } ?>
                </td>
            </tr>
			<?php
		}
	}

	/**
	 * @param $user_id
	 * @param $old_user_data
	 *
	 * @hooked on profile_update
	 */
	public function bwf_update_contact_on_user_update( $user_id, $old_user_data = [] ) {
		if ( 'profile_update' === current_action() ) {
			$this->do_profile_update_async_call( $user_id, $old_user_data );

			return;
		}
		if ( 'woocommerce_save_account_details' === current_action() ) {
			$this->do_profile_update_async_call( $user_id );
		}
	}

	/** Do async profile update call */
	public function do_profile_update_async_call( $user_id, $old_user_data = null ) {
		$data = array( 'user_id' => $user_id );
		if ( $old_user_data instanceof WP_User && is_email( $old_user_data->user_email ) ) {
			$data['old_user_email'] = $old_user_data->user_email;
		}

		/** Get Changed Address Fields */
		$data['fields'] = array();
		foreach ( $this->_user_address_meta_updated as $meta_key => $meta_value ) {
			$crm_key = array_search( $meta_key, $this->contact_wp_user_address_fields, true );
			if ( empty( $crm_key ) ) {
				continue;
			}

			$data['fields'][ $crm_key ] = $meta_value;
		}

		$url  = site_url() . '/?rest_route=/woofunnel_customer/v1/wp_profile_update';
		$args = bwf_get_remote_rest_args( $data );

		wp_remote_post( $url, $args );
	}

	/** Update Address fields on WP User update */
	public function capture_profile_update_event( $request ) {
		/** Return if version is less than 2.0.2 */
		if ( defined( 'BWFAN_PRO_VERSION' ) && ! version_compare( BWFAN_PRO_VERSION, '2.0.2', '>' ) ) {
			return;
		}

		$posted_data    = $request->get_body_params();
		$user_id        = isset( $posted_data['user_id'] ) ? absint( $posted_data['user_id'] ) : 0;
		$old_user_email = isset( $posted_data['old_user_email'] ) ? $posted_data['old_user_email'] : '';
		$fields         = isset( $posted_data['fields'] ) && is_array( $posted_data['fields'] ) ? $posted_data['fields'] : array();

		$contact = $this->maybe_get_contact_on_profile_update( $user_id, $old_user_email );

		if ( false === $contact ) {
			$this->_user_address_meta_updated = array();

			return;
		}

		if ( ! class_exists( 'WooCommerce' ) || empty( $fields ) ) {
			$contact->save();

			return;
		}

		$contact = apply_filters( 'bwf_before_profile_update_contact_sync', $contact, $user_id );

		foreach ( $fields as $crm_key => $meta_value ) {
			if ( 'state' === $crm_key ) {
				$contact->set_state( $meta_value );
				continue;
			}

			if ( 'country' === $crm_key ) {
				$contact->set_country( $meta_value );
				continue;
			}

			$contact = apply_filters( 'bwf_profile_update_contact_sync_field', $contact, $crm_key, $meta_value, $user_id );
		}

		$contact = apply_filters( 'bwf_after_profile_update_contact_sync', $contact, $user_id );

		$contact->set_last_modified( current_time( 'mysql', 1 ) );
		$contact->save();
	}

	/** Get the unsaved contact with WPID and Email changes */
	public function maybe_get_contact_on_profile_update( $user_id, $old_user_email = '' ) {
		/** Check if Old User Data valid */
		$old_email_valid = is_email( $old_user_email );
		$new_user        = get_user_by( 'id', $user_id );
		$new_user_exists = $new_user instanceof WP_User && is_email( $new_user->user_email );

		/** Check if email changed */
		$email_changed = $old_email_valid && $new_user_exists && $new_user->user_email !== $old_user_email;
		/** Get Contact by Old Email & ( get new_contact, if email changed ) */
		if ( ! $old_email_valid ) {
			$contact     = new WooFunnels_Contact( '', $new_user->user_email );
			$new_contact = null;
		} else {
			$contact     = new WooFunnels_Contact( '', $old_user_email );
			$new_contact = $email_changed ? new WooFunnels_Contact( '', $new_user->user_email ) : null;
		}

		$old_contact_exists = $contact instanceof WooFunnels_Contact && absint( $contact->get_id() ) > 0;
		$new_contact_exists = $new_contact instanceof WooFunnels_Contact && absint( $new_contact->get_id() ) > 0;

		if ( $new_contact_exists ) {
			$this->maybe_set_wpid_of_correct_contact( $new_contact, $contact, $user_id );

			/** If both old and new exists, then return */
			if ( $old_contact_exists ) {
				return false;
			}

			/** If both old doesn't exists, then use new as old and go ahead */
			$contact            = $new_contact;
			$new_contact_exists = false;
			$old_contact_exists = true;
		}

		/** If both old and new doesn't exists, then create the contact with new email */
		if ( ! $old_contact_exists && ! $new_contact_exists ) {
			/** If Email changes, then contact with new email, else old one */
			$contact = $new_contact instanceof WooFunnels_Contact ? $new_contact : $contact;

			/** If contact is not WooFunnels_Contact */
			$contact = $contact instanceof WooFunnels_Contact ? $contact : new WooFunnels_Contact( $user_id, $new_user->user_email );

			$contact->set_f_name( $new_user->first_name );
			$contact->set_l_name( $new_user->last_name );
			$old_contact_exists = true;
		}

		/** Update WPID if old WPID is different */
		if ( $user_id !== absint( $contact->get_wpid() ) ) {
			$contact->set_wpid( $user_id );
		}

		/** Update email if changed */
		if ( $new_user_exists && $email_changed ) {
			$contact->set_email( $new_user->user_email );
		}

		return $contact;
	}

	private function maybe_set_wpid_of_correct_contact( $new_contact, $old_contact, $user_id ) {
		global $wpdb;

		$old_contact_exists = $old_contact instanceof WooFunnels_Contact && absint( $old_contact->get_id() ) > 0;
		$new_contact_exists = $new_contact instanceof WooFunnels_Contact && absint( $new_contact->get_id() ) > 0;

		/** Set wpid, if not same */
		if ( $new_contact_exists && $user_id !== absint( $new_contact->get_wpid() ) ) {
			$new_contact->set_wpid( $user_id );
			$new_contact->set_last_modified( current_time( 'mysql', 1 ) );
			$new_contact->save();
		}

		/** Remove WPID on old contact if same as user_id */
		if ( $old_contact_exists && $user_id === absint( $old_contact->get_wpid() ) ) {
			/** Using SQL because setting wpid as blank is not supported in core */
			$wpdb->update( $wpdb->prefix . 'bwf_contact', array(
				'wpid'          => 0,
				'last_modified' => current_time( 'mysql', 1 ),
			), array( 'id' => $old_contact->get_id() ) );
		}
	}

	public function mark_updated_address_fields( $meta_id, $object_id, $meta_key, $_meta_value ) {
		/** Return if version is less than 2.0.2 */
		if ( defined( 'BWFAN_PRO_VERSION' ) && ! version_compare( BWFAN_PRO_VERSION, '2.0.2', '>' ) ) {
			return;
		}

		$address_meta_keys = array_values( $this->contact_wp_user_address_fields );
		if ( in_array( $meta_key, $address_meta_keys, true ) ) {
			$this->_user_address_meta_updated[ $meta_key ] = $_meta_value;
		}
	}

	/**
	 * Indexing orders and create/update contacts and customers on user login
	 *
	 * @param $user_login
	 * @param $user
	 *
	 * @hooked on wp_login
	 */
	public function bwf_index_orders_on_login( $user_login, $user ) {
		$data = array( 'user_id' => $user->ID );
		$url  = site_url() . '/?rest_route=/woofunnel_customer/v1/wp_user_login';
		$args = bwf_get_remote_rest_args( $data );

		wp_remote_post( $url, $args );
	}

	/**
	 *
	 */
	public function maybe_clean_indexing() {
		if ( 1 === did_action( 'admin_head' ) && current_user_can( 'manage_options' ) && 'yes' === filter_input( INPUT_GET, 'bwf_index_clean', FILTER_SANITIZE_STRING ) ) {
			global $wpdb;

			$tables = array(
				'bwf_wc_customers',
			);

			foreach ( $tables as &$table ) {
				$bwf_table = $wpdb->prefix . $table;
				$wpdb->query( "DROP TABLE IF EXISTS $bwf_table" );  //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			delete_option( '_bwf_db_version' );
			delete_option( '_bwf_db_upgrade' );
			delete_option( '_bwf_order_threshold' );
			delete_option( '_bwf_offset' );
			delete_option( '_bwf_last_offsets' );
			delete_option( '_bwf_contacts_threshold' );
			delete_option( '_bwf_contacts_offset' );
			delete_option( '_bwf_contacts_last_offsets' );

			$table = $wpdb->prefix . 'postmeta';
			$wpdb->delete( $table, array( 'meta_key' => '_woofunnel_cid' ) );  //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$wpdb->delete( $table, array( 'meta_key' => '_woofunnel_custid' ) );  //phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$this->updater->kill_process_safe();
			BWF_Logger::get_instance()->log( 'Indexing was cleaned manually.', 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
		$this->bwf_maybe_restart_indexing();
	}

	/**
	 * Restart indexing when it is stop due to any reason like cron disabled, server stopped etc
	 */
	public function bwf_maybe_restart_indexing() {
		if ( 1 === did_action( 'admin_head' ) && current_user_can( 'manage_options' ) && 'yes' === filter_input( INPUT_GET, 'bwf_restart_indexing', FILTER_SANITIZE_STRING ) ) {
			$this->set_upgrade_state( '2' );
			$this->woofunnels_maybe_update_customer_database();
		}
	}

	/**
	 * @hooked over `admin_head`
	 * This method takes care of database updating process.
	 * Checks whether there is a need to update the database
	 * Iterates over define callbacks and passes it to background updater class
	 * Update bwf_customer and bwf_customer_meta tables with new token from different tables
	 */
	public function woofunnels_maybe_update_customer_database() {

		if ( is_null( $this->updater ) ) {
			return;
		}

		if ( isset( $_GET['bwf_update_db'] ) && isset( $_GET['_bwf_update_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_bwf_update_nonce'] ) ), '_bwf_start_update_nonce' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woofunnels' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'woofunnels' ) );
			}

			$bwf_update_db = sanitize_text_field( wp_unslash( $_GET['bwf_update_db'] ) );

			$get_state = $this->get_upgrade_state();
			if ( 'yes' === $bwf_update_db && '2' === $get_state ) {
				$this->bwf_start_indexing();
			}
		}
	}

	public function bwf_start_indexing() {
		$task = 'bwf_create_update_contact_customer';  //Scanning order table and updating customer tables
		$this->updater->push_to_queue( $task );
		BWF_Logger::get_instance()->log( '**************START INDEXING************', 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		$this->set_upgrade_state( '3' );
		$this->updater->save()->dispatch();
		BWF_Logger::get_instance()->log( 'First Dispatch completed', 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
	}

	public function capture_fatal_error() {
		$error = error_get_last();
		if ( ! empty( $error ) ) {
			if ( is_array( $error ) && in_array( $error['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ), true ) ) {

				if ( $this->is_ignorable_error( $error['message'] ) ) {
					return;
				}
				BWF_Logger::get_instance()->log( 'Error logged during the process' . print_r( $error, true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

				$current_offset = get_option( '_bwf_offset', 0 );
				$current_offset ++;
				update_option( '_bwf_offset', $current_offset );

				$order_id = WooFunnels_Dashboard::$classes['WooFunnels_DB_Updater']->get_order_id_process();
				$order    = wc_get_order( $order_id );
				if ( $order instanceof WC_Order ) {

					$order->update_meta_data( '_woofunnel_cid', 0 );
					$order->save_meta_data();
				}
			}
		}
	}

	private function is_ignorable_error( $str ) {
		$get_all_ingorable_regex = $this->ignorable_errors();

		foreach ( $get_all_ingorable_regex as $re ) {
			$matches = [];
			preg_match_all( $re, $str, $matches, PREG_SET_ORDER, 0 );
			if ( ! empty( $matches ) ) {
				return true;
			}
		}

		return false;
	}

	private function ignorable_errors() {
		return [ '/Maximum execution time of/m', '/Allowed memory size of/m' ];
	}

	public function capture_fatal_error_contacts() {
		$error = error_get_last();
		if ( ! empty( $error ) ) {
			if ( is_array( $error ) && in_array( $error['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ), true ) ) {

				if ( $this->is_ignorable_error( $error['message'] ) ) {
					return;
				}
				BWF_Logger::get_instance()->log( 'Error logged during the process' . print_r( $error, true ), 'woofunnels_contacts_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

				$current_offset = get_option( '_bwf_contacts_offset', 0 );
				$current_offset ++;
				update_option( '_bwf_contacts_offset', $current_offset );

			}
		}
	}

	public function set_order_id_in_process( $order_id ) {
		$this->order_id_in_process = $order_id;
	}

	public function get_order_id_process() {
		return $this->order_id_in_process;
	}

	public function maybe_re_dispatch_background_process() {
		$this->updater->maybe_re_dispatch_background_process();
	}

	public function maybe_dispatch_contact_table_indexing() {
		$task_list = array(
			'bwf_contacts_v1_0_init_db_setup',
		);

		$update_queued = false;

		foreach ( $task_list as $task ) {

			$this->contacts_updater->push_to_queue( $task );
			$update_queued = true;
		}

		if ( $update_queued ) {

			$this->contacts_updater->save()->dispatch();
		}
	}

	/**
	 * @hooked over `admin_head`
	 * This method takes care of database updating process.
	 * Checks whether there is a need to update the database
	 * Iterates over define callbacks and passes it to background updater class
	 */
	public function maybe_update_database_update_contacts() {
		$current_db_version = get_option( '_bwf_db_version', '0.0.0' );
		if ( version_compare( BWF_DB_VERSION, $current_db_version, '>' ) ) {
			$tables = WooFunnels_DB_Tables::get_instance();
			$tables->add_if_needed();

			/** Empty contact meta values */
			$this->empty_contact_meta_table();
		}
		update_option( '_bwf_db_version', BWF_DB_VERSION, true );
	}

	/**
	 * Truncate the contact meta table
	 * Run when BWF_DB_VERSION is 1.0.3
	 */
	protected function empty_contact_meta_table() {
		global $wpdb;
		$result = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}bwf_contact_meta'", ARRAY_A );
		if ( is_array( $result ) && count( $result ) > 0 ) {
			$wpdb->query( "TRUNCATE TABLE `{$wpdb->prefix}bwf_contact_meta`" );
		}
	}

	public function maybe_flag_old_contacts_indexing() {
		$indexing_option = get_option( '_bwf_migrate_contacts_indexing' );
		if ( ! empty( $indexing_option ) ) {
			return;
		}

		global $wpdb;
		$bwf_tables = get_option( '_bwf_created_tables' );
		if ( ! is_array( $bwf_tables ) || ! in_array( $wpdb->prefix . 'bwf_contact', $bwf_tables, true ) || ! in_array( $wpdb->prefix . 'bwf_contact_meta', $bwf_tables, true ) ) {
			return;
		}

		$contact_count = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}bwf_contact" );
		if ( 0 === absint( $contact_count ) ) {
			return;
		}

		/**
		 * 1 - Pending
		 * 2 - In Progress
		 * 3 - Complete
		 */
		update_option( '_bwf_migrate_contacts_indexing', 1 );
	}
}
