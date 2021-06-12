<?php

abstract class BWFAN_Action {

	/**
	 * Connector slug for current action. Tells to which connector current action relates to.
	 * @var null
	 */
	public $connector = null;
	public $is_action_tag = false;
	public $automation_id = false;
	protected $localize_data = [];
	protected $action_priority = 0;
	/**
	 * For actions which support wysiwyg editor
	 * @var bool
	 */
	/**
	 * Restrict events on which current action will not shown
	 * @var array
	 */
	protected $excluded_events = array();
	protected $included_events = array();
	/**
	 * Stores current action's data
	 * @var array
	 */
	protected $data = [];
	protected $allowed_responses = [ 200, 201, 202 ];
	/**
	 * Tells to which integration this action belongs to.
	 * @var null
	 */
	protected $fields_missing = false;
	/** @var string Action name */
	protected $action_name = '';
	/** @var string Action description */
	protected $action_desc = '';
	/**
	 * Required fields to run the action.
	 * @var array
	 */
	protected $required_fields = [];
	protected $is_editor_supported = false;
	protected $missing_field = array();
	protected $integration_type = 'wp';
	public $support_language = false;

	public function get_random_api_error() {
		return __( 'Api Error: No response from API', 'wp-marketing-automations' );
	}

	public function register_action() {

	}

	public function get_action_priority() {
		return $this->action_priority;
	}

	public function load_hooks() {
		//
	}

	public function get_view() {
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_js( $this->get_slug() ); ?>">
		</script>
		<?php
	}

	public function get_slug() {
		return str_replace( array( 'bwfan_' ), '', sanitize_title( get_class( $this ) ) );
	}

	public function add_description( $desc, $size = 'm', $position = 'top', $esc = true ) {
		if ( empty( $desc ) ) {
			return '';
		}

		ob_start();
		?>
        <div class="bwfan_tooltip" data-size="<?php echo esc_attr( $size ); ?>">
            <span class="bwfan_tooltip_text" data-position="<?php echo esc_attr( $position ); ?>"><?php echo ( true === $esc ) ? esc_js( $desc ) : $desc; ?></span>
        </div>
		<?php
		$return = ob_get_clean();

		return $return;
	}

	public function inline_template_selector_invoke() {
		ob_start();
		?>
        <a href="javascript:void(0)" class="bwfan_inline_merge_tag" data-izimodal-open="#modal-autonami-template-selector" data-izimodal-title="My Templates" data-izimodal-transitionin="comingIn">
            <?php esc_html_e( 'My Templates', 'wp-marketing-automations' ); ?>
        </a>
		<?php
		return ob_get_clean();
	}

	public function inline_merge_tag_invoke() {
		ob_start();
		?>
        <a href="javascript:void(0)" class="bwfan_inline_merge_tag" data-izimodal-open="#modal-show-merge-tags" data-izimodal-title="Use Merge Tags" data-izimodal-transitionin="comingIn">
            <?php esc_html_e( 'Merge tags', 'wp-marketing-automations' ); ?>
        </a>
		<?php
		return ob_get_clean();
	}

	/**
	 * Reset the data of an action
	 */
	public function reset_data() {
		$this->data = [];
	}

	/**
	 * Saves the request data and response data for every action into the DB
	 *
	 * @param $data
	 * @param $response
	 */
	public function save_data( $data, $response ) {

	}

	/**
	 * Checks the required fields for every action
	 *
	 * @param $data
	 * @param $required_fields
	 *
	 * @return bool
	 */
	public function check_fields( $data, $required_fields ) {
		$bool = true;
		foreach ( $required_fields as $single_field ) {
			if ( false === isset( $data[ $single_field ] ) ) {
				$this->missing_field = $single_field;
				$bool                = false;
				break;
			}
		}

		return $bool;
	}

	/**
	 * Return the error
	 *
	 * @return array
	 */
	public function show_fields_error() {
		$message = __( 'Required Field Missing', 'woofunnels' );
		$message .= ': ' . $this->missing_field;

		return array(
			'bwfan_response' => $message,
		);
	}

