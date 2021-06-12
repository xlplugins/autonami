<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BWFCRM_Dashboard extends BWFCRM_Base_React_Page {
	private static $ins = null;
	public $page_data = [];

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function __construct() {
		if ( isset( $_GET['page'] ) && 'autonami' === $_GET['page'] ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 100 );
		}
	}

	public function enqueue_assets() {
		$this->prepare_data_for_enqueue();
		$this->enqueue_app_assets( 'main' );
	}

	public function render() {
		?>
		<div id="bwfcrm-page" class="bwfcrm-page"></div>
		<?php
	}
}
