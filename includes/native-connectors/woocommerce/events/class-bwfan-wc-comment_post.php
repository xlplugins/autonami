<?php

final class BWFAN_WC_Comment_Post extends BWFAN_Event {
	private static $instance = null;
	public $comment_id = null;
	public $comment_details = null;

	private function __construct() {
		$this->optgroup_label         = esc_html__( 'Reviews', 'wp-marketing-automations' );
		$this->event_name             = esc_html__( 'New Review', 'wp-marketing-automations' );
		$this->event_desc             = esc_html__( 'This event runs after a new review is submitted on a product.', 'wp-marketing-automations' );
		$this->event_merge_tag_groups = array( 'wc_customer', 'wc_product', 'wc_review' );
		$this->event_rule_groups      = array( 'automation', 'wc_comment' );
		$this->priority               = 35;
	}

	public function load_hooks() {
		add_action( 'comment_post', array( $this, 'product_review' ), 10, 2 );
		add_action( 'transition_comment_status', array( $this, 'my_approve_comment_callback' ), 20, 3 );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * This function is the callback function for comment hook
	 *
	 * @param $comment_id
	 * @param $status
	 */
	public function product_review( $comment_id, $status ) {
		if ( 1 === $status ) {
			$this->send_single_product_review_feed( $comment_id );
		}
	}

	/**
	 * This function is fired when a comment is approved or a approved comment is posted
	 *
	 * @param $comment_id
	 */
	public function send_single_product_review_feed( $comment_id ) {
		$this->process( $comment_id );
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $comment_id
	 */
	public function process( $comment_id ) {
		$data               = $this->get_default_data();
		$data['comment_id'] = $comment_id;

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
		$data_to_send                         = [];
		$data_to_send['global']['comment_id'] = $this->comment_details['comment_id'];
		$data_to_send['global']['email']      = $this->comment_details['email'];

		return $data_to_send;
	}

	/**
	 * This function gets fired when state of a comment is changed to approved
	 *
	 * @param $comment
	 */
	public function my_approve_comment_callback( $new_status, $old_status, $comment ) {
		if ( 'approved' === $new_status ) {
			$comment_id = $comment->comment_ID;
			$this->send_single_product_review_feed( $comment_id );
		}
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
		if ( ! is_array( $global_data ) ) {
			return '';
		}
		if ( isset( $global_data['comment_id'] ) ) {
			?>
            <li>
                <strong><?php esc_html_e( 'Comment ID:', 'wp-marketing-automations' ); ?> </strong>
				<?php echo "<a href='" . get_edit_comment_link( $global_data['comment_id'] ) . "' target='blank'>" . esc_html( $global_data['comment_id'] ) . '</a>'; //phpcs:ignore WordPress.Security.EscapeOutput ?>
            </li>
			<?php
		}
		if ( isset( $global_data['email'] ) ) {
			?>
            <li>
                <strong><?php esc_html_e( 'Email:', 'wp-marketing-automations' ); ?> </strong>
				<?php esc_html_e( $global_data['email'] ); ?>
            </li>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$wc_comment_id = BWFAN_Merge_Tag_Loader::get_data( 'wc_comment_id' );
		if ( empty( $wc_comment_id ) || $wc_comment_id !== $task_meta['global']['comment_id'] ) {
			$set_data = array(
				'wc_comment_id'      => $task_meta['global']['comment_id'],
				'email'              => $task_meta['global']['email'],
				'wc_comment_details' => $this->get_comment_feed( $task_meta['global']['comment_id'] ),
			);

			$set_data['user_id']    = $set_data['wc_comment_details']['user_id'];
			$set_data['product_id'] = $set_data['wc_comment_details']['product_id'];

			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
	}

	/**
	 *
	 * This function is a wrapper function and it returns a single feed for comment
	 *
	 * @param $comment_id
	 *
	 * @return array
	 */
	public function get_comment_feed( $comment_id ) {
		$final_data      = array();
		$args            = array(
			'comment__in' => array( $comment_id ),
			'post_type'   => 'product',
		);
		$comment_details = get_comments( $args );
		if ( ! is_array( $comment_details ) || 0 === count( $comment_details ) ) {
			return $final_data;
		}
		$comment_details  = $comment_details[0];
		$single_feed_data = $this->get_single_comment_data( $comment_details );
		if ( ! is_array( $single_feed_data ) || 0 === count( $single_feed_data ) ) {
			return $final_data;
		}
		$final_data = $single_feed_data;

		return $final_data;
	}

	/**
	 *
	 * This function makes a single feed data for a comment
	 *
	 * @param $comment_details
	 *
	 * @return array
	 */

	public function get_single_comment_data( $comment_details ) {
		$comment_details                        = (array) $comment_details;
		$single_feed_details                    = array();
		$post_id                                = $comment_details['comment_post_ID'];
		$product_details                        = get_post( $post_id );
		$rating                                 = get_comment_meta( $comment_details['comment_ID'], 'rating', true );
		$single_feed_details['product_id']      = $product_details->ID;
		$single_feed_details['product_name']    = $product_details->post_title;
		$single_feed_details['full_name']       = $this->capitalize_word( $comment_details['comment_author'] );
		$single_feed_details['comment_message'] = $comment_details['comment_content'];
		$single_feed_details['email']           = $comment_details['comment_author_email'];
		$single_feed_details['ip']              = $comment_details['comment_author_IP'];
		$single_feed_details['rating_star']     = $rating;
		$single_feed_details['rating_number']   = $rating;
		$single_feed_details['is_verified']     = get_comment_meta( $comment_details['comment_ID'], 'verified', true );
		$single_feed_details['user_id']         = $comment_details['user_id'];
		$single_feed_details['comment_id']      = $comment_details['comment_ID'];
		$single_feed_details['comment_date']    = $comment_details['comment_date_gmt'];

		return $single_feed_details;
	}

	public function capitalize_word( $text ) {
		return ucwords( strtolower( $text ) );
	}

	/**
	 * Recalculate action's execution time with respect to order date.
	 * eg.
	 * today is 22 jan.
	 * order was placed on 17 jan.
	 * user set an email to send after 10 days of order placing.
	 * user setup the sync process.
	 * email should be sent on 27 Jan as the order date was 17 jan.
	 *
	 * @param $actions
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function recalculate_actions_time( $actions ) {
		$comment_date = $this->comment_details['comment_date'];
		$comment_date = DateTime::createFromFormat( 'Y-m-d H:i:s', $comment_date );
		$actions      = $this->calculate_actions_time( $actions, $comment_date );

		return $actions;
	}


	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$comment_id            = BWFAN_Common::$events_async_data['comment_id'];
		$this->comment_id      = $comment_id;
		$this->comment_details = $this->get_comment_feed( $this->comment_id );
		if ( ! isset( $this->comment_details['product_id'] ) ) {
			return false;
		}

		return $this->run_automations();
	}

	/**
	 * Set up rules data
	 *
	 * @param $value
	 */
	public function pre_executable_actions( $value ) {
		BWFAN_Core()->rules->setRulesData( $this->event_automation_id, 'automation_id' );
		BWFAN_Core()->rules->setRulesData( $this->comment_details, 'wc_comment' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->get_email_event(), $this->get_user_id_event() ), 'bwf_customer' );
	}

	public function get_email_event() {
		if ( ! isset( $this->comment_details['email'] ) ) {
			return false;
		}

		return $this->comment_details['email'];
	}

	public function get_user_id_event() {
		if ( ! isset( $this->comment_details['user_id'] ) ) {
			return false;
		}

		return $this->comment_details['user_id'];
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_woocommerce_active() ) {
	return 'BWFAN_WC_Comment_Post';
}
