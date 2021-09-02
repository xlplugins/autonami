<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class BWFAN_DB
 * @package Autonami
 * @author XlPlugins
 */
class BWFAN_DB {
	private static $ins = null;

	/**
	 * BWFAN_DB constructor.
	 */
	public function __construct() {
		global $wpdb;
		$wpdb->bwfan_automations         = $wpdb->prefix . 'bwfan_automations';
		$wpdb->bwfan_automationmeta      = $wpdb->prefix . 'bwfan_automationmeta';
		$wpdb->bwfan_tasks               = $wpdb->prefix . 'bwfan_tasks';
		$wpdb->bwfan_taskmeta            = $wpdb->prefix . 'bwfan_taskmeta';
		$wpdb->bwfan_task_claim          = $wpdb->prefix . 'bwfan_task_claim';
		$wpdb->bwfan_logs                = $wpdb->prefix . 'bwfan_logs';
		$wpdb->bwfan_logmeta             = $wpdb->prefix . 'bwfan_logmeta';
		$wpdb->bwfan_syncrecords         = $wpdb->prefix . 'bwfan_syncrecords';
		$wpdb->bwfan_message_unsubscribe = $wpdb->prefix . 'bwfan_message_unsubscribe';
		$wpdb->bwfan_cart_count          = $wpdb->prefix . 'bwfan_cart_counts';

		add_action( 'plugins_loaded', [ $this, 'load_db_classes' ], 8 );
		add_action( 'admin_init', [ $this, 'version_1_0_0' ], 10 );
	}

	/**
	 * Return the object of current class
	 *
	 * @return null|BWFAN_DB
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Include all the DB Table files
	 */
	public static function load_db_classes() {
		$integration_dir = __DIR__ . '/db';
		foreach ( glob( $integration_dir . '/class-*.php' ) as $_field_filename ) {
			$file_data = pathinfo( $_field_filename );
			if ( isset( $file_data['basename'] ) && 'index.php' === $file_data['basename'] ) {
				continue;
			}
			require_once( $_field_filename );
		}
	}

	/**
	 * Creating tables for v 1.0
	 */
	public function version_1_0_0() {
		/** create table in ver 1.0 */
		if ( false !== get_option( 'bwfan_ver_1_0', false ) ) {
			return;
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}
		$max_index_length = 191;

		$creationSQL = "CREATE TABLE {$wpdb->prefix}bwfan_automations (
 		  ID bigint(20) unsigned NOT NULL auto_increment,
 		  source varchar(60) NOT NULL,
 		  event varchar(60) NOT NULL,
 		  status tinyint(1) not null default 0 COMMENT '1 - Active 2 - Inactive',
 		  priority tinyint(3) not null default 0,
		  PRIMARY KEY  (ID),
		  KEY ID (ID),
		  KEY status (status)
		) $collate;";
		dbDelta( $creationSQL );

