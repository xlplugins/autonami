<?php

final class BWFAN_AB_Cart_Abandoned extends BWFAN_Event {
	private static $instance = null;
	public $emails = [];
	public $tokens = [];
	public $cart_items = [];
	public $abandoned_email = false;
	public $abandoned_id = null;
	public $token = null;
	public $cart_item = null;
	public $user_id = false;
	public $abandoned_data = array();

	public function __construct( $source_slug ) {
		$this->source_type            = $source_slug;
		$this->optgroup_label         = __( 'Cart', 'wp-marketing-automations' );
		$this->event_name             = __( 'Cart Abandoned', 'wp-marketing-automations' );
		$this->event_desc             = __( 'This automation would trigger when a user abandoned the cart.', 'wp-marketing-automations' );
		$this->event_merge_tag_groups = array( 'wc_ab_cart', 'bwf_contact' );
		$this->event_rule_groups      = array( 'ab_cart', 'aerocheckout', 'bwf_contact_segments', 'bwf_contact_fields', 'bwf_contact_user', 'bwf_contact_wc', 'bwf_contact_geo' );
		$this->support_lang           = true;
		$this->priority               = 5;
		$this->customer_email_tag     = '{{cart_billing_email}}';
	}

	public function load_hooks() {
	}

	public static function get_instance( $source_slug ) {
		if ( null === self::$instance ) {
			self::$instance = new self( $source_slug );
		}

		return self::$instance;
	}

	/**
	 * Get all the abandoned rows from db table. It runs at every 2 minutes.
	 */
	public function get_eligible_abandoned_rows() {
		global $wpdb;
		$global_settings           = BWFAN_Common::get_global_settings();
		$abandoned_time_in_minutes = intval( $global_settings['bwfan_ab_init_wait_time'] );

		/** Status 0: Pending, 4: Re-Scheduled */
		$query                  = $wpdb->prepare( 'SELECT * FROM {table_name} WHERE TIMESTAMPDIFF(MINUTE,last_modified,UTC_TIMESTAMP) >= %d AND status IN (0,4)', $abandoned_time_in_minutes );
		$active_abandoned_carts = BWFAN_Model_Abandonedcarts::get_results( $query );

		if ( ! is_array( $active_abandoned_carts ) || count( $active_abandoned_carts ) === 0 ) {
			return;
		}

		/** Status 1: In-Progress (Automations Found), 3: Pending (No Tasks Found) */
		$active_automations = count( BWFAN_Core()->automations->get_active_automations_for_event( $this->get_slug() ) );

		$ids = array_column( $active_abandoned_carts, 'ID', 'ID' );

		if ( 0 === absint( $active_automations ) ) {
			BWFAN_Common::update_abandoned_rows( $ids, 3 );

			return;
		}

		$this->get_abandoned_automations( $active_abandoned_carts );
	}

	/**
	 * Get all the automations related to abandoned rows in abandoned table
	 *
	 * @param $active_abandoned_carts
	 */
	public function get_abandoned_automations( $active_abandoned_carts ) {
		BWFAN_Core()->public->load_active_automations( $this->get_slug() );

		foreach ( $active_abandoned_carts as $active_abandoned_cart ) {
			BWFAN_Common::maybe_create_abandoned_contact( $active_abandoned_cart );// create contact at the time of abandonment
			$this->process( $active_abandoned_cart );
		}
	}