	/**
	 * Sends a wp remote call
	 *
	 * @param $url
	 * @param array $params
	 * @param int $req_method
	 *
	 * @return array|mixed|object|string
	 */
	public function make_wp_requests( $url, $params = array(), $headers = array(), $req_method = 1 ) {
		$body = array(
			'response' => 500,
			'body'     => __( 'Curl Error', 'wp-marketing-automations' ),
		);

		// $req_method
		// 1 stands for get
		// 2 stands for post
		// 3 stands for delete

		$args = array(
			'timeout'     => 45,
			'httpversion' => '1.0',
			'blocking'    => true,
			'body'        => $params,
		);

		if ( is_array( $headers ) && count( $headers ) > 0 ) {
			$args['headers'] = $headers;
		}

		switch ( $req_method ) {
			case 2:
				$args['method'] = 'POST';
				break;
			case 3:
				$args['method'] = 'DELETE';
				break;
			case 4:
				$args['method'] = 'PUT';
				break;
			case 5:
				$args['method'] = 'PATCH';
				break;
			default:
				$args['method'] = 'GET';
				break;
		}

		$response = wp_remote_request( $url, $args );

		if ( ! is_wp_error( $response ) ) {
			$body    = wp_remote_retrieve_body( $response );
			$headers = wp_remote_retrieve_headers( $response );
			if ( $this->is_json( $body ) ) {
				$body = json_decode( $body, true );
			}
			$body = maybe_unserialize( $body );
			if ( in_array( $response['response']['code'], $this->allowed_responses, true ) ) {
				$response_code = 200;
			} else {
				$response_code = $response['response']['code'];
			}

			$body = array(
				'response' => intval( $response_code ),
				'body'     => $body,
				'headers'  => $headers,
			);

			return $body;
		}

		$body['body'] = [ $response->get_error_message() ];

		return $body;
	}

	/**
	 * check if a string is json or not
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	public function is_json( $string ) {
		json_decode( $string );

		return ( json_last_error() == JSON_ERROR_NONE );//phpcs:ignore WordPress.PHP.StrictComparisons
	}

	public function parse_unsubscribe_link() {
		if ( false === $this->automation_id ) {
			return;
		}

		add_filter( 'bwfan_unsubscribe_link', [ $this, 'bwfan_unsubscribe_link_add_aid' ] );
	}

	public function bwfan_unsubscribe_link_add_aid( $link ) {
		$link = add_query_arg( array(
			'automation_id' => $this->automation_id,
		), $link );

		return $link;
	}

	/**
	 * @param $integration_object BWFAN_Integration
	 * @param $task_meta
	 *
	 * @return array
	 */
	public function make_data( $integration_object, $task_meta ) {

		return $task_meta;
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

		$result = [
			'status'  => 0,
			'message' => __( 'Default Resource Message', 'wp-marketing-automations' ),
		];

		$integration = BWFAN_Core()->integration->get_integration( $action_data['integration_slug'] );

		if ( is_null( $integration ) ) {
			return $result;
		}

		$this->set_data( $action_data['processed_data'] );

		if ( $integration->need_connector() ) {
			$load_connector = WFCO_Load_Connectors::get_instance();

			$call_class = $load_connector->get_call( $this->call );
			if ( is_null( $call_class ) ) {
				return $result;
			}
			$call_class->set_data( $action_data['processed_data'] );
			$result = $call_class->process();
			$result = $integration->handle_response( $result, $this->connector, $this->call );
			$result = $this->handle_response( $result, $call_class );

		} else {
			$result = $this->process();
		}

		return $result;
	}

	/**
	 * Set the data for every action
	 *
	 * @param $data
	 */
	public function set_data( $data ) {
		$this->data = $data;
	}

	/**
	 * @param $response
	 * @param $call_object WFCO_Call
	 *
	 * @return mixed
	 */
	protected function handle_response( $response, $call_object = null ) {
		return $response;
	}

	public function process() {
		return '';
	}

	/**
	 * Get the merge tags which are array types and convert their values as comma separated string
	 *
	 * @param $dynamic_array
	 * @param $integration_data
	 *
	 * @return array
	 */
	public function parse_merge_tags( $dynamic_array, $integration_data ) {
		$result = array();
		foreach ( $dynamic_array as $key1 => $value ) {
			if ( is_array( $value ) && count( $value ) > 0 ) {
				$dynamic_array[ $key1 ] = implode( ', ', $value );
			}
		}

		$result['parsed_merge_tags'] = $dynamic_array;
		$result['data']              = $integration_data;

		return $result;
	}

