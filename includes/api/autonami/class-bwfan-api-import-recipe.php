<?php

class BWFAN_API_Import_Recipe extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function __construct() {
		parent::__construct();
		$this->method = WP_REST_Server::CREATABLE;
		$this->route  = '/recipe/import';
	}

	public function default_args_values() {
		$args = [
			'recipe_slug' => '',
			'title'      => '',
		];

		return $args;
	}

	public function process_api_call() {
		$recipe_slug = $this->args['recipe_slug'];
		$title       = $this->args['title'];

		if ( empty( $recipe_slug ) ) {
			return $this->error_response( __( 'Recipe is missing', 'wp-marketing-automations' ), null, 404 );
		}

		$automation = BWFAN_Recipes::create_automation_by_recipe($recipe_slug, $title);

		if($automation['status']){
			$this->response_code = 200;
			return $this->success_response( ['automation_id' => $automation['id']], __( 'Recipe imported successfully.', 'wp-marketing-automations' ) );
		}else{
			return $this->error_response( __( 'Unable to import recipe.', 'wp-marketing-automations' ), null, 404 );
		}
	}

}

BWFAN_API_Loader::register( 'BWFAN_API_Import_Recipe' );