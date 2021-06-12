<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BWFAN_Post_Table extends WP_List_Table {

	public static $per_page = 50;
	public static $current_page;
	public $data;
	public $meta_data;
	public $date_format;

	/**
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
	 * Show the status links for automations.
	 */
	public static function render_trigger_nav() {
		$get_campaign_statuses = apply_filters( 'bwfan_admin_trigger_nav', array(
			'all'      => __( 'All', 'wp-marketing-automations' ),
			'active'   => __( 'Active', 'wp-marketing-automations' ),
			'inactive' => __( 'Inactive', 'wp-marketing-automations' ),
		) );
		$html                  = '<ul class="subsubsub subsubsub_bwfan">';
		$html_inside           = array();
		$current_status        = 'all';
		if ( isset( $_GET['status'] ) && '' !== sanitize_text_field( $_GET['status'] ) ) { // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			$current_status = sanitize_text_field( $_GET['status'] ); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		}

		foreach ( $get_campaign_statuses as $slug => $status ) {
			$need_class = '';
			if ( $slug === $current_status ) {
				$need_class = 'current';
			}

			$url           = add_query_arg( array(
				'status' => $slug,
			), admin_url( 'admin.php?page=autonami-automations' ) );
			$html_inside[] = sprintf( '<li><a href="%s" class="%s">%s</a> </li>', $url, $need_class, $status );
		}

		if ( is_array( $html_inside ) && count( $html_inside ) > 0 ) {
			$html .= implode( '', $html_inside );
		}
		$html .= '</ul>';

		echo $html; //phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Text to display if no items are present.
	 * @return  void
	 * @since  1.0.0
	 */
	public function no_items() {
		echo esc_html( __( 'No Automation available.', 'wp-marketing-automations' ) );
	}

	/**
	 * Get all the automations and the data of automations for listing screen.
	 * @return array
	 */
	public function get_automations_data() {
		$automations = BWFAN_Core()->automations->get_all_automations();

		if ( ! is_array( $automations ) || count( $automations ) === 0 ) {
			return array();
		}

		$found_posts = array();
		// get those automations whose sync process is running.
		$active_sync_automations = BWFAN_Core()->automations->get_automations_sync_status();
		if ( is_array( $active_sync_automations ) && count( $active_sync_automations ) > 0 ) {
			$active_sync_automations = array_column( $active_sync_automations, 'a_id' );
		}

		$found_posts['found_posts'] = BWFAN_Model_Automations::count_rows();
		$items                      = array();
		foreach ( $automations as $automation ) {
			$automation_id          = $automation['id'];
			$automation_sync_status = ( in_array( $automation_id, $active_sync_automations, true ) ) ? 'data-sync-state="on"' : 'data-sync-state="off"';
			$automation_tasks       = [];

			$data = [
				'automation_id' => [
					'operator' => '%d',
					'value'    => $automation_id,
				],
				'status'        => [
					'operator' => '%d',
					'value'    => '0',
				],
			];

			$automation_tasks['scheduled'] = BWFAN_Model_Tasks::count( $data );
			$data['status']['value']       = '1';
			$automation_tasks['completed'] = BWFAN_Model_Logs::count( $data );
			$data['status']['value']       = '0';
			$automation_tasks['failed']    = BWFAN_Model_Logs::count( $data );

			$status   = $automation['status'];
			$priority = $automation['priority'];
			$source   = $automation['source'];
			$event    = $automation['event'];

			$automation_url      = add_query_arg( array(
				'page' => 'autonami-automations',
				'edit' => $automation_id,
			), admin_url( 'admin.php' ) );
			$row_actions         = array();
			$automation_actions  = array();
			$row_actions['edit'] = array(
				'action' => 'edit',
				'text'   => __( 'Edit', 'wp-marketing-automations' ),
				'link'   => $automation_url,
				'attrs'  => '',
			);

			$automation_delete_url = add_query_arg( array(
				'page'   => 'autonami',
				'delete' => $automation_id,
			), admin_url( 'admin.php' ) );

			$automation_export_url = add_query_arg( array(
				'page'   => 'bwfan-autonami-export',
				'export' => $automation_id,
			), admin_url( 'admin.php' ) );

			$automation_duplicate_url = add_query_arg( array(
				'page'                 => 'autonami',
				'duplicate_automation' => $automation_id,
			), admin_url( 'admin.php' ) );

			$row_actions['duplicate'] = array(
				'action' => 'bwfan-duplicate-automation',
				'text'   => __( 'Duplicate', 'wp-marketing-automations' ),
				'link'   => $automation_duplicate_url,
				'attrs'  => 'data-automation=' . $automation_id,
			);

			$row_actions['export'] = array(
				'action' => 'bwfan-export-automation',
				'text'   => __( 'Export', 'wp-marketing-automations' ),
				'link'   => $automation_export_url,
				'attrs'  => 'data-automation=' . $automation_id,
			);

			$row_actions['delete'] = array(
				'action' => 'delete',
				'text'   => __( 'Delete', 'wp-marketing-automations' ),
				'link'   => $automation_delete_url,
				'attrs'  => '',
			);

			$actions = array();
			if ( isset( $automation['meta']['actions'] ) ) {
				$integration_data = $automation['meta']['actions'];
				$unique_actions   = BWFAN_Core()->automations->get_unique_automation_actions( $integration_data );

				foreach ( $unique_actions as $action => $integration ) {
					$action_obj      = BWFAN_Core()->integration->get_action( $action );
					$integration_obj = BWFAN_Core()->integration->get_integration( $integration );
					if ( $integration_obj instanceof BWFAN_Integration && $action_obj instanceof BWFAN_Action ) {
						$nice_name               = $integration_obj->get_name();
						$actions[][ $nice_name ] = $action_obj->get_name();
					} else {
						$integration_name = BWFAN_Common::get_entity_nice_name( 'integration', $integration );
						$action_name      = BWFAN_Common::get_entity_nice_name( 'action', $action );
						if ( ! empty( $integration_name ) && ! empty( $action_name ) ) {
							$actions[][ $integration_name ] = $action_name;
						}
					}
				}
			}

			$title = ( isset( $automation['meta']['title'] ) && ! empty( $automation['meta']['title'] ) ) ? $automation['meta']['title'] : '';

			$automation_actions['actions'] = $actions;
			$items[ $automation_id ]       = array(
				'id'                     => $automation_id,
				'name'                   => $title,
				'last_update'            => get_date_from_gmt( $automation['meta']['m_date'], $this->date_format ),
				'status'                 => $status,
				'row_actions'            => $row_actions,
				'automation_actions'     => $automation_actions,
				'priority'               => $priority,
				'source'                 => __( 'Not Found', 'wp-marketing-automations' ),
				'event'                  => __( 'Not Found', 'wp-marketing-automations' ),
				'run_count'              => ( isset( $automation['meta']['run_count'] ) ) ? $automation['meta']['run_count'] : 0,
				'requires_update'        => $automation['meta']['requires_update'],
				'automation_sync_status' => $automation_sync_status,
				'tasks_count'            => $automation_tasks,
			);

			/** Source name */
			$single_source = BWFAN_Core()->sources->get_source( $source );
			if ( $single_source instanceof BWFAN_Source ) {
				$items[ $automation_id ]['source'] = $single_source->get_name();
			} else {
				$source_name = BWFAN_Common::get_entity_nice_name( 'source', $source );
				if ( ! empty( $source_name ) ) {
					$items[ $automation_id ]['source'] = $source_name;
				}
			}

			/** Event name */
			if ( ! empty( $event ) ) {
				$single_event = BWFAN_Core()->sources->get_event( $event );
				if ( $single_event instanceof BWFAN_Event ) {
					$items[ $automation_id ]['event'] = $single_event->get_name();

					if ( 1 === absint( $items[ $automation_id ]['requires_update'] ) ) {
						BWFAN_Common::mark_automation_require_update( $automation_id, false );
						$items[ $automation_id ]['requires_update'] = '0';
					}
				} else {
					$event_name = BWFAN_Common::get_entity_nice_name( 'event', $event );
					if ( ! empty( $event_name ) ) {
						$items[ $automation_id ]['event'] = $event_name;
					}

					/** Marking automation 'require_update' */
					if ( isset( $automation['meta']['requires_update'] ) && 1 !== absint( $automation['meta']['requires_update'] ) ) {
						BWFAN_Common::mark_automation_require_update( $automation_id );
						$items[ $automation_id ]['requires_update'] = '1';
					}
				}
			}
		}

		$found_posts['items'] = $items;

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
		$status = '';
		switch ( $column_name ) {
			case 'check-column':
				$status = '&nbsp;';
				break;
			case 'status':
				$status = $item[ $column_name ];
				break;
		}

		return $status;
	}

	/**
	 * Show the activate/deactivate column for automations.
	 *
	 * @param object $item
	 */
	public function column_cb( $item ) {
		$automation_status = '';
		if ( '1' === $item['status'] ) {
			$automation_status = "checked='checked'";
		}
		?>
        <div class='bwfan_fsetting_table_title'>
            <div class='offer_state bwfan_toggle_btn'>
                <input name='offer_state' id='state<?php echo esc_html( $item['id'] ); ?>' data-id="<?php echo esc_html( $item['id'] ); ?>" type='checkbox' class='bwfan-tgl bwfan-tgl-ios' <?php echo esc_html( $automation_status ); ?> <?php echo wp_kses_data( $item['automation_sync_status'] ); ?> />
                <label for='state<?php echo esc_html( $item['id'] ); ?>' class='bwfan-tgl-btn bwfan-tgl-btn-small'></label>
            </div>
        </div>
		<?php
	}

	/**
	 * Show delete automation link.
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_name( $item ) {
		unset( $item['row_actions']['delete'] );
		$edit_link     = $item['row_actions']['edit']['link'];
		$column_string = '<div><strong>';

		$column_string .= '<a href="' . $edit_link . '" class="row-title">' . $item['name'] . ' (#' . $item['id'] . ')</a>';
		$column_string .= '</strong>';
		$column_string .= "<div style='clear:both'></div></div>";
		$column_string .= '<div class=\'row-actions\'>';

		foreach ( $item['row_actions'] as $k => $action ) {

			$column_string .= '<span class="' . $action['action'] . '"><a href="' . $action['link'] . '" ' . $action['attrs'] . '>' . $action['text'] . '</a>';
			$column_string .= '</span>';
		}


		$column_string .= '<span class="delete"><a href="javascript:void(0);" class="bwfan-delete-automation" data-id="' . $item['id'] . '">' . __( 'Delete', 'wp-marketing-automations' ) . '</a></span>';
		$column_string .= '</div>';

		return ( $column_string );
	}

	public function column_last_update( $item ) {
		return $item['last_update'];
	}

	public function column_actions( $item ) {
		$column_string = '';
		if ( isset( $item['automation_actions']['actions'] ) && is_array( $item['automation_actions']['actions'] ) && count( $item['automation_actions']['actions'] ) > 0 ) {
			foreach ( $item['automation_actions']['actions'] as $value ) {
				foreach ( $value as $int => $action ) {
					$column_string .= apply_filters( 'bwfan_automation_list_col_action', $int . ': ' . $action, $action, $item ) . '<br>';
				}
			}
		} else {
			$column_string = __( 'N/A', 'wp-marketing-automations' );
		}

		return $column_string;
	}

	public function column_event( $item ) {
		return $item['source'] . ': ' . $item['event'];
	}

	public function column_run_count( $item ) {
		return $item['run_count'];
	}

	public function column_tasks( $item ) {
		$output = [];
		foreach ( $item['tasks_count'] as $key => $count ) {
			if ( absint( $count ) > 0 ) {
				/** with link */
				$url = add_query_arg( array(
					'page'       => 'autonami',
					'filter_aid' => $item['id'],
				), admin_url( 'admin.php' ) );

				if ( 'scheduled' === $key ) {
					/** scheduled */
					$url = add_query_arg( array(
						'tab'    => 'tasks',
						'status' => 't_0',
					), $url );
				} elseif ( 'completed' === $key ) {
					/** completed */
					$url = add_query_arg( array(
						'tab'    => 'logs',
						'status' => 'l_1',
					), $url );
				} elseif ( 'failed' === $key ) {
					/** failed */
					$url = add_query_arg( array(
						'tab'    => 'logs',
						'status' => 'l_0',
					), $url );
				}
				$output[] = ucfirst( $key ) . ": <a target='_blank' href='{$url}'>{$count}</a>";
			} else {
				$output[] = ucfirst( $key ) . ": {$count}";
			}
		}

		return implode( '<br/>', $output );
	}

	public function column_status( $item ) {
		$column_string = '<span class="dashicons dashicons-warning" title="' . __( 'Event doesn\'t exists. Kindly check the Automation and re-save it', 'wp-marketing-automations' ) . '"></span>';
		if ( 0 === absint( $item['requires_update'] ) ) {
			$column_string = '<span class="dashicons dashicons-yes" title="' . __( 'Automation is correctly configured', 'wp-marketing-automations' ) . '"></span>';
		}

		return $column_string;
	}

	/**
	 * Retrieve an array of possible bulk actions.
	 * @return array
	 * @since  1.0.0
	 */
	public function get_bulk_actions() {
		$actions = array();

		return $actions;
	}

	/**
	 * Prepare an array of items to be listed.
	 * return array Prepared items.
	 * @since  1.0.0
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$total_items = ( isset( $this->data['found_posts'] ) ) ? $this->data['found_posts'] : 0;

		$this->set_pagination_args( array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => self::$per_page, //WE have to determine how many items to show on a page
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
			'cb'        => '',
			'name'      => __( 'Name', 'wp-marketing-automations' ),
			'event'     => __( 'Event', 'wp-marketing-automations' ),
			'actions'   => __( 'Actions', 'wp-marketing-automations' ),
			'tasks'     => __( 'Tasks', 'wp-marketing-automations' ),
			'run_count' => __( 'Total Runs', 'wp-marketing-automations' ),
			'status'    => __( 'Status', 'wp-marketing-automations' ),
		);

		return $columns;
	}

	public function get_table_classes() {
		$get_default_classes = parent::get_table_classes();
		array_push( $get_default_classes, 'bwfan-instance-table' );
		array_push( $get_default_classes, 'bwfan-list-automations' );

		return $get_default_classes;
	}

	public function single_row( $item ) {
		$tr_class = 'bwfan_automation list_automations';
		echo '<tr class="' . esc_attr( $tr_class ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
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

		if ( ! empty( $_REQUEST['orderby'] ) ) { // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( $_REQUEST['orderby'] ) ) . '" />'; // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		}
		if ( ! empty( $_REQUEST['order'] ) ) { // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( $_REQUEST['order'] ) ) . '" />'; // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		}
		?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_html( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
            <input type="search" id="<?php echo esc_html( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php
			submit_button( $text, '', '', false, array(
				'id' => 'search-submit',
			) );
			?>
        </p>
		<?php
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @param string $which
	 *
	 * @since 3.1.0
	 *
	 */
	protected function display_tablenav( $which ) {
		?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php
			$this->extra_tablenav( $which );
			$this->pagination( 'bottom' );
			?>

            <br class="clear"/>
        </div>
		<?php
	}

}
