<?php

/**
 * Class that is responsible for pushing , removing and sometimes handling the way notifications comes from the core.
 * @since 1.0.0
 * @package WooFunnels
 * @author woofunnels
 */
class WooFunnels_Admin_Notifications {

	public static $all_notifications;

	/**
	 * Add notification
	 * <br/> This function will add the notification
	 *
	 * @param array $args configuration for notification
	 *
	 * <br/>
	 *
	 * @since 1.0.0
	 */
	public static function add_notification( $args ) {
		$default = array(
			'type'           => 'success',
			'content'        => '',
			'is_dismissible' => true,
			'callback'       => null,
			'show_only'      => false,
		);

		self::$all_notifications[ key( $args ) ] = wp_parse_args( $args[ key( $args ) ], $default );
	}

	/**
	 * Hide notices to show under dashboard
	 * <br/> Its a handler function to any request come to hide the notice
	 * <br/> save the slug in Database so that it wont come again
	 */
	public static function hide_notices() {
		if ( isset( $_GET['woofunnels-hide-notice'] ) && isset( $_GET['_woofunnels_notice_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_text_field( $_GET['_woofunnels_notice_nonce'] ), 'woofunnels_hide_notices_nonce' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'woofunnels' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Cheating huh?', 'woofunnels' ) );
			}

			$hide_notice = sanitize_text_field( $_GET['woofunnels-hide-notice'] );
			self::remove_notice( $hide_notice );
			do_action( 'woofunnels_hide_' . $hide_notice . '_notice' );
		}
	}

	/**
	 * Remove a notice from being displayed
	 *
	 * @param string $name
	 */
	public static function remove_notice( $name ) {
		$has_notice_removed = get_option( 'woofunnels_admin_notices' );
		if ( $has_notice_removed && ! empty( $has_notice_removed ) ) {

			$array_to_push = array_push( $has_notice_removed, $name );
			update_option( 'woofunnels_admin_notices', $array_to_push, false );
		} else {
			update_option( 'woofunnels_admin_notices', array( $name ), false );
		}
	}

	/**
	 * Render all the notifications after filtering
	 * @since 1.0.0
	 */
	public static function render() {
		self::filter_before_render();
		if ( ! is_null( self::$all_notifications ) && ! empty( self::$all_notifications ) ) {
			foreach ( self::$all_notifications as $slug => $notification ) {
				?>

                <div id="message" class="notice notice-<?php echo $notification['type']; ?>"><?php echo wp_kses_post( $notification['content'] ); ?></div>
				<?php
			}
		}
	}

	/**
	 * Filter function that will filter pushed notification against the removed ones
	 */
	public static function filter_before_render() {
		$clone           = self::$all_notifications;
		$get_all_removed = self::get_all_removed();
		if ( ! is_null( self::$all_notifications ) && ! empty( self::$all_notifications ) && ! empty( $get_all_removed ) ) {
			foreach ( self::$all_notifications as $slug => $notification ) {
				if ( in_array( $slug, self::get_all_removed(), true ) ) {
					unset( $clone[ $slug ] );
				}
			}
		}
		self::$all_notifications = $clone;
	}

	/**
	 * Get all removed notifications from database
	 * So that we can filter the push notification with the removed ones
	 * @return array
	 */
	public static function get_all_removed() {
		return get_option( 'woofunnels_admin_notices' );
	}

	/**
	 * Check whether has notification pushed?
	 * @since 1.0.0
	 */
	public static function has_notification( $slug ) {
		if ( is_null( self::$all_notifications ) ) :
			return false;
		endif;

		return array_key_exists( $slug, self::$all_notifications );
	}

}