	/**
	 * Set up rules data
	 *
	 * @param $value
	 */
	public function pre_executable_actions( $value ) {
		BWFAN_Core()->rules->setRulesData( $this->abandoned_data, 'abandoned_data' );
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $abandoned_cart
	 *
	 * @return array|bool|void
	 */
	public function process( $abandoned_cart ) {
		$this->abandoned_id   = $abandoned_cart['ID'];
		$this->abandoned_data = BWFAN_Model_Abandonedcarts::get( $this->abandoned_id );
		if ( ! is_array( $this->abandoned_data ) ) {
			return;
		}
		$this->abandoned_email = $this->abandoned_data['email'];
		$this->token           = $abandoned_cart['token'];
		$this->cart_item       = $this->abandoned_data['items'];
		$this->user_id         = $abandoned_cart['user_id'];

		return $this->run_automations();
	}

	/**
	 * Override method to change the state of Cart based on Automations found
	 * @return array|bool
	 */
	public function run_automations() {
		if ( ! is_array( $this->automations_arr ) || count( $this->automations_arr ) === 0 ) {
			/** Status 3 for Captured, which means Abandoned happen, but no Automations found */
			BWFAN_Common::update_abandoned_rows( array( $this->abandoned_id ), 3 );

			return false;
		}

		$automation_actions = [];

		foreach ( $this->automations_arr as $automation_id => $automation_data ) {
			if ( $this->get_slug() !== $automation_data['event'] || 0 !== intval( $automation_data['requires_update'] ) ) {
				continue;
			}
			$ran_actions = $this->handle_single_automation_run( $automation_data, $automation_id );

			$automation_actions[ $automation_id ] = $ran_actions;
		}

		if ( 0 === array_sum( $automation_actions ) ) {
			/** We found no tasks to create. So, setting status 3 i.e. Pending (No Tasks Found) */
			BWFAN_Common::update_abandoned_rows( array( $this->abandoned_id ), 3 );
		} else {
			/** Updating carts to in-progress i.e. 1 state */
			BWFAN_Common::update_abandoned_rows( array( $this->abandoned_id ), 1 );
		}

		return $automation_actions;
	}

	/**
	 * Override method to change the state of Cart based on Tasks to be created
	 *
	 * @param $automation_data
	 * @param $automation_id
	 *
	 * @return bool|int
	 */
	public function handle_single_automation_run( $automation_data, $automation_id ) {
		$this->event_automation_id = $automation_id;

		/** Setup the rules data */
		$this->pre_executable_actions( $automation_data );

		/** get all the actions which have passed the rules */
		$actions = $this->get_executable_actions( $automation_data );

		if ( ! isset( $actions['actions'] ) || ! is_array( $actions['actions'] ) || count( $actions['actions'] ) === 0 ) {
			return 0;
		}

		$event_data = $this->get_automation_event_data( $automation_data );

		try {
			/** Register all those tasks which passed through rules or which are direct actions. The following function is present in every event class. */
			$this->register_tasks( $automation_id, $actions['actions'], $event_data );
		} catch ( Exception $exception ) {
			BWFAN_Core()->logger->log( 'Register task function not overrided by child class' . get_class( $this ), $this->log_type );
		}

		return count( $actions['actions'] );
	}

	/**
	 * Registers the tasks for current event.
	 *
	 * @param $automation_id
	 * @param $integration_data
	 * @param $event_data
	 */
	public function register_tasks( $automation_id, $integration_data, $event_data ) {
		$data_to_send = $this->get_event_data();
		add_action( 'bwfan_task_created_ab_cart_abandoned', [ $this, 'update_task_meta' ], 10, 2 );

		$this->create_tasks( $automation_id, $integration_data, $event_data, $data_to_send );
	}

	public function get_event_data() {
		$data_to_send                                = [];
		$data_to_send['global']['email']             = $this->abandoned_email;
		$data_to_send['global']['cart_abandoned_id'] = $this->abandoned_id;

		return $data_to_send;
	}

	/**
	 * If any event has email and it does not contain order object, then following method must be overridden by child event class.
	 * Return email
	 * @return bool
	 */
	public function get_email_event() {
		return $this->abandoned_email;
	}

	/**
	 * If any event has user id and it does not contain order object, then following method must be overridden by child event class.
	 * Return user id
	 * @return bool
	 */
	public function get_user_id_event() {
		return $this->user_id;
	}

	public function update_task_meta( $index, $task_id ) {
		BWFAN_Core()->tasks->insert_taskmeta( $task_id, 'c_a_id', $this->abandoned_id );
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
		?>

        <li>
            <strong><?php esc_html_e( 'Abandoned Email:', 'wp-marketing-automations' ); ?> </strong>
			<?php echo "<a href='" . site_url( 'wp-admin/admin.php?page=autonami&path=/carts/recoverable/' . $global_data['cart_abandoned_id'] . '/tasks' ) . "'>" . esc_html( $global_data['email'] ) . '</a>'; //phpcs:ignore WordPress.Security.EscapeOutput ?>
        </li>
		<?php
		return ob_get_clean();
	}

	public function validate_event( $task_details ) {
		$cart_id   = $task_details['processed_data']['cart_abandoned_id'];
		$cart_data = BWFAN_Model_Abandonedcarts::get( $cart_id );

		$email = is_email( $cart_data['email'] ) ? $cart_data['email'] : $task_details['processed_data']['email'];
		if ( ! is_email( $email ) ) {
			return $this->get_automation_event_success();
		}

		/** If order is pending or failed then cart is valid so continue */
		$orders = wc_get_orders( array(
			'billing_email' => $email,
			'date_after'    => $cart_data['created_time'],
		) );

		/** empty orders than return **/
		if ( empty( $orders ) ) {
			return $this->get_automation_event_success();
		}

		$orders = array_filter( $orders, function ( $order ) {
			$failed_statuses = [ 'pending', 'failed', 'cancelled', 'trash' ];
			if ( ! in_array( $order->get_status(), $failed_statuses, true ) ) {
				return true;
			}

			return false;
		} );

		if ( empty( $orders ) ) {
			return $this->get_automation_event_success();
		}

		/** in case order is not an instance than return success **/
		if ( ! $orders[0] instanceof WC_Order ) {
			return $this->get_automation_event_success();
		}

		$order_id = $orders[0]->get_id();

		/** Order is placed, discard the task execution */
		$automation_id = $task_details['processed_data']['automation_id'];

		/** Attributing the sale */
		update_post_meta( $order_id, '_bwfan_ab_cart_recovered_a_id', $automation_id );

		$task_data_meta = BWFAN_Model_Tasks::get_task_with_data( $task_details['task_id'] );
		$track_id       = $task_data_meta['meta']['t_track_id'];
		if ( ! empty( $track_id ) ) {
			update_post_meta( $order_id, '_bwfan_ab_cart_recovered_t_id', $track_id );
		}

		update_post_meta( $order_id, '_bwfan_recovered_ab_id', $cart_id );

		$cart_tasks = BWFAN_Common::get_schedule_task_by_email( [ $automation_id ], $cart_data['email'] );
		$cart_tasks = $cart_tasks[ $automation_id ];

		$cart_tasks = array_map( function ( $v ) {
			return $v['ID'];
		}, $cart_tasks );

		$fail_resp = array(
			'status'  => 4,
			'message' => 'Cart is recovered already',
		);

		if ( empty( $cart_tasks ) ) {
			BWFAN_Model_Abandonedcarts::delete( $cart_id );

			return $fail_resp;
		}

		/** Delete the tasks */

		global $wpdb;
		$tasks_count = count( $cart_tasks );

		if ( in_array( $task_details['task_id'], $cart_tasks ) ) {
			$cart_tasks = array_diff( $cart_tasks, [ $task_details['task_id'] ] );
			sort( $cart_tasks );
			$tasks_count = count( $cart_tasks );
		}

		$prepare_placeholders = array_fill( 0, $tasks_count, '%d' );
		$prepare_placeholders = implode( ', ', $prepare_placeholders );

		/** Delete Tasks */
		$sql_query = "DELETE FROM {table_name} WHERE `ID` IN ($prepare_placeholders)";
		$sql_query = $wpdb->prepare( $sql_query, $cart_tasks ); // WPCS: unprepared SQL OK
		BWFAN_Model_Tasks::query( $sql_query );

		/** Delete Tasks Meta */
		$sql_query = "Delete FROM {table_name} WHERE `bwfan_task_id` IN ($prepare_placeholders)";
		$sql_query = $wpdb->prepare( $sql_query, $cart_tasks ); // WPCS: unprepared SQL OK
		BWFAN_Model_Taskmeta::query( $sql_query );

		/** Delete the cart */
		BWFAN_Model_Abandonedcarts::delete( $cart_id );

		return $fail_resp;
	}

	/**
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$cart_abandoned_id = BWFAN_Merge_Tag_Loader::get_data( 'cart_abandoned_id' );
		if ( empty( $cart_abandoned_id ) || $cart_abandoned_id !== $task_meta['global']['cart_abandoned_id'] ) {
			$set_data = array(
				'cart_abandoned_id' => $task_meta['global']['cart_abandoned_id'],
				'email'             => $task_meta['global']['email'],
				'cart_details'      => BWFAN_Model_Abandonedcarts::get( $task_meta['global']['cart_abandoned_id'] ),
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
	}

	/**
	 * checking if the abandoned cart contain empty cart
	 */
	public function validate_event_data_before_executing_task( $data ) {
		return $this->validate_cart_details( $data );
	}

	/** validating abandoned cart contain item or not
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function validate_cart_details( $data ) {
		if ( ! isset( $data['cart_abandoned_id'] ) ) {
			return false;
		}

		$cart_data  = BWFAN_Model_Abandonedcarts::get( $data['cart_abandoned_id'] );
		$cart_items = maybe_unserialize( $cart_data['items'] );

		if ( empty( $cart_items ) ) {
			$this->message_validate_event = __( 'Cart does not contain any item.', 'wp-marketing-automations' );

			return false;
		}

		return true;
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_woocommerce_active() ) {
	return 'BWFAN_AB_Cart_Abandoned';
}
