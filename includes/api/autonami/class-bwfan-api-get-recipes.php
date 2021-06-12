<?php

class BWFAN_API_Get_Recipes extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public $total_count = 0;

	public function __construct() {
		parent::__construct();
		$this->method = WP_REST_Server::READABLE;
		$this->route  = '/recipes';
	}

	public function default_args_values() {
		$args = [];

		return $args;
	}

	public function process_api_call() {

		$all_recipes         = BWFAN_Recipe_Loader::get_recipes_array();
		$this->response_code = 200;
		$this->total_count   = is_array( $all_recipes ) ? count( $all_recipes ) : 0;

		return $this->success_response( $all_recipes, __( 'Got all recipes.', 'wp-marketing-automations' ) );
	}

	public function get_result_total_count() {
		return $this->total_count;
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Get_Recipes' );