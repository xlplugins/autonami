<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BWFAN_Recipe_Loader {
	public static $included_recipe = array();

	/** @var array */
	private static $_registered_recipe = array();
	private static $instance = null;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'load_recipe' ], 8 );
		add_action( 'plugins_loaded', [ $this, 'register_classes' ], 9 );
		add_action( 'wp_ajax_bwfan_recipe_import', [ $this, 'bwfan_recipe_import' ] );
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFAN_Recipe_Loader|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Function to get all the data set
	 *
	 * @param string $key
	 *
	 * @return array|mixed
	 */
	public static function get_data( $key = '' ) {
		if ( empty( $key ) ) {
			return self::$data;
		}
		if ( isset( self::$data[ $key ] ) ) {
			return self::$data[ $key ];
		}

		return array();
	}

	/**
	 * Set recipe data
	 *
	 * @param $data
	 */
	public static function set_data( $data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				self::$data[ $key ] = $value;
			}
		}
	}

	/**
	 * Include all the recipe files
	 */
	public static function load_recipe() {
		$integration_dir = __DIR__ . '/recipes';
		foreach ( glob( $integration_dir . '/class-*.php' ) as $_field_filename ) {
			$file_data = pathinfo( $_field_filename );
			if ( isset( $file_data['basename'] ) && 'index.php' === $file_data['basename'] ) {
				continue;
			}
			require_once( $_field_filename );
		}
		do_action( 'bwfan_recipes_loaded' );

	}

	/**
	 * Register the integration when the integration file is included
	 *
	 * @param $shortName
	 * @param $class
	 * @param null $overrides
	 */
	public static function register( $class, $overrides = null ) {
		if ( ! class_exists( $class ) || ! method_exists( $class, 'get_instance' ) ) {
			return;
		}

		/**
		 * @var $instance BWFAN_Recipe
		 */
		$instance                          = $class::get_instance();
		$slug                              = $instance->get_slug();
		self::$_registered_recipe[ $slug ] = $instance;
	}

	/**
	 * Return all the actions with group and their integrations
	 *
	 * @return array
	 */
	public static function get_all_recipe() {
		return self::$included_recipe;
	}

	public static function get_recipes_filter_connectors() {
		$array = [];
		if ( ! is_array( self::$_registered_recipe ) || 0 === count( self::$_registered_recipe ) ) {
			return $array;
		}

		foreach ( self::$_registered_recipe as $recipe_slug => $recipe_data ) {
			if ( ! is_array( $recipe_data->data['connector-filter'] ) || 0 === count( $recipe_data->data['connector-filter'] ) ) {
				continue;
			}
			foreach ( $recipe_data->data['connector-filter'] as $connector_abbr ) {
				$array[ $connector_abbr ]['name']     = $recipe_data::get_connector_name( $connector_abbr );
				$array[ $connector_abbr ]['recipe'][] = $recipe_slug;
			}
		}

		return $array;
	}

	public static function get_recipes_filter_plugins() {
		$array = [];
		if ( ! is_array( self::$_registered_recipe ) || 0 === count( self::$_registered_recipe ) ) {
			return $array;
		}

		foreach ( self::$_registered_recipe as $recipe_slug => $recipe_data ) {
			if ( ! is_array( $recipe_data->data['plugin-filter'] ) || 0 === count( $recipe_data->data['plugin-filter'] ) ) {
				continue;
			}
			foreach ( $recipe_data->data['plugin-filter'] as $connector_abbr ) {
				$array[ $connector_abbr ]['name']     = $recipe_data->get_plugin_name( $connector_abbr );
				$array[ $connector_abbr ]['recipe'][] = $recipe_slug;
			}
		}

		return $array;
	}

	/** get recipes filters
	 * @return array
	 */
	public static function get_recipes_filter_integrations() {
		$array = [];
		if ( ! is_array( self::$_registered_recipe ) || 0 === count( self::$_registered_recipe ) ) {
			return $array;
		}

		foreach ( self::$_registered_recipe as $recipe_slug => $recipe_data ) {
			if ( ! is_array( $recipe_data->data['integration-filter'] ) || 0 === count( $recipe_data->data['integration-filter'] ) ) {
				continue;
			}
			foreach ( $recipe_data->data['integration-filter'] as $integration_abbr ) {
				$array[ $integration_abbr ] = $recipe_data->get_integration_name( $integration_abbr );
			}
		}

		return $array;
	}

	/** get channel filters
	 * @return array
	 */
	public static function get_recipes_filter_channel() {
		$array = [];
		if ( ! is_array( self::$_registered_recipe ) || 0 === count( self::$_registered_recipe ) ) {
			return $array;
		}

		foreach ( self::$_registered_recipe as $recipe_slug => $recipe_data ) {
			if ( ! is_array( $recipe_data->data['channel-filter'] ) || 0 === count( $recipe_data->data['channel-filter'] ) ) {
				continue;
			}
			foreach ( $recipe_data->data['channel-filter'] as $integration_abbr ) {
				$array[ $integration_abbr ] = $recipe_data->get_channel_name( $integration_abbr );
			}
		}

		return $array;
	}

	/** get type filters
	 * @return array
	 */
	public static function get_recipes_filter_type() {
		$array = [];
		if ( ! is_array( self::$_registered_recipe ) || 0 === count( self::$_registered_recipe ) ) {
			return $array;
		}

		foreach ( self::$_registered_recipe as $recipe_slug => $recipe_data ) {
			if ( ! is_array( $recipe_data->data['type-filter'] ) || 0 === count( $recipe_data->data['type-filter'] ) ) {
				continue;
			}
			foreach ( $recipe_data->data['type-filter'] as $integration_abbr ) {
				$array[ $integration_abbr ] = $recipe_data->get_type_name( $integration_abbr );
			}
		}

		return $array;
	}

	/** get type filters
	 * @return array
	 */
	public static function get_recipes_filter_goal() {
		$array = [];
		if ( ! is_array( self::$_registered_recipe ) || 0 === count( self::$_registered_recipe ) ) {
			return $array;
		}

		foreach ( self::$_registered_recipe as $recipe_slug => $recipe_data ) {
			if ( ! is_array( $recipe_data->data['goal-filter'] ) || 0 === count( $recipe_data->data['goal-filter'] ) ) {
				continue;
			}
			foreach ( $recipe_data->data['goal-filter'] as $integration_abbr ) {
				$array[ $integration_abbr ] = $recipe_data->get_goal_name( $integration_abbr );
			}
		}

		return $array;
	}

	/**
	 * Registers every integration as a system integration
	 */
	public function register_classes() {
		$load_classes = self::get_registered_recipes();

		if ( is_array( $load_classes ) && count( $load_classes ) > 0 ) {
			self::$included_recipe = $load_classes;
		}
	}

	/**
	 * Return the registered integrations
	 *$instance->get_data()
	 * @return mixed
	 */
	public static function get_registered_recipes() {
		return self::$_registered_recipe;
	}

	/**
	 *  on ajax call import recipe
	 */
	public function bwfan_recipe_import() {
		$recipe_slug = isset( $_POST['recipe_slug'] ) ? sanitize_text_field( $_POST['recipe_slug'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		if ( empty( $recipe_slug ) ) {
			return '';
		}

		// calling object of slug
		$recipe_slug_instance = new $recipe_slug();

		$checking_dependency = $recipe_slug_instance->checking_dependencies();
		if ( 1 !== $checking_dependency ) {
			return false;
		}

		die();
	}

	/** get recipe array
	 * @return array
	 */
	public static function get_recipes_array() {
		$all_recipe_data = array();
		$all_recipe      = self::get_all_recipe();

		if ( empty( $all_recipe ) ) {
			return array();
		}

		foreach ( $all_recipe as $recipe_key => $recipe_data ) {
			$data                                          = $recipe_data->get_data();
			$all_recipe_data[ $recipe_key ]['name']        = $data['name'];
			$all_recipe_data[ $recipe_key ]['description'] = $data['description'];
			$all_recipe_data[ $recipe_key ]['tag']         = $data['tags'];
			$all_recipe_data[ $recipe_key ]['integration'] = $data['integration-filter'];
			$all_recipe_data[ $recipe_key ]['channel']     = $data['channel-filter'];
			$all_recipe_data[ $recipe_key ]['goal']        = $data['goal-filter'];
			$all_recipe_data[ $recipe_key ]['type']        = $data['type-filter'];
			$all_recipe_data[ $recipe_key ]['media']       = '';
			$all_recipe_data[ $recipe_key ]['slug']        = $recipe_key;
			$all_recipe_data[ $recipe_key ]['json']        = $recipe_key;

		}

		return $all_recipe_data;
	}

}

if ( class_exists( 'BWFAN_Core' ) ) {
	BWFAN_Core::register( 'bwfan_recipe', 'BWFAN_Recipe_Loader' );

}