		$creationSQL = "CREATE TABLE {$wpdb->prefix}bwfan_automationmeta (
		  ID bigint(20) unsigned NOT NULL auto_increment,
		  bwfan_automation_id bigint(20) unsigned NOT NULL default '0',
		  meta_key varchar(255) default NULL,
		  meta_value longtext,
		  PRIMARY KEY  (ID),
		  KEY bwfan_automation_id (bwfan_automation_id),
		  KEY meta_key (meta_key($max_index_length))
		) $collate;";
		dbDelta( $creationSQL );

		$creationSQL = "CREATE TABLE {$wpdb->prefix}bwfan_tasks (
		  ID bigint(20) unsigned NOT NULL auto_increment,
 		  c_date datetime NOT NULL default '0000-00-00 00:00:00',
 		  e_date bigint(12) NOT NULL,
 		  automation_id int(10) not null,
 		  integration_slug varchar(50) default NULL,
 		  integration_action varchar(100) default NULL,
 		  status int(1) not null default 0 COMMENT '0 - Pending 1 - Paused',
		  claim_id bigint(20) unsigned default 0,
		  attempts tinyint(1) unsigned default 0,
		  priority int(5) unsigned default 10,
		  PRIMARY KEY  (ID),
		  KEY ID (ID),
		  KEY status (status),
		  KEY automation_id (automation_id)
		) $collate;";
		dbDelta( $creationSQL );

		$creationSQL = "CREATE TABLE {$wpdb->prefix}bwfan_taskmeta (
		  ID bigint(20) unsigned NOT NULL auto_increment,
		  bwfan_task_id bigint(20) unsigned NOT NULL default '0',
		  meta_key varchar(255) default NULL,
		  meta_value longtext,
		  PRIMARY KEY  (ID),
		  KEY bwfan_task_id (bwfan_task_id),
		  KEY meta_key (meta_key($max_index_length))
		) $collate;";
		dbDelta( $creationSQL );

		$creationSQL = "CREATE TABLE {$wpdb->prefix}bwfan_task_claim (
		  claim_id bigint(20) unsigned NOT NULL auto_increment,
		  date_created_gmt datetime NOT NULL default '0000-00-00 00:00:00',
		  PRIMARY KEY  (claim_id),
		  KEY date_created_gmt (date_created_gmt)
		) $collate;";
		dbDelta( $creationSQL );

		$creationSQL = "CREATE TABLE {$wpdb->prefix}bwfan_logs (
		  ID bigint(20) unsigned NOT NULL auto_increment,
 		  c_date datetime NOT NULL default '0000-00-00 00:00:00',
 		  e_date bigint(12) NOT NULL,
 		  status int(1) not null default 0 COMMENT '0 - Failed 1 - Success',
 		  integration_slug varchar(50) default NULL,
 		  integration_action varchar(100) default NULL,
 		  automation_id int(10) not null,
		  PRIMARY KEY  (ID),
		  KEY ID (ID),
		  KEY status (status),
		  KEY automation_id (automation_id)
		) $collate;";
		dbDelta( $creationSQL );

		$creationSQL = "CREATE TABLE {$wpdb->prefix}bwfan_logmeta (
		  ID bigint(20) unsigned NOT NULL auto_increment,
		  bwfan_log_id bigint(20) unsigned NOT NULL default '0',
		  meta_key varchar(255) default NULL,
		  meta_value longtext,
		  PRIMARY KEY  (ID),
		  KEY bwfan_log_id (bwfan_log_id),
		  KEY meta_key (meta_key($max_index_length))
		) $collate;";
		dbDelta( $creationSQL );

		$creationSQL = "CREATE TABLE {$wpdb->prefix}bwfan_syncrecords (
		  ID bigint(20) unsigned NOT NULL auto_increment,	
		  a_id bigint(20) NOT NULL,
		  total int(10) not NULL,
		  sync_date bigint(12) NOT NULL,
		  processed int(10) NOT NULL,
		  offset int(10) NOT NULL,
          status int(10) not NULL COMMENT '1 - Processing 2 - Completed 3 - Stopped',
          sync_data longtext,
		  PRIMARY KEY  (ID),
		  KEY ID (ID),
		  KEY a_id (a_id),
		  KEY status (status)
		) $collate;";
		dbDelta( $creationSQL );

		$creationSQL = "CREATE TABLE {$wpdb->prefix}bwfan_message_unsubscribe (
		  ID bigint(20) unsigned NOT NULL auto_increment,
		  recipient varchar(255) default NULL,
          mode tinyint(1) NOT NULL COMMENT '1 - Email 2 - SMS' default 1,
		  c_date datetime NOT NULL default '0000-00-00 00:00:00',
		  automation_id bigint(20) unsigned default '0',
		  c_type tinyint(1) NOT NULL default '1'  COMMENT '1 - Automation 2 - Broadcasr 3 - Manual 4 - Form',
		  PRIMARY KEY  (ID),
		  KEY ID (ID),
		  KEY recipient (recipient($max_index_length)),
		  KEY mode (mode),
		  KEY automation_id (automation_id),
		  KEY c_type (c_type),
		  KEY c_date (c_date)
		) $collate;";
		dbDelta( $creationSQL );

		$creationSQL = "CREATE TABLE {$wpdb->prefix}bwfan_contact_automations (
		  contact_id bigint(20) NOT NULL,
		  automation_id bigint(20) NOT NULL,
		  time bigint(12) NOT NULL,
		  KEY contact_id (contact_id),
		  KEY automation_id (automation_id)
		) $collate;";
		dbDelta( $creationSQL );

		$creationSQL = "CREATE TABLE {$wpdb->prefix}bwfan_abandonedcarts (
		  ID bigint(20) unsigned NOT NULL auto_increment,	
		  email varchar(32) NOT NULL,	  
		  status int(1) not null default 0,
		  user_id bigint(20) not null default 0,
		  last_modified datetime NOT NULL,
		  created_time datetime NOT NULL,
		  items longtext,
		  coupons longtext,
		  fees longtext,
		  shipping_tax_total varchar(32),
		  shipping_total varchar(32),
		  total varchar(32),
		  total_base varchar(32),
		  token varchar(32) NOT NULL,
		  currency varchar(8) NOT NULL,
		  cookie_key varchar(32) NOT NULL,
		  checkout_data longtext,
		  order_id bigint(20) not null,
		  checkout_page_id bigint(20) not null,
		  PRIMARY KEY  (ID),
		  KEY ID (ID),
		  KEY status (status),
		  KEY user_id (user_id),
		  KEY email (email),
		  KEY last_modified (last_modified),
		  KEY token (token)
		) $collate;";
		dbDelta( $creationSQL );

		do_action( 'bwfan_db_1_0_tables_created' );

		update_option( 'bwfan_ver_1_0', date( 'Y-m-d' ), true );

		/** Unique key to share in rest calls */
		$unique_key = md5( time() );
		update_option( 'bwfan_u_key', $unique_key, true );

		/** Scheduling actions one-time */
		$this->schedule_actions();

		/** Auto global settings */
		if ( BWFAN_Plugin_Dependency::woocommerce_active_check() ) {
			$global_option = get_option( 'bwfan_global_settings', array() );

			$global_option['bwfan_ab_enable'] = true;
			update_option( 'bwfan_global_settings', $global_option, true );

			/** Insert default automations */
			$this->insert_automation_on_activation();
		}
	}

	protected function schedule_actions() {
		$ins = BWFAN_Admin::get_instance();
		$ins->maybe_set_as_ct_worker();
		$ins->schedule_abandoned_cart_cron();
	}

	/**
	 *  create automation on first time plugin activation
	 */
	protected function insert_automation_on_activation() {
		if ( ! class_exists( 'BWFAN_Model_Automations' ) ) {
			return;
		}

		/** Checking if any automation exist */
		$count = BWFAN_Model_Automations::count_rows();
		if ( $count > 0 ) {
			return;
		}

		/** Json files */
		$json_file_path = BWFAN_PLUGIN_DIR . '/admin/json/';
		$files          = array_diff( scandir( $json_file_path ), array( '.', '..', 'index.php' ) );

		/** If files available */
		if ( empty( $files ) || ! is_array( $files ) ) {
			return;
		}

		BWFAN_Common::create_automation_on_activation( $files );
	}
}

if ( class_exists( 'BWFAN_DB' ) ) {
	BWFAN_Core::register( 'db', 'BWFAN_DB' );
}
