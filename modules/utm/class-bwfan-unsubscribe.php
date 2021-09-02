<?php

class BWFAN_unsubscribe {

	private static $ins = null;

	public function __construct() {

		/** Shortcodes for unsubscribe */
		add_shortcode( 'bwfan_unsubscribe_button', array( $this, 'bwfan_unsubscribe_button' ) );
		add_shortcode( 'wfan_unsubscribe_button', array( $this, 'bwfan_unsubscribe_button' ) );
		add_shortcode( 'bwfan_subscriber_recipient', array( $this, 'bwfan_subscriber_recipient' ) );
		add_shortcode( 'wfan_contact_email', array( $this, 'bwfan_subscriber_recipient' ) );
		add_shortcode( 'bwfan_subscriber_name', array( $this, 'bwfan_subscriber_name' ) );
		add_shortcode( 'wfan_contact_name', array( $this, 'bwfan_subscriber_name' ) );

		add_action( 'bwfan_db_1_0_tables_created', array( $this, 'create_unsubscribe_sample_page' ) );

		add_action( 'wp_head', array( $this, 'unsubscribe_page_non_crawlable' ) );

		/** Ajax Calls */
		add_action( 'wp_ajax_bwfan_select_unsubscribe_page', array( $this, 'bwfan_select_unsubscribe_page' ) );
		add_action( 'wp_ajax_bwfan_unsubscribe_user', array( $this, 'bwfan_unsubscribe_user' ) );
		add_action( 'wp_ajax_nopriv_bwfan_unsubscribe_user', array( $this, 'bwfan_unsubscribe_user' ) );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function bwfan_unsubscribe_button( $attrs ) {
		$atts = shortcode_atts(
			array(
				'label' => 'Update my preference',
			),
			$attrs
		);

		ob_start();
		echo "<style type='text/css'>
			a#bwfan_unsubscribe {
			    text-shadow: none;
			    display: inline-block;
			    padding: 15px 20px;
			    cursor: pointer;
			    text-decoration: none !important;
			}
		</style>";

		echo '<div>';
		$this->print_unsubscribe_lists();

		echo '<a id="bwfan_unsubscribe" class="button-primary button" href="#">' . esc_html__( $atts['label'] ) . '</a>';
		if ( isset( $_GET['automation_id'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			echo '<input type="hidden" id="bwfan_automation_id" value="' . esc_attr__( sanitize_text_field( $_GET['automation_id'] ) ) . '">'; // WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		if ( isset( $_GET['bid'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			echo '<input type="hidden" id="bwfan_broadcast_id" value="' . esc_attr__( sanitize_text_field( $_GET['bid'] ) ) . '">'; // WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		if ( isset( $_GET['fid'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			echo '<input type="hidden" id="bwfan_form_feed_id" value="' . esc_attr__( sanitize_text_field( $_GET['fid'] ) ) . '">'; // WordPress.CSRF.NonceVerification.NoNonceVerification
		}
		echo '<input type="hidden" id="bwfan_unsubscribe_nonce" value="' . esc_attr( wp_create_nonce( 'bwfan-unsubscribe-nonce' ) ) . '" name="bwfan_unsubscribe_nonce">';
		echo '</div>';

		return ob_get_clean();
	}

	public function print_unsubscribe_lists() {
		if ( ! bwfan_is_autonami_pro_active() ) {
			$this->only_unsubscribe_from_all_lists_html();

			return false;
		}

		$settings = BWFAN_Common::get_global_settings();
		$enabled  = isset( $settings['bwfan_unsubscribe_lists_enable'] ) ? $settings['bwfan_unsubscribe_lists_enable'] : 0;
		if ( 0 === absint( $enabled ) ) {
			$this->only_unsubscribe_from_all_lists_html();

			return false;
		}

		$lists = $settings['bwfan_unsubscribe_public_lists'];
		if ( empty( $lists ) || ! is_array( $lists ) ) {
			$this->only_unsubscribe_from_all_lists_html();

			return false;
		}

		$contact            = isset( $_GET['subscriber_recipient'] ) ? BWFCRM_Common::get_contact_by_email_or_phone( $_GET['subscriber_recipient'] ) : false;
		$is_unsubscribed    = false;
		$unsubscribed_lists = array();
		if ( $contact instanceof BWFCRM_Contact ) {
			$not_in_lists  = array();
			$contact_lists = $contact->get_lists();
			if ( ! empty( $contact_lists ) && is_array( $contact_lists ) ) {
				$not_in_lists = array_values( array_diff( $lists, $contact_lists ) );
			}

			if ( empty( $lists ) ) {
				$this->only_unsubscribe_from_all_lists_html( $contact );

				return false;
			}

			$is_unsubscribed    = BWFCRM_Contact::$DISPLAY_STATUS_UNSUBSCRIBED === $contact->get_display_status();
			$unsubscribed_lists = $contact->get_field_by_slug( 'unsubscribed-lists' );
			$unsubscribed_lists = empty( $unsubscribed_lists ) ? array() : json_decode( $unsubscribed_lists, true );
			$unsubscribed_lists = array_unique( array_merge( $unsubscribed_lists, $not_in_lists ) );
			$unsubscribed_lists = array_map( 'absint', $unsubscribed_lists );
		}

		$lists = BWFCRM_Lists::get_lists( $lists );
		$this->unsubscribe_lists_html( $lists, $unsubscribed_lists, $is_unsubscribed );

		return true;
	}

	public function only_unsubscribe_from_all_lists_html( $contact = false ) {
		/** In case Pro is active and Contact is valid */
		if ( bwfan_is_autonami_pro_active() ) {
			/** Passed Contact, if it is not valid, get the valid one */
			if ( ! $contact instanceof BWFCRM_Contact ) {
				$contact = isset( $_GET['subscriber_recipient'] ) ? BWFCRM_Common::get_contact_by_email_or_phone( $_GET['subscriber_recipient'] ) : false;
			}

			/** If it is valid now, then show the 'Unsubscribe from All' list view */
			if ( $contact instanceof BWFCRM_Contact ) {
				$is_unsubscribed = BWFCRM_Contact::$DISPLAY_STATUS_UNSUBSCRIBED === $contact->get_display_status();
				$this->unsubscribe_lists_html( array(), array(), $is_unsubscribed );

				return;
			}
		}

		/** If Pro is not active OR Contact is not valid */
		$recipient       = isset( $_GET['subscriber_recipient'] ) ? $_GET['subscriber_recipient'] : '';
		$is_unsubscribed = BWFAN_Model_Message_Unsubscribe::get_message_unsubscribe_row(
			array(
				'recipient' => array( $recipient ),
			),
			true
		);
		$is_unsubscribed = is_array( $is_unsubscribed ) && count( $is_unsubscribed ) > 0;
		$this->unsubscribe_lists_html( array(), array(), $is_unsubscribed );
	}

	public function unsubscribe_lists_html( $lists = array(), $unsubscribed_lists = array(), $is_unsubscribed = false ) {
		$settings    = BWFAN_Common::get_global_settings();
		$label       = isset( $settings['bwfan_unsubscribe_from_all_label'] ) && ! empty( $settings['bwfan_unsubscribe_from_all_label'] ) ? $settings['bwfan_unsubscribe_from_all_label'] : __( '"Unsubscribe From All" Label', 'wp-marketing-automations' );
		$description = isset( $settings['bwfan_unsubscribe_from_all_description'] ) ? $settings['bwfan_unsubscribe_from_all_description'] : '';

		?>
		<style>
			.bwfan-unsubscribe-single-list {
				border-bottom: 1px solid #aaa;
				padding: 20px;
			}

			.bwfan-unsubscribe-single-list:last-child {
				border: none;
				padding: 20px;
			}

			.bwfan-unsubscribe-single-list p {
				margin-top: 3px;
				margin-bottom: 0;
			}

			.bwfan-unsubscribe-single-list label {
				margin-left: 10px;
			}

			p.bwfan-unsubscribe-list-description {
				font-size: 14px;
			}

			.bwfan-unsubscribe-lists {
				margin-bottom: 30px;
			}

			.bwfan-unsubscribe-from-all-lists label {
				font-size: 16px;
				font-weight: 500;
			}
		</style>
		<div class="bwfan-unsubscribe-lists" id="bwfan-unsubscribe-lists">
			<?php
			foreach ( $lists as $list ) {
				$is_checked = ! in_array( absint( $list['ID'] ), $unsubscribed_lists ) && ! $is_unsubscribed;
				?>
				<div class="bwfan-unsubscribe-single-list">
					<div class="bwfan-unsubscribe-list-checkbox">
						<input
							id="bwfan-list-<?php echo $list['ID']; ?>"
							type="checkbox"
							value="<?php echo $list['ID']; ?>"
							<?php echo $is_checked ? 'checked="checked"' : ''; ?>
						/>
						<label for="bwfan-list-<?php echo $list['ID']; ?>"><?php echo $list['name']; ?></label>
					</div>
					<?php if ( isset( $list['description'] ) ) : ?>
						<p class="bwfan-unsubscribe-list-description"><?php echo $list['description']; ?></p>
					<?php endif; ?>
				</div>
				<?php
			}
			?>
			<!-- Global Unsubscription option -->
			<div class="bwfan-unsubscribe-single-list bwfan-unsubscribe-from-all-lists">
				<div class="bwfan-unsubscribe-list-checkbox">
					<input id="bwfan-list-unsubscribe-all" type="checkbox" value="unsubscribe_all" <?php echo $is_unsubscribed ? 'checked="checked"' : ''; ?> />
					<label for="bwfan-list-unsubscribe-all"><?php echo $label; ?></label>
				</div>
				<?php if ( ! empty( $description ) ) : ?>
					<p class="bwfan-unsubscribe-list-description"><?php echo $description; ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	public function bwfan_subscriber_recipient( $attrs ) {
		$atts = shortcode_atts(
			array(
				'fallback' => 'John@example.com',
			),
			$attrs
		);

		if ( isset( $_GET['subscriber_recipient'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			$atts['fallback'] = sanitize_text_field( $_GET['subscriber_recipient'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		return '<span id="bwfan_unsubscribe_recipient">' . $atts['fallback'] . '</span>';
	}

	/**
	 * Adding noindex, nofollow meta tag for unsubscribe page
	 */
	public function unsubscribe_page_non_crawlable() {
		$global_settings     = get_option( 'bwfan_global_settings' );
		$unsubscribe_page_id = isset( $global_settings['bwfan_unsubscribe_page'] ) ? $global_settings['bwfan_unsubscribe_page'] : 0;
		if ( ! empty( $unsubscribe_page_id ) && is_page( $unsubscribe_page_id ) ) {
			echo "\n<meta name='robots' content='noindex,nofollow' />\n";
		}
	}

	public function bwfan_subscriber_name( $attrs ) {
		$atts = shortcode_atts(
			array(
				'fallback' => 'John',
			),
			$attrs
		);

		if ( isset( $_GET['subscriber_name'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			$atts['fallback'] = sanitize_text_field( $_GET['subscriber_name'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		return '<span id="bwfan_unsubscribe_name">' . $atts['fallback'] . '</span>';
	}

	public function create_unsubscribe_sample_page() {
		$global_settings = get_option( 'bwfan_global_settings', array() );
		$content         = "Hi [wfan_contact_name]\n\nHelp us to improve your experience with us through better communication. Please adjust your preferences for email [wfan_contact_email].\n\n[wfan_unsubscribe_button label='Update my preference']";

		$new_page = array(
			'post_title'   => __( 'Let\'s Keep In Touch', 'wp-marketing-automations' ),
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
		);

		$post_id                                   = wp_insert_post( $new_page );
		$global_settings['bwfan_unsubscribe_page'] = $post_id;
		update_option( 'bwfan_global_settings', $global_settings );
	}

	public function bwfan_select_unsubscribe_page() {
		global $wpdb;
		$term    = isset( $_POST['search_term']['term'] ) ? sanitize_text_field( $_POST['search_term']['term'] ) : ''; // WordPress.CSRF.NonceVerification.NoNonceVerification
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_title FROM {$wpdb->prefix}posts WHERE post_title LIKE %s and post_type = %s and post_status =%s", '%' . $term . '%', 'page', 'publish' ) );

		$response = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$response[] = array(
					'id'    => $result->ID,
					'text'  => $result->post_title,
					'value' => $result->ID,
					'label' => $result->post_title,
				);
			}
		}

		wp_send_json(
			array(
				'results' => $response,
			)
		);
	}

	public function bwfan_unsubscribe_user() {
		global $wpdb;
		$nonce = ( isset( $_POST['_nonce'] ) ) ? sanitize_text_field( $_POST['_nonce'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification
		if ( ! wp_verify_nonce( $nonce, 'bwfan-unsubscribe-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['recipient'] ) || ( ! isset( $_POST['automation_id'] ) && ! isset( $_POST['broadcast_id'] ) ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			wp_send_json(
				array(
					'success' => 0,
					'message' => __( 'Security check failed', 'wp-marketing-automations' ),
				)
			);
		}

		$global_settings = BWFAN_Common::get_global_settings();
		$recipient       = sanitize_text_field( $_POST['recipient'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		$automation_id   = absint( sanitize_text_field( $_POST['automation_id'] ) ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		$broadcast_id    = absint( sanitize_text_field( $_POST['broadcast_id'] ) ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		$form_feed_id    = absint( sanitize_text_field( $_POST['form_feed_id'] ) ); // WordPress.CSRF.NonceVerification.NoNonceVerification

		if ( empty( $recipient ) || ( empty( $automation_id ) && empty( $broadcast_id ) && empty( $form_feed_id ) ) ) {
			wp_send_json(
				array(
					'success' => 0,
					'message' => __( 'Unable to unsubscribe. No contact found.', 'wp-marketing-automations' ),
				)
			);
		}

		if ( false !== filter_var( $recipient, FILTER_VALIDATE_EMAIL ) ) {
			$mode = 1;
		} elseif ( is_numeric( $recipient ) ) {
			$mode = 2;
		} else {
			wp_send_json(
				array(
					'success' => 0,
					'message' => __( 'Unable to unsubscribe. No contact found.', 'wp-marketing-automations' ),
				)
			);
		}

		$this->handle_unsubscribe_lists_submission();

		/** @var  $where
		 *  checking if recipient already added to unsubscribe table
		 */
		$where         = "WHERE `recipient` = '" . sanitize_text_field( $recipient ) . "' and `mode` = '" . $mode . "'";
		$unsubscribers = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}bwfan_message_unsubscribe $where ORDER BY ID DESC LIMIT 0,1 " );//phpcs:ignore WordPress.DB.PreparedSQL

		if ( $unsubscribers > 0 ) {
			wp_send_json(
				array(
					'success' => 0,
					'message' => __( 'You have already unsubscribed', 'wp-marketing-automations' ),
				)
			);
		}

		$c_type = 0;
		if ( ! empty( $automation_id ) ) {
			$c_type = 1;
		} elseif ( ! empty( $broadcast_id ) ) {
			$c_type = 2;
		} elseif ( ! empty( $form_feed_id ) ) {
			$c_type = 4;
		} else {
			/** Manual (Single Sending) */
			$c_type = 3;
		}

		$oid = 0;
		if ( ! empty( $automation_id ) ) {
			$oid = absint( $automation_id );
		} elseif ( ! empty( $broadcast_id ) ) {
			$oid = absint( $broadcast_id );
		} elseif ( ! empty( $form_feed_id ) ) {
			$oid = absint( $form_feed_id );
		}

		$insert_data = array(
			'recipient'     => $recipient,
			'c_date'        => current_time( 'mysql' ),
			'mode'          => $mode,
			'automation_id' => $oid,
			'c_type'        => $c_type,
		);

		BWFAN_Model_Message_Unsubscribe::insert( $insert_data );

		wp_send_json(
			array(
				'success' => 1,
				'message' => $global_settings['bwfan_unsubscribe_data_success'],
			)
		);
	}

	public function handle_unsubscribe_lists_submission() {
		/** If invalid lists data */
		if ( ! isset( $_POST['unsubscribe_lists'] ) || empty( $_POST['unsubscribe_lists'] ) ) {
			wp_send_json(
				array(
					'success' => 0,
					'message' => __( 'Invalid Unsubscribe Data', 'wp-marketing-automations' ),
				)
			);
		}

		/** If unsubscribe from all then do normal unsubscribe operation */
		if ( false !== strpos( $_POST['unsubscribe_lists'], 'all' ) ) {
			return;
		}

		$lists = json_decode( $_POST['unsubscribe_lists'], true );
		if ( empty( $lists ) ) {
			$lists = array();
		}

		/** If unsubscribe_lists doesn't contains 'all',
		 * and lists are also empty, then resubscribe only, if pro is not active.
		 * (Because there will be no lists view if pro not active,
		 * and submission means 'User has unchecked the "Unsubscribe from all"')
		 * */
		if ( empty( $lists ) && ! bwfan_is_autonami_pro_active() ) {
			$this->maybe_resubscribe();

			return;
		}

		$contact = BWFCRM_Common::get_contact_by_email_or_phone( sanitize_text_field( $_POST['recipient'] ) );
		if ( ! $contact instanceof BWFCRM_Contact ) {
			wp_send_json(
				array(
					'success' => 0,
					'message' => __( 'Invalid Recipient', 'wp-marketing-automations' ),
				)
			);
		}

		/** If 'all' is missing, then do subscribe the contact */
		if ( BWFCRM_Contact::$DISPLAY_STATUS_UNSUBSCRIBED === $contact->get_display_status() ) {
			$contact->resubscribe();
		}

		$settings      = BWFAN_Common::get_global_settings();
		$public_lists  = $settings['bwfan_unsubscribe_public_lists'];
		$contact_lists = $contact->get_lists();

		$lists = array_map( 'sanitize_text_field', $lists );
		$lists = array_map( 'strval', $lists );

		$lists_to_add   = array_values( array_diff( $public_lists, $contact_lists, $lists ) );
		$lists_to_unsub = array_values( array_intersect( $public_lists, $contact_lists, $lists ) );

		/** Subscribe to lists which are checked, but not assigned to contact */
		if ( ! empty( $lists_to_add ) ) {
			$contact_lists = array_merge( $contact_lists, $lists_to_add );
			$contact->contact->set_lists( $contact_lists );
		}

		/** Unsubscribe from lists which are unchecked, but are assigned to contact */
		$contact->set_field_by_slug( 'unsubscribed-lists', wp_json_encode( $lists_to_unsub ) );
		$contact->save_fields();

		$contact->contact->set_last_modified( current_time( 'mysql', 1 ) );
		if ( method_exists( $contact, 'save' ) ) {
			$contact->save();
		} else {
			$contact->contact->save();
		}

		wp_send_json(
			array(
				'success' => 1,
				'message' => __( 'Your Lists preferences are saved!', 'wp-marketing-automations' ),
			)
		);
	}

	public function maybe_resubscribe() {
		global $wpdb;

		$recipient = sanitize_text_field( $_POST['recipient'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		if ( empty( $recipient ) ) {
			wp_send_json(
				array(
					'success' => 0,
					'message' => __( 'Empty Recipient', 'wp-marketing-automations' ),
				)
			);

			return;
		}

		$mode = 2;
		if ( false !== filter_var( $recipient, FILTER_VALIDATE_EMAIL ) ) {
			$mode = 1;
		} elseif ( is_numeric( $recipient ) ) {
			$mode = 2;
		} else {
			wp_send_json(
				array(
					'success' => 0,
					'message' => __( 'Invalid Recipient', 'wp-marketing-automations' ),
				)
			);
		}

		$where         = "WHERE `recipient` = '" . sanitize_text_field( $recipient ) . "' and `mode` = '" . $mode . "'";
		$unsubscribers = $wpdb->get_results( "SELECT ID,recipient FROM {$wpdb->prefix}bwfan_message_unsubscribe $where ORDER BY ID DESC", ARRAY_A );//phpcs:ignore WordPress.DB.PreparedSQL
		if ( ! empty( $unsubscribers ) ) {
			// $unsubscribers = array_column( $unsubscribers, 'ID' );
			foreach ( $unsubscribers as $unsubscriber ) {
				$id        = $unsubscriber['ID'];
				$recipient = $unsubscriber['recipient'];
				$result    = BWFAN_Model_Message_Unsubscribe::delete( $id );
				do_action( 'bwfan_unsubscribers_deleted', $result, $recipient );
			}

			wp_send_json(
				array(
					'success' => 1,
					'message' => __( 'You are now re-subscribed', 'wp-marketing-automations' ),
				)
			);
		}

		wp_send_json(
			array(
				'success' => 0,
				'message' => __( 'You are already subscribed', 'wp-marketing-automations' ),
			)
		);
	}


}

BWFAN_unsubscribe::get_instance();