	public function parse_tags_fields( $dynamic_array, $integration_data ) {
		$result         = array();
		$new_merge_tags = array();
		foreach ( $dynamic_array as $key1 => $value ) {
			$key = array_search( '{{' . $key1 . '}}', $integration_data, true );

			if ( is_array( $integration_data ) && count( $integration_data ) > 0 ) {//phpcs:ignore WordPress.PHP.StrictComparisons
				foreach ( $integration_data as $possible_merge_tag ) {
					if ( is_array( $possible_merge_tag ) && count( $possible_merge_tag ) > 0 ) {
						foreach ( $possible_merge_tag as $p_m_t ) {
							if ( strpos( $p_m_t, '{{' . $key1 . '}}' ) !== false ) {
								if ( is_array( $value ) && count( $value ) > 0 ) {
									unset( $integration_data[ $key ] );
									foreach ( $value as $key2 => $value2 ) {
										$integration_data[]               = '{{tag_' . $key2 . '}}';
										$new_merge_tags[ 'tag_' . $key2 ] = str_replace( '{{' . $key1 . '}}', $value2, $p_m_t );
									}
								} elseif ( '' !== $value ) {
									$integration_data[]      = '{{' . $key1 . '}}';
									$new_merge_tags[ $key1 ] = str_replace( '{{' . $key1 . '}}', $value, $p_m_t );
								}
							}
						}
					} else {
						if ( strpos( $possible_merge_tag, '{{' . $key1 . '}}' ) !== false ) {
							if ( is_array( $value ) && count( $value ) > 0 ) {
								unset( $integration_data[ $key ] );
								foreach ( $value as $key2 => $value2 ) {
									$integration_data[]               = '{{tag_' . $key2 . '}}';
									$new_merge_tags[ 'tag_' . $key2 ] = str_replace( '{{' . $key1 . '}}', $value2, $possible_merge_tag );
								}
							} elseif ( '' !== $value ) {
								$integration_data[]      = '{{' . $key1 . '}}';
								$new_merge_tags[ $key1 ] = str_replace( '{{' . $key1 . '}}', $value, $possible_merge_tag );
							}
						}
					}
				}
			}
		}

		$integration_data = array_unique( $integration_data );

		$result['parsed_merge_tags'] = $new_merge_tags;
		$result['data']              = $integration_data;

		return $result;
	}

	public function set_data_for_merge_tags( $task_meta ) {
		$event_slug = $task_meta['event_data']['event_slug'];
		BWFAN_Merge_Tag_Loader::set_data( array(
			'automation_id' => $task_meta['automation_id'],
		) );
		$single_event = BWFAN_Core()->sources->get_event( $event_slug );
		if ( $single_event instanceof BWFAN_Event ) {
			$single_event->set_merge_tags_data( $task_meta ); // This function is written in every event class
		}
	}

	public function get_class_slug() {
		return str_replace( 'bwfan_', '', strtolower( get_class( $this ) ) );
	}

	public function before_executing_task() {

	}

	public function after_executing_task() {

	}

	public function get_integration_type() {
		return $this->integration_type;
	}

	public function set_integration_type( $type ) {
		$this->integration_type = $type;
	}

	public function get_included_events() {
		return $this->included_events;
	}

	public function get_excluded_events() {
		return $this->excluded_events;
	}

	/**
	 * Return localize data of event for frontend UI
	 * @return array
	 */
	public function get_localize_data() {
		$this->localize_data = [
			'included_events'  => $this->included_events,
			'excluded_events'  => $this->excluded_events,
			'action_name'      => $this->action_name,
			'action_desc'      => $this->action_desc,
			'slug'             => $this->get_slug(),
			'required'         => $this->required_fields,
			'support_language' => $this->support_language,
		];

		return apply_filters( 'bwfan_action_' . $this->get_slug() . '_localize_data', $this->localize_data, $this );
	}

	public function is_editor_supported() {
		return $this->is_editor_supported;
	}

	public function get_data() {
		return $this->data;
	}

	public function get_name() {
		return $this->action_name;
	}

	public function check_required_data( $data ) {
		return true;
	}

	public function __get( $key ) {
		if ( 'call' === $key ) {
			return 'wfco_' . $this->get_slug();
		}
	}

	/**
	 * to avoid unserialize of the current class
	 */
	public function __wakeup() {
		throw new ErrorException( 'BWFAN_Core can`t converted to string' );
	}

	/**
	 * to avoid serialize of the current class
	 */
	public function __sleep() {
		throw new ErrorException( 'BWFAN_Core can`t converted to string' );
	}

	/**
	 * To avoid cloning of current class
	 */
	protected function __clone() {
	}

}
