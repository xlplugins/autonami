<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BWFAN_Unsubscribers_Table extends WP_List_Table {

	public static $per_page = 20;
	public static $current_page;
	public $data;
	public $date_format;

	/**meta_data
	 * Constructor.
	 * @since  1.0.0
	 */
	public function __construct( $args = array() ) {
		self::$current_page = $this->get_pagenum();
		$this->data         = array();
		$this->date_format  = BWFAN_Common::get_date_format();

		// Make sure this file is loaded, so we have access to plugins_api(), etc.
		require_once( ABSPATH . '/wp-admin/includes/plugin-install.php' );

		parent::__construct( $args );
	}

	/**
	 * Text to display if no items are present.
	 * @return  void
	 * @since  1.0.0
	 */
	public function no_items() {
		echo esc_html( __( 'No unsubscribers available.', 'wp-marketing-automations' ) );
	}

	/** Made the data for Unsubscribers screen.
	 * @return array
	 */
	public function get_unsubscribers_table_data() {
		global $wpdb;
		$where    = '';
		$paged    = ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) ? sanitize_text_field( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		$per_page = ( isset( $_GET['posts_per_page'] ) && ! empty( $_GET['posts_per_page'] ) ) ? sanitize_text_field( $_GET['posts_per_page'] ) : self::$per_page; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		$offset   = ( $paged - 1 ) * $per_page;

		/** Check for search unsubscriber */
		if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			$where = "WHERE `recipient` = '" . sanitize_text_field( $_GET['s'] ) . "'"; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		/** Query to fetch unsubscribers data from DB */
		$unsubscribers = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bwfan_message_unsubscribe $where ORDER BY ID DESC LIMIT $offset,$per_page " );//phpcs:ignore WordPress.DB.PreparedSQL

		if ( empty( $unsubscribers ) ) {
			return array();
		}

		$found_posts = array();
		$items       = array();

		foreach ( $unsubscribers as $unsubscriber ) {
			$items[] = array(
				'id'            => $unsubscriber->ID,
				'recipient'     => $unsubscriber->recipient,
				'date'          => date( $this->date_format, strtotime( $unsubscriber->c_date ) ),
				'automation_id' => $unsubscriber->automation_id,
			);
		}

		$found_posts['found_posts'] = BWFAN_Model_Message_Unsubscribe::count_rows();
		$found_posts['items']       = $items;

		return $found_posts;
	}

	/**
	 * The content of each column.
	 *
	 * @param array $item The current item in the list.
	 * @param string $column_name The key of the current column.
	 *
	 * @return string              Output for the current column.
	 * @since  1.0.0
	 */
	public function column_default( $item, $column_name ) {
		$column_temp = '';
		switch ( $column_name ) {
			case 'recipient':
				$column_temp = $item[ $column_name ];
				break;
			case 'create_date':
				$column_temp = $item[ $column_name ];
				break;
			case 'automation':
				$column_temp = $item[ $column_name ];
				break;
		}

		return $column_temp;
	}

	/**
	 * Prepare an array of items to be listed.
	 * @since  1.0.0
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$total_items           = ( isset( $this->data['found_posts'] ) ) ? $this->data['found_posts'] : 0;

		$this->set_pagination_args( array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => self::$per_page, //WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / self::$per_page ),
		) );

		$this->items = ( isset( $this->data['items'] ) ) ? $this->data['items'] : array();
	}

	/**
	 * Retrieve an array of columns for the list table.
	 * @return array Key => Value pairs.
	 * @since  1.0.0
	 */
	public function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'recipient'   => __( 'Contact', 'wp-marketing-automations' ),
			'create_date' => __( 'Date', 'wp-marketing-automations' ),
			'automation'  => __( 'Automation', 'wp-marketing-automations' ),
		);

		return $columns;
	}

	public function process_bulk_action() {
		if ( ! isset( $_GET['action'] ) || ! isset( $_GET['action2'] ) || ! isset( $_GET['bwfan_unsubscriber_ids'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			return;
		}
		if ( 'bwfan_delete_unsubscribers' !== $_GET['action'] && 'bwfan_delete_unsubscribers' !== $_GET['action2'] ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification
			return;
		}

		$ids = $_GET['bwfan_unsubscriber_ids']; // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.Security.ValidatedSanitizedInput
		if ( empty( $ids ) || ! is_array( $ids ) ) {
			return;
		}

		/** Bulk Delete Unsubscribers */
		foreach ( $ids as $id ) {
			$where = array(
				'ID' => $id,
			);
			BWFAN_Model_Message_Unsubscribe::delete_message_unsubscribe_row( $where );
		}

		do_action( 'bwfan_bulk_delete_unsubscribers' );
	}

	/**
	 * Retrieve an array of possible bulk actions.
	 * @return array
	 * @since  1.0.0
	 */
	public function get_bulk_actions() {
		return array(
			'bwfan_delete_unsubscribers' => 'Delete',
		);
	}

	public function column_cb( $item ) {
		?>
        <div class='bwfan_fsetting_table_title'>
            <div class=''>
                <input name='bwfan_unsubscriber_ids[]' data-id="<?php echo esc_html( $item['id'] ); ?>" value="<?php echo esc_html( $item['id'] ); ?>" type='checkbox' class=''>
                <label for='' class=''></label>
            </div>
        </div>
		<?php
	}

	public function column_recipient( $item ) {
		return '<div class="bwfan-unsubscriber_recipient">' . $item['recipient'] . '</div>';
	}

	public function column_create_date( $item ) {
		return '<div class="bwfan-unsubscriber_create_date">' . $item['date'] . '</div>';
	}

	public function column_automation( $item ) {
		if ( 0 !== absint( $item['automation_id'] ) ) {
			$automation_name = BWFAN_Model_Automationmeta::get_meta( $item['automation_id'], 'title' ) . ' (# ' . $item['automation_id'] . ')';
			$automation_url  = add_query_arg( array(
				'page' => 'autonami-automations',
				'edit' => $item['automation_id'],
			), admin_url( 'admin.php' ) );

			return '<div class="bwfan-unsubscriber_automation"><a href="' . $automation_url . '">' . $automation_name . '</a></div>';
		}

		return '<div class="bwfan-unsubscriber_automation">' . __( 'N.A.', 'wp-marketing-automations' ) . '</div>';
	}

	/**
	 * Displays the search box.
	 *
	 * @param string $text The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 *
	 * @since 3.1.0
	 *
	 */
	public function search_box( $text = '', $input_id = 'bwfan' ) {
		$input_id = $input_id . '-search-input';

		?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
            <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php
			submit_button( $text, '', '', false, array(
				'id' => 'search-submit',
			) );
			?>
        </p>
		<?php
	}


}
