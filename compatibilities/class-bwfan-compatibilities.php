<?php

/**
 * Class BWFAN_Compatibilities
 * Loads all the compatibilities files we have in Autonami against plugins
 */
class BWFAN_Compatibilities {


	public static function load_all_compatibilities() {

		// load all the BWFAN_Compatibilities files automatically
		foreach ( glob( plugin_dir_path( BWFAN_PLUGIN_FILE ) . '/compatibilities/*.php' ) as $_field_filename ) {

			require_once( $_field_filename );
		}
	}
}

//hooked over 999 so that all the plugins got initiated by that time
add_action( 'plugins_loaded', array( 'BWFAN_Compatibilities', 'load_all_compatibilities' ), 999 );
