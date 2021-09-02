<?php
defined( 'ABSPATH' ) || exit;

class WooFunnels_Notifications {

	private static $instance = null;
	/** Notification listed array */
	protected $notifications_list = [];

	public function __construct() {

		/** Inline styling for woofunnels notifications */
		add_action( 'admin_head', [ $this, 'notification_inline_style' ] );

		/** Inline javascript for woofunnels notifications */
		add_action( 'admin_footer', [ $this, 'notification_inline_script' ] );

		/** Ajax function dismiss to  woofunnels notifications */
		add_action( 'wp_ajax_wf_dismiss_link', array( $this, 'wf_dismiss_link' ) );
	}

	/**
	 * Instance of class
	 * @return WooFunnels_Notifications|null
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new WooFunnels_Notifications();
		}

		return self::$instance;
	}

	/**
	 * This will return all the notifications or the notifications based on the group id.
	 *
	 * @param string $notification_group Optional
	 *
	 * @return array
	 */
	public function get_all_notifications( $notification_group = '' ) {
		if ( '' !== $notification_group && isset( $this->notifications_list[ $notification_group ] ) && ( is_array( $this->notifications_list[ $notification_group ] ) && count( $this->notifications_list[ $notification_group ] ) > 0 ) ) {
			$notification_group_arr                        = [];
			$notification_group_arr[ $notification_group ] = $this->notifications_list[ $notification_group ];

			return $notification_group_arr;
		}

		return $this->notifications_list;
	}

	/**
	 * This is used to register a notification
	 *
	 * @param array $notification_value
	 * @param string $group
	 *
	 * @return array
	 */
	public function register_notification( $notification_value = [], $group = '' ) {
		$error = [
			'error' => 'Notification not register checkout your notification array or group name    ',
		];

		if ( ( ! is_array( $notification_value ) || sizeof( $notification_value ) >= 2 ) || '' === $group ) {
			$error['error'] = 'Check Your notification array format or notification group name';

			return $error;
		}

		if ( ! is_array( $notification_value ) || count( $notification_value ) === 0 ) {
			return $error;
		}

		$notice_key = key( $notification_value );

		if ( isset( $this->notifications_list[ $group ][ $notice_key ] ) ) {
			return $error;
		}

		$this->notifications_list[ $group ][ $notice_key ] = $notification_value[ $notice_key ];

		return [
			'success' => $notice_key . ' Key Set',
		];
	}

	/**
	 * This is used to deregister a notification based on notification key and group
	 *
	 * @param string $notification_key
	 * @param string $notification_group
	 *
	 * @return array
	 */
	public function deregister_notification( $notification_key = '', $notification_group = '' ) {
		$error = [
			'error' => $notification_key . ' Key or notification group may be not Available.',
		];
		if ( '' === $notification_key || '' === $notification_group ) {
			$error['error'] = 'Check your notification key and their group. Both are required for deletion';

			return $error;
		}
		if ( ! isset( $this->notifications_list[ $notification_group ][ $notification_key ] ) || ! is_array( $this->notifications_list[ $notification_group ][ $notification_key ] ) || count( $this->notifications_list[ $notification_group ][ $notification_key ] ) === 0 ) {
			return $error;
		}

		unset( $this->notifications_list[ $notification_group ][ $notification_key ] );

		return [
			'success' => $notification_key . ' Notices has been removed',
		];
	}

