<?php

/**
 * Class BWFAN_Source
 */
abstract class BWFAN_Source {
	protected $show_in_ui = true;
	protected $localize_data = [];
	protected $nice_name = '';
	protected $event_dir = __DIR__;
	protected $slug = '';
	protected $group_slug = '';
	protected $group_name = '';
	protected $priority = 0;

	/**
	 * Loads all events of current trigger
	 */
	public function load_events() {
		$resource_dir    = $this->event_dir . '/events';
		$global_settings = BWFAN_Common::get_global_settings();
		if ( @file_exists( $resource_dir ) ) { //phpcs:ignore PHP_CodeSniffer - Generic.PHP.NoSilencedErrors, Generic.PHP.NoSilencedErrors
			foreach ( glob( $resource_dir . '/class-*.php' ) as $_field_filename ) {
				$file_data = pathinfo( $_field_filename );
				if ( isset( $file_data['basename'] ) && 'index.php' === $file_data['basename'] ) {
					continue;
				}
				$event_class = require_once( $_field_filename );
				if ( is_string( $event_class ) && method_exists( $event_class, 'get_instance' ) ) {
					/**
					 * @var $event_obj BWFAN_Event
					 */
					$event_obj = $event_class::get_instance();

					BWFAN_Load_Sources::$all_events[ $this->get_name() ][ $event_obj->get_slug() ] = $event_obj->get_name();
					if ( isset( $global_settings[ 'bwfan_stop_event_' . $event_obj->get_slug() ] ) && ! empty( $global_settings[ 'bwfan_stop_event_' . $event_obj->get_slug() ] ) ) {
						continue;
					}

					$event_obj->load_hooks();
					$event_obj->set_source_type( $this->get_slug() );
					BWFAN_Load_Sources::register_events( $event_obj );
				}
			}

			do_action( 'bwfan_' . $this->get_slug() . '_events_loaded' );
		}
	}

	public function get_slug() {
		$this->slug = str_replace( array( 'bwfan_', '_source' ), '', sanitize_title( get_class( $this ) ) );

		return $this->slug;
	}

	public function get_group_slug() {
		return $this->group_slug;
	}

	public function get_group_name() {
		return $this->group_name;
	}

	public function get_localize_data() {

		$this->localize_data = [
			'show_in_ui' => $this->show_in_ui(),
			'slug'       => $this->get_slug(),
			'group_slug' => $this->get_group_slug(),
			'group_name' => $this->get_group_name(),
			'nice_name'  => $this->get_name(),
			'priority'   => $this->priority,
			'available'  => 'yes',
		];

		return $this->localize_data;
	}

	public function show_in_ui() {
		return $this->show_in_ui;
	}

	public function get_name() {
		return $this->nice_name;
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
