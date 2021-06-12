<?php

class BWFAN_API_Update_User_Prefernece extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public $contact;

	public function __construct() {
		parent::__construct();
		$this->method       = WP_REST_Server::EDITABLE;
		$this->route        = '/user-preference/(?P<user_id>[\\d]+)';
	}

	public function default_args_values() {
		return array(
			'user_id' => 0,
			'data' => []
		);
	}

	public function process_api_call() {
		/** checking if id present in params **/
		$user_id = $this->get_sanitized_arg( 'user_id', 'key' );
		if($user_id){
			$user_exists = (bool) get_users( array(
				'include' => $user_id,
				'fields'  => 'ID',
			) );
			if ( ! $user_exists ) {
				$this->response_code = 404;
				return $this->error_response( __( "Contact doesn't exists with the id : ", 'wp-marketing-automations-crm' ) . $user_id);
			}

			if ( ! empty( $this->args['data'] ) && is_array( $this->args['data'] ) ) {
				$data = $this->args['data'];
			}

			if(isset($data['contact_column'])){
				update_user_meta($user_id, '_bwfan_contact_columns', $data['contact_column']);
			}
			if(isset($data['campaign_column'])){
				update_user_meta($user_id, '_bwfan_broadcast_columns', $data['campaign_column']);
			}
			if(isset($data['welcome_note_dismiss'])){
				update_user_meta($user_id, '_bwfan_welcome_note_dismissed', $data['welcome_note_dismiss']);
			}
			return $this->success_response(true);
		}else{
			$this->response_code = 404;
			return $this->error_response( __( "Please provide the user for the data.", 'wp-marketing-automations-crm' ));
		}
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Update_User_Prefernece' );
