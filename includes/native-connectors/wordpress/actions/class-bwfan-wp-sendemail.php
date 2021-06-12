<?php

final class BWFAN_Wp_Sendemail extends BWFAN_Action {

	private static $ins = null;
	public $is_preview = false;
	public $preview_body = '';

	public $support_language = true;

	protected function __construct() {
		$this->action_name     = __( 'Send Email', 'wp-marketing-automations' );
		$this->action_desc     = __( 'This action sends an email to a user', 'autonami-automations-connectors' );
		$this->required_fields = array( 'subject', 'body', 'email', 'from_email', 'from_name' );

		add_filter( 'admin_body_class', array( $this, 'add_email_preview_class' ) );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	/**
	 * Localize data for html fields for the current action.
	 */
	public function admin_enqueue_assets() {
		wp_enqueue_media();
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$data = [];

			$data['raw_template'] = __( 'Rich Text', 'wp-marketing-automations' );
			if ( bwfan_is_woocommerce_active() ) {
				$data['wc_template'] = __( 'WooCommerce', 'wp-marketing-automations' );
			}
			$data['raw'] = __( 'Raw HTML', 'wp-marketing-automations' );
			if ( bwfan_is_autonami_pro_active() ) {
				$data['editor'] = __( 'Drag & Drop', 'wp-marketing-automations' );
			}

			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'template_options', $data );
		}
	}

	public function add_unsubscribe_merge_tag( $text ) {
		if ( isset( $this->data['promotional_email'] ) && 0 === absint( $this->data['promotional_email'] ) ) {
			return $text;
		}

		// add separator if there is footer text
		if ( trim( $text ) ) {
			$text .= apply_filters( 'bwfan_woo_email_footer_separator', ' - ' );
		}

		$global_settings  = BWFAN_Common::get_global_settings();
		$unsubscribe_link = BWFAN_Common::decode_merge_tags( '{{unsubscribe_link}}' );
		$text             .= '<a href="' . $unsubscribe_link . '">' . $global_settings['bwfan_unsubscribe_email_label'] . '</a>';

		return $text;
	}

	public function add_unsubscribe_query_args( $link ) {
		if ( empty( $this->data ) ) {
			return $link;
		}
		if ( isset( $this->data['email'] ) ) {
			$link = add_query_arg( array(
				'subscriber_recipient' => $this->data['email'],
			), $link );
		}
		if ( isset( $this->data['name'] ) ) {
			$link = add_query_arg( array(
				'subscriber_name' => $this->data['name'],
			), $link );
		}

		return $link;
	}

	public function skip_name_email( $flag ) {
		return true;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		include_once BWFAN_PLUGIN_DIR . '/includes/native-connectors/wordpress/views/bwfan-wp-send-email.php';
	}

	/**
	 * Make all the data which is required by the current action.
	 * This data will be used while executing the task of this action.
	 *
	 * @param $integration_object
	 * @param $task_meta
	 *
	 * @return array|void
	 */
	public function make_data( $integration_object, $task_meta ) {
		$user_id = isset( $task_meta['global']['user_id'] ) && ! empty( $task_meta['global']['user_id'] ) ? absint( $task_meta['global']['user_id'] ) : 0;
		$user_id = empty( $user_id ) && isset( $task_meta['data']['user_id'] ) && ! empty( $task_meta['data']['user_id'] ) ? absint( $task_meta['data']['user_id'] ) : 0;

		$global_email_settings = BWFAN_Common::get_global_email_settings();
		$data_to_set           = array(
			'subject'           => BWFAN_Common::decode_merge_tags( $task_meta['data']['subject'] ),
			'email'             => BWFAN_Common::decode_merge_tags( $task_meta['data']['to'] ),
			'name'              => BWFAN_Common::decode_merge_tags( '{{customer_first_name}}' ),
			'email_heading'     => BWFAN_Common::decode_merge_tags( $task_meta['data']['email_heading'] ),
			'preheading'        => empty( $task_meta['data']['preheading'] ) ? '' : BWFAN_Common::decode_merge_tags( $task_meta['data']['preheading'] ),
			'template'          => $task_meta['data']['template'],
			'promotional_email' => ( isset( $task_meta['data']['promotional_email'] ) ) ? 1 : 0,
			'append_utm'        => ( isset( $task_meta['data']['append_utm'] ) ) ? 1 : 0,
			'utm_source'        => ( isset( $task_meta['data']['utm_source'] ) ) ? BWFAN_Common::decode_merge_tags( $task_meta['data']['utm_source'] ) : '',
			'utm_medium'        => ( isset( $task_meta['data']['utm_medium'] ) ) ? BWFAN_Common::decode_merge_tags( $task_meta['data']['utm_medium'] ) : '',
			'utm_campaign'      => ( isset( $task_meta['data']['utm_campaign'] ) ) ? BWFAN_Common::decode_merge_tags( $task_meta['data']['utm_campaign'] ) : '',
			'utm_term'          => ( isset( $task_meta['data']['utm_term'] ) ) ? BWFAN_Common::decode_merge_tags( $task_meta['data']['utm_term'] ) : '',
			'event'             => $task_meta['event_data']['event_slug'],
			'body'              => $this->get_email_body( $task_meta ),
			'from_email'        => $global_email_settings['bwfan_email_from'],
			'from_name'         => $global_email_settings['bwfan_email_from_name'],
			'reply_to_email'    => $global_email_settings['bwfan_email_reply_to'],
			'user_id'           => empty( $user_id ) ? null : $user_id
		);

		$data_to_set['body'] = stripslashes( $data_to_set['body'] );
		if ( true === $this->is_preview ) {
			$this->preview_body  = $data_to_set['body'];
			$data_to_set['body'] = BWFAN_Common::decode_merge_tags( $data_to_set['body'] );
			$data_to_set['body'] = apply_filters( 'bwfan_before_send_email_body', $data_to_set['body'], $data_to_set );
			$data_to_set['body'] = $this->email_content( $data_to_set );
			$data_to_set['body'] = BWFAN_Common::bwfan_correct_protocol_url( $data_to_set['body'] );
		}

		return apply_filters( 'bwfan_sendemail_make_data', $data_to_set, $task_meta );
	}

	public function get_email_body( $task_meta ) {
		switch ( $task_meta['data']['template'] ) {
			case 'raw':
				return $task_meta['data']['body_raw'];
			case 'editor':
				return $task_meta['data']['editor']['body'];
			default:
				return $task_meta['data']['body'];
		}
	}

	public function email_content( $data ) {
		$body = isset( $data['body'] ) ? $data['body'] : '';
		if ( method_exists( $this, 'email_body_' . $data['template'] ) ) {
			$body = call_user_func( [ $this, 'email_body_' . $data['template'] ], $data );
		}

		return $body;
	}

	/**
	 * Execute the current action.
	 * Return 3 for successful execution , 4 for permanent failure.
	 *
	 * @param $action_data
	 *
	 * @return array
	 */
	public function execute_action( $action_data ) {
		global $wpdb;
		$this->set_data( $action_data['processed_data'] );
		$this->data['task_id'] = $action_data['task_id'];
		$sql_query             = 'Select meta_value FROM {table_name} WHERE bwfan_task_id = %d AND meta_key = %s';
		$sql_query             = $wpdb->prepare( $sql_query, $this->data['task_id'], 't_track_id' ); // WPCS: unprepared SQL OK
		$gids                  = BWFAN_Model_Taskmeta::get_results( $sql_query );
		$this->data['gid']     = '';

		if ( ! empty( $gids ) && is_array( $gids ) ) {
			foreach ( $gids as $gid ) {
				$this->data['gid'] = $gid['meta_value'];

			}
		}

		if ( 1 === absint( $this->data['promotional_email'] ) && ( false === apply_filters( 'bwfan_force_promotional_email', false, $this->data ) ) ) {
			$to     = trim( stripslashes( $this->data['email'] ) );
			$emails = explode( ',', $to );

			$emails = array_map( function ( $email ) {
				return trim( $email );
			}, $emails );

			$where             = array(
				'recipient' => $emails,
				'mode'      => 1,
			);
			$check_unsubscribe = BWFAN_Model_Message_Unsubscribe::get_message_unsubscribe_row( $where, false );

			if ( ! empty( $check_unsubscribe ) && is_array( $check_unsubscribe ) ) {
				$check_unsubscribe = array_map( function ( $unsubscribe_row ) {
					return $unsubscribe_row['recipient'];
				}, $check_unsubscribe );

				$unsubscribed_emails = implode( ', ', array_unique( $check_unsubscribe ) );

				return array(
					'status'  => 4,
					'message' => __( 'User(s) are already unsubscribed, with email(s): ' . $unsubscribed_emails, 'wp-marketing-automations' ),
				);
			}
		}

		$result = $this->process();
		if ( true === $result ) {
			return array(
				'status' => 3,
			);
		}

		if ( bwfan_is_autonami_pro_active() && BWFCRM_Core()->campaigns->maybe_daily_limit_reached() ) {
			return array(
				'status'  => 0,
				'message' => __( 'Daily Email Limit reached. Will retry after sometime' )
			);
		}

		if ( is_array( $result ) && isset( $result['message'] ) ) {
			return array(
				'status'  => 4,
				'message' => $result['message'],
			);
		}

		return array(
			'status'  => 4,
			'message' => __( 'Unknown Error occurred during Send Email', 'wp-marketing-automations' ),
		);
	}

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		return $this->send_email();
	}

	/**
	 * Send an Email.
	 *
	 * subject, body , email are required.
	 *
	 * @return array|bool
	 */
	public function send_email() {
		$to        = trim( stripslashes( $this->data['email'] ) );
		$subject   = stripslashes( $this->data['subject'] );
		$headers   = [];
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'From: ' . $this->data['from_name'] . ' <' . $this->data['from_email'] . '>';
		$headers[] = 'Content-type:text/html;charset=UTF-8';
		if ( isset( $this->data['reply_to_email'] ) && ! empty( $this->data['reply_to_email'] ) ) {
			$headers[] = 'Reply-To:  ' . $this->data['reply_to_email'];
		}

		if ( empty( $subject ) ) {
			return array(
				'message' => __( 'Email subject missing. Please provide subject to send email.', 'wp-marketing-automations' ),
			);
		}
		if ( empty( $to ) ) {
			return array(
				'message' => __( 'Recipient email missing. Please provide email to send email.', 'wp-marketing-automations' ),
			);
		}

		/** Send Email */
		$global_settings = BWFAN_Common::get_global_settings();
		$emails          = explode( ',', $to );
		$emails          = array_map( function ( $email ) {
			return trim( $email );
		}, $emails );

		if ( true === $this->is_preview ) {
			$this->data['body'] = $this->preview_body;
		}

		$body = $this->data['body'];

		/** Set content type to prevent conflict with other plugins who are using 'wp_mail_content_type' filter */
		add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ), 999 );

		/**
		 * @todo optimize send email code
		 */
		$conversations = [];
		if ( ! isset( $global_settings['bwfan_email_service'] ) || 'wp' === $global_settings['bwfan_email_service'] ) {
			foreach ( $emails as $email ) {
				$this->data['email'] = $email;

				/** Modify email body for engagement tracking */
				if ( bwfan_is_autonami_pro_active() ) {
					$this->data['body'] = BWFAN_Core()->conversations->bwfan_modify_email_body_data( $this->data['body'], $this->data );
				} else {
					$this->data['body'] = BWFAN_Common::decode_merge_tags( $this->data['body'] );
				}

//				$this->data['body']  = BWFAN_Common::decode_merge_tags( $this->data['body'] );
				$this->data['body'] = apply_filters( 'bwfan_before_send_email_body', $this->data['body'], $this->data );
				$this->data['body'] = $this->email_content( $this->data );
				$this->data['body'] = BWFAN_Common::bwfan_correct_protocol_url( $this->data['body'] );
				$this->data['body'] = $this->append_to_email_body( $this->data['body'], $this->data['preheading'] );
				$res                = wp_mail( $email, $subject, $this->data['body'], $headers );
				$this->data['body'] = $body; // Set the original body to use correct body in email.
				/** updating conversation only when the bwfan autonami pro is activated */
				if ( function_exists( 'bwfan_is_autonami_pro_active' ) && bwfan_is_autonami_pro_active() ) {
					$conversations[ $email ]['res']               = $res;
					$conversations[ $email ]['conversation_id']   = isset( $this->data['conversation_id'] ) ? $this->data['conversation_id'] : '';
					$conversations[ $email ]['hash_code']         = isset( $this->data['hash_code'] ) ? $this->data['hash_code'] : '';
					$conversations[ $email ]['subject_merge_tag'] = isset( $this->data['subject_merge_tag'] ) ? $this->data['subject_merge_tag'] : '';
				}
			}
		} else {
			// Every connector which registers itself for email service must have send_email() in its integration class.
			foreach ( $emails as $email ) {
				$this->data['email'] = $email;
				/** Modify email body for engagement tracking */
				if ( bwfan_is_autonami_pro_active() ) {
					$this->data['body'] = BWFAN_Core()->conversations->bwfan_modify_email_body_data( $this->data['body'], $this->data );
				} else {
					$this->data['body'] = BWFAN_Common::decode_merge_tags( $this->data['body'] );
				}

//				$this->data['body']     = BWFAN_Common::decode_merge_tags( $this->data['body'] );
				$this->data['body']     = apply_filters( 'bwfan_before_send_email_body', $this->data['body'], $this->data );
				$this->data['body']     = $this->email_content( $this->data );
				$this->data['body']     = BWFAN_Common::bwfan_correct_protocol_url( $this->data['body'] );
				$autonami_integrations  = BWFAN_Core()->integration->get_integrations();
				$selected_email_service = $global_settings['bwfan_email_service'];
				$res                    = isset( $autonami_integrations[ $selected_email_service ] ) ? $autonami_integrations[ $selected_email_service ]->send_email( $email, $subject, $this->data['body'], $headers ) : wp_mail( $email, $subject, $this->data['body'], $headers );
				$this->data['body']     = $body; // Set the original body to use correct body in email.
				$this->data['body']     = $this->append_to_email_body( $this->data['body'], $this->data['preheading'] );
				/** updating conversation only when the bwfan autonami pro is activated */
				if ( function_exists( 'bwfan_is_autonami_pro_active' ) && bwfan_is_autonami_pro_active() ) {
					$conversations[ $email ]['res']               = $res;
					$conversations[ $email ]['conversation_id']   = isset( $this->data['conversation_id'] ) ? $this->data['conversation_id'] : '';
					$conversations[ $email ]['hash_code']         = isset( $this->data['hash_code'] ) ? $this->data['hash_code'] : '';
					$conversations[ $email ]['subject_merge_tag'] = isset( $this->data['subject_merge_tag'] ) ? $this->data['subject_merge_tag'] : '';
				}
			}
		}

		remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ), 999 );

		$return = true;
		if ( ! $res ) {
			$return = $this->maybe_get_failed_mail_error();
		}

		if ( ! isset( $data['test'] ) ) {
			do_action( 'bwfan_conversation_sendemail_action', $this, $body, $conversations );
		}

		return $return;
	}

	public function maybe_get_failed_mail_error() {
		global $phpmailer;

		if ( ! class_exists( '\WPMailSMTP\MailCatcher' ) ) {
			return false;
		}

		if ( ! ( $phpmailer instanceof \WPMailSMTP\MailCatcher ) ) {
			return false;
		}

		$debug_log = get_option( 'wp_mail_smtp_debug', false );
		if ( empty( $debug_log ) || ! is_array( $debug_log ) ) {
			return false;
		}

		return array( 'message' => $debug_log[0] );
	}

	public function set_email_content_type( $content_type ) {
		return 'text/html';
	}

	public function before_executing_task() {
		add_filter( 'bwfan_change_tasks_retry_limit', [ $this, 'modify_retry_limit' ], 99 );
		add_filter( 'woocommerce_email_footer_text', array( $this, 'add_unsubscribe_merge_tag' ) );
		add_filter( 'bwfan_unsubscribe_link', array( $this, 'add_unsubscribe_query_args' ) );
		add_filter( 'bwfan_skip_name_email_from_unsubscribe_link', array( $this, 'skip_name_email' ) );
	}

	public function after_executing_task() {
		remove_filter( 'bwfan_change_tasks_retry_limit', [ $this, 'modify_retry_limit' ], 99 );
		remove_filter( 'woocommerce_email_footer_text', array( $this, 'add_unsubscribe_merge_tag' ) );
		remove_filter( 'bwfan_unsubscribe_link', array( $this, 'add_unsubscribe_query_args' ) );
		remove_filter( 'bwfan_skip_name_email_from_unsubscribe_link', array( $this, 'skip_name_email' ) );
	}

	public function modify_retry_limit( $retry_data ) {
		$retry_data[] = DAY_IN_SECONDS;

		return $retry_data;
	}

	public function add_email_preview_class( $classes ) {
		if ( isset( $_GET['section'] ) && 'preview_email' === $_GET['section'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$classes .= ' bwfan_preview_email';
		}

		return $classes;
	}

	/**
	 * Outputs WC template email body
	 *
	 * @param $data
	 *
	 * @return string
	 */
	protected function email_body_wc_template( $data ) {
		$email_body    = $data['body'];
		$email_heading = $data['email_heading'];
		$mailer        = WC()->mailer();

		// If promotional checkbox is not checked, then remove {{unsubscribe_link}} merge tag
		if ( isset( $data['promotional_email'] ) && 0 === absint( $data['promotional_email'] ) ) {
			remove_filter( 'woocommerce_email_footer_text', array( $this, 'add_unsubscribe_merge_tag' ) );
		}
		$email_abstract_object = new WC_Email();
		ob_start();

		do_action( 'woocommerce_email_header', $email_heading, $email_abstract_object );

		echo $email_body; //phpcs:ignore WordPress.Security.EscapeOutput

		do_action( 'woocommerce_email_footer', $email_abstract_object );

		$email_body = ob_get_clean();


		return apply_filters( 'woocommerce_mail_content', $email_abstract_object->style_inline( wptexturize( $email_body ) ) );
	}

	/**
	 * Outputs Custom template email body
	 *
	 * @param $data
	 *
	 * @return string
	 */
	protected function email_body_raw_template( $data ) {
		$email_body = $this->prepare_email_content( $data['body'] );

		ob_start();
		include BWFAN_PLUGIN_DIR . '/templates/email-styles.php';
		$css = ob_get_clean();

		if ( BWFAN_Common::supports_emogrifier() ) {
			$emogrifier_class = '\\Pelago\\Emogrifier';
			if ( ! class_exists( $emogrifier_class ) ) {
				include_once BWFAN_PLUGIN_DIR . '/libraries/class-emogrifier.php';
			}
			try {
				/** @var \Pelago\Emogrifier $emogrifier */
				$emogrifier = new $emogrifier_class( $email_body, $css );
				$email_body = $emogrifier->emogrify();
			} catch ( Exception $e ) {
				BWFAN_Core()->logger->log( $e->getMessage(), 'send_email_emogrifier' );
			}
		} else {
			$email_body = '<style type="text/css">' . $css . '</style>' . $email_body;
		}

		return $email_body;
	}

	/**
	 * Outputs RAW HTML/CSS template email body
	 *
	 * @param $data
	 *
	 * @return string
	 */
	protected function email_body_raw( $data ) {
		return $data['body'];
	}

	/**
	 * @param $content
	 *
	 * @return string|null
	 */
	private function prepare_email_content( $content ) {
		$has_body      = stripos( $content, '<body' ) !== false;
		$preview_class = $this->is_preview ? 'bwfan_email_preview' : '';

		/** Check if body tag exists */
		if ( ! $has_body ) {
			return '<html><head></head><body><div id="body_content" class="' . $preview_class . '">' . $content . '</div></body></html>';
		}

		$pattern     = "/<body(.*?)>(.*?)<\/body>/is";
		$replacement = '<body$1><div id="body_content" class="' . $preview_class . '">$2</div></body>';

		return preg_replace( $pattern, $replacement, $content );
	}

	/** append pre header in email body
	 *
	 * @param $body
	 * @param $pre_header
	 *
	 * @return string|string[]|null
	 */
	public function append_to_email_body( $body, $pre_header ) {

		if ( empty( $pre_header ) ) {
			return $body;
		}

		$pre_header = '<span class="preheader" style="display: none; mso-hide: all; width: 0px; height: 0px; max-width: 0px; max-height: 0px; font-size: 0px; line-height: 0px;">' . BWFAN_Common::decode_merge_tags( $pre_header ) . '</span>';
		$appended_body = $body;

		if ( strpos( $body, '</body>' ) ) {
			$pattern       = '/<body(.*?)>(.*?)<\/body>/is';
			$replacement   = '<body$1>' . $pre_header . '$2</body>';
			$appended_body = preg_replace( $pattern, $replacement, $body );
		} else {
			$appended_body = $pre_header . ' ' . $body;
		}

		return $appended_body;
	}
}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_Wp_Sendemail';