	/**
	 * Display internal CSS
	 */
	public function notification_inline_style() {
		?>
        <style>

            .notice.notice-error.wf_notice_cache_wrap {
                position: relative;
                overflow: hidden;
            }

            .wf_notice_cache_wrap a::before {
                position: relative;
                top: 18px;
                left: -20px;
                -webkit-transition: all .1s ease-in-out;
                transition: all .1s ease-in-out;
            }

            .wf_notice_cache_wrap a.notice-dismiss {
                position: static;
                float: right;
                top: 0;
                right: 0;
                padding: 0 15px 10px 28px;
                margin-top: -10px;
                font-size: 13px;
                line-height: 1.23076923;
                text-decoration: none;
            }

            /* cache setting */
            .wf_notification_list_wrap h3.hndle {
                text-align: left;
            }

            .wf_notification_wrap .swal2-icon.swal2-warning {
                font-size: 6px;
                line-height: 33px;
            }

            .wf_notification_content_sec p {
                font-size: 14px;
                line-height: 1.5;
                position: relative;
                padding-left: 20px;
                margin-bottom: 11px;
            }

            .wf_notification_links a {
                font-size: 15px;
                font-weight: 100;
                line-height: 1.5;
            }

            .wf_notification_wrap .wf_notification_btn_wrap a:hover {
                background: #fafafa;
                border-color: #999;
                color: #23282d;
            }

            .wf_notification_wrap .inside {
                padding: 0px 15px 30px;
            }

            .wf_notification_btn_wrap {
                margin-bottom: 10px;

            }

            .wf_overlay_active {
                z-index: 1;
                position: absolute;
                left: 0;
                right: 0;
                bottom: 0;
                background: #ffffff5e;
                top: 0;
                display: none;
            }

            .wf_overlay_active.wf_show {
                display: block;
            }

            .wf_notification_wrap a.notice-dismiss {
                bottom: auto;
                top: auto;
                position: relative;
                float: none;
                right: 0;
                padding: 0;
                margin-top: 0;
                font-size: 13px;
                line-height: 20px;
                text-decoration: none;
                height: 20px;
                display: inline-block;
                margin-left: 20px;
            }

            .wf_notice_dismiss_link_wrap {
                text-align: center;
            }

            .wf_notification_wrap a.notice-dismiss:before {
                position: absolute;
                top: 0px;
                left: -20px;
                -webkit-transition: all .1s ease-in-out;
                transition: all .1s ease-in-out;
            }

            .wf_notification_content_sec:last-child {
                margin-bottom: 0;
            }

            .wf_notification_content_sec {
                margin-bottom: 30px;
                position: relative;
            }

            .wf_notification_wrap.closed a.notice-dismiss {
                display: none;
            }

            .wf_notification_wrap .wf_notification_btn_wrap a:last-child {
                margin-bottom: 0;
            }

            .wf_notification_wrap .wf_notification_btn_wrap a {
                color: #555;
                border-color: #cccccc;
                background: #f7f7f7;
                box-shadow: 0 1px 0 #cccccc;
                vertical-align: top;
                display: block;
                text-decoration: none;
                font-size: 15px;
                line-height: 20px;
                padding: 8px 10px;
                cursor: pointer;
                margin: 0 0 11px;
                border-width: 1px;
                border-style: solid;
                -webkit-appearance: none;
                border-radius: 3px;
                white-space: nowrap;
                box-sizing: border-box;
                text-align: center;
            }

            .wf_notification_wrap a.notice-dismiss + span {
                display: block;
                margin-top: 2px;
                font-size: 14px;
                line-height: 20px;
                text-transform: capitalize;
            }

            .wf_notification_content_sec .wf_notification_html p:first-child:before {
                width: 10px;
                content: '';
                height: 10px;
                background: red;
                position: absolute;
                left: 0;
                border-radius: 50%;
                top: 6px;
            }

            .wf_notification_content_sec.wf_warning .wf_notification_html p:first-child:before {
                background-color: orange;
            }

            .wf_notification_content_sec.wf_success .wf_notification_html > p:first-child:before {
                background-color: green;
            }

            .wf_notification_content_sec.wf_error .wf_notification_html > p:first-child:before {
                background-color: red;
            }

            .wf_notification_links a {
                font-size: 15px;
                font-weight: 100;
                line-height: 1.5;
            }

        </style>
		<?php
	}


