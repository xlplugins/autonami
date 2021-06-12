<?php

final class BWFAN_WP_User_Creation extends BWFAN_Event {
	private static $instance = null;
	/** @var WP_User $user */
	public $user = null;
	public $user_id = null;

	private function __construct() {
		$this->optgroup_label         = esc_html__( 'User', 'wp-marketing-automations' );
		$this->event_name             = esc_html__( 'User Creation', 'wp-marketing-automations' );
		$this->event_desc             = esc_html__( 'This event runs after a new user is created.', 'wp-marketing-automations' );
		$this->event_merge_tag_groups = array( 'wc_customer' );
		$this->event_rule_groups      = array( 'wp_user' );
		$this->priority               = 105;
	}

	public function load_hooks() {
		add_action( 'user_register', [ $this, 'user_created' ], 10, 1 );
	}

	/**
	 * @return BWFAN_WP_User_Creation|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Set up rules data
	 *
	 * @param $value
	 */
	public function pre_executable_actions( $value ) {
		BWFAN_Core()->rules->setRulesData( $this->user, 'wp_user' );
		BWFAN_Core()->rules->setRulesData( $this->user_id, 'user_id' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->user->user_email, $this->user_id ), 'bwf_customer' );
	}

	public function user_created( $user_id ) {
		$this->process( [
			'user_id' => $user_id,
		] );
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $details
	 */
	public function process( $details ) {
		$data            = $this->get_default_data();
		$data['details'] = $details;

		$this->send_async_call( $data );
	}

	/**
	 * Registers the tasks for current event.
	 *
	 * @param $automation_id
	 * @param $integration_data
	 * @param $event_data
	 */
	public function register_tasks( $automation_id, $integration_data, $event_data ) {
		if ( ! is_array( $integration_data ) ) {
			return;
		}
		$data_to_send = $this->get_event_data();

		$this->create_tasks( $automation_id, $integration_data, $event_data, $data_to_send );
	}

	public function get_event_data() {
		$data_to_send                      = [];
		$data_to_send['global']['user_id'] = $this->user_id;
		$data_to_send['global']['email']   = is_object( $this->user ) ? $this->user->user_email : '';

		return $data_to_send;
	}

	/**
	 * Make the view data for the current event which will be shown in task listing screen.
	 *
	 * @param $global_data
	 *
	 * @return false|string
	 */
	public function get_task_view( $global_data ) {
		ob_start();
		$user_data = get_userdata( $global_data['user_id'] );
		?>
        <li>
            <strong><?php esc_html_e( 'User:', 'wp-marketing-automations' ); ?> </strong>
            <a target="_blank" href="<?php echo admin_url( 'user-edit.php?user_id=' . $global_data['user_id'] ); //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo esc_html__( $user_data->user_nicename ); ?></a>
        </li>
        <li>
            <strong><?php esc_html_e( 'Email:', 'wp-marketing-automations' ); ?> </strong>
            <span><?php esc_html_e( $global_data['email'] ); ?></span>
        </li>
		<?php
		return ob_get_clean();
	}

	public function get_email_event() {
		if ( $this->user instanceof WP_User ) {
			return $this->user->user_email;
		}

		if ( ! empty( absint( $this->user_id ) ) ) {
			$user = get_user_by( 'id', absint( $this->user_id ) );

			return false !== $user ? $user->user_email : false;
		}

		return false;
	}

	public function get_user_id_event() {
		if ( ! empty( absint( $this->user_id ) ) ) {
			return absint( $this->user_id );
		}

		if ( $this->user instanceof WP_User ) {
			return $this->user->ID;
		}

		return false;
	}

	/**
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$get_data = BWFAN_Merge_Tag_Loader::get_data( 'user_id' );
		if ( empty( $get_data ) || intval( $get_data ) !== intval( $task_meta['global']['user_id'] ) ) {
			$set_data = array(
				'user_id' => intval( $task_meta['global']['user_id'] ),
				'email'   => $task_meta['global']['email'],
				'wp_user' => get_user_by( 'ID', $task_meta['global']['user_id'] ),
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$details       = BWFAN_Common::$events_async_data['details'];
		$this->user_id = intval( $details['user_id'] );
		$this->user    = get_user_by( 'ID', $this->user_id );

		return $this->run_automations();
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
return 'BWFAN_WP_User_Creation';