	/**
	 * Display internal scripts
	 */
	public function notification_inline_script() {

		?>
        <script>

            (function ($) {
                function wf_dismiss_notice() {

                    jQuery(document).on('click', '.wf_notice_dismiss_link_wrap .notice-dismiss', function (e) {

                        var $this = jQuery(this);
                        var noticeGroup = $this.parents('.wf_notification_content_sec').attr('wf-noti-group');
                        var noticekey = $this.parents('.wf_notification_content_sec').attr('wf-noti-key');
                        var wf_count = $this.attr('wf-notice-count');
                        $this.parents('.wf_notification_content_sec').find('.wf_overlay_active ').addClass('wf_show');

                        if (noticeGroup === '' && noticeGroup === '') {
                            return;
                        }

                        jQuery.ajax({
                            url: ajaxurl,
                            type: "POST",
                            data: {
                                action: 'wf_dismiss_link',
                                noticeGroup: noticeGroup,
                                noticekey: noticekey,
                                _nonce: '<?php echo wp_create_nonce( 'bwf_notice_dismiss' ); ?>',
                            },
                            success: function (result) {
                                if (result.status === 'success' && result.success === 'true') {

                                    /** check if the right notification area has only 1 notification */
                                    if (jQuery(".wf_notification_wrap .wf_notification_content_sec").length == 1) {
                                        $this.parents('.wf_notification_list_wrap').remove();
                                        return;
                                    }

                                    /** remove the self notification html after 500 ms delay */
                                    setTimeout(function () {
                                        $this.parents('.wf_notification_content_sec').remove();
                                    }, 500);

                                } else {
                                    $this.parents('.wf_notification_content_sec').find('.wf_overlay_active ').removeClass('wf_show');
                                    jQuery(".wf_notice_dismiss_link_wrap").append("<span >" + result.msg + "</span >")
                                }
                            }
                        });
                    });
                }

                jQuery(document).ready(function () {
                    wf_dismiss_notice();
                });
            })(jQuery);

        </script>

		<?php
	}

	/**
	 * Dismiss the notification
	 */
	public function wf_dismiss_link() {
		$results = array(
			'status'  => 'failed',
			'success' => 'false',
			'msg'     => 'Problem With dismiss',
		);

		check_ajax_referer( 'bwf_notice_dismiss', '_nonce' );

		if ( ( isset( $_POST['noticeGroup'] ) && $_POST['noticeGroup'] !== '' ) && ( isset( $_POST['noticekey'] ) && $_POST['noticekey'] !== '' ) ) {

			$noticeGroup     = sanitize_text_field( $_POST['noticeGroup'] );
			$noticekey       = str_replace( 'wf-', '', sanitize_text_field( $_POST['noticekey'] ) );
			$notices_display = get_option( 'wf_notification_list_' . $noticeGroup, [] );
			$notice          = $this->get_notification( $noticekey, $noticeGroup );

			if ( ! is_array( $notices_display ) ) {
				unset( $notices_display );
				$notices_display = [];
			}

			if ( is_array( $notice ) && count( $notice ) > 0 ) {
				$notices_display[] = $noticekey;
				update_option( 'wf_notification_list_' . $noticeGroup, $notices_display );
			}

			$results = array(
				'status'  => 'success',
				'success' => 'true',
				'msg'     => $noticekey . ' Notification Deleted',
			);
		}

		wp_send_json( $results );
	}

	/**
	 * This will return the notification based on notification key and the group id
	 *
	 * @param string $notification_key
	 * @param string $notification_group
	 *
	 * @return array
	 */
	public function get_notification( $notification_key = '', $notification_group = '' ) {
		$error = [
			'error' => $notification_key . ' Key or Notification group may be Not Available.',
		];
		if ( '' === $notification_key || '' === $notification_group ) {
			$error['error'] = 'Check your Notification Key and Their group. Both are required';
		}
		if ( isset( $this->notifications_list[ $notification_group ][ $notification_key ] ) && is_array( $this->notifications_list[ $notification_group ][ $notification_key ] ) && count( $this->notifications_list[ $notification_group ][ $notification_key ] ) > 0 ) {
			return $this->notifications_list[ $notification_group ][ $notification_key ];
		}

		return $error;
	}

	/**
	 * Return the updated dismiss notification keys
	 *
	 * @param $group
	 *
	 * @return array|mixed|void
	 */
	public function get_dismiss_notification_key( $group ) {
		if ( '' === $group ) {
			return [
				'error' => 'Need to a notice group for update key',
			];
		}
		$notices_display = get_option( 'wf_notification_list_' . $group, [] );

		if ( ! is_array( $notices_display ) ) {
			$notices_display = [];
		}

		return $notices_display;
	}

	/**
	 * Display the notifications HTML
	 *
	 * @param $notifications_list
	 */
	public function get_notification_html( $notifications_list ) {
		include dirname( dirname( __FILE__ ) ) . '/views/woofunnels-notifications.php';
	}

}
