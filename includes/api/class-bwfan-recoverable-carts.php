<?php

class BWFAN_Recoverable_Carts {

	public static function get_abandoned_carts( $search_by = '', $search_term = '', $offset = '', $limit = '', $status = '', $count = false ) {
		global $wpdb;

		if ( empty( $status ) ) {
			$status = '0,1,3,4';
		}
		$where = "WHERE status IN ($status)";
		/** Check for search query */
		if ( ! empty( $search_by ) && ! empty( $search_term ) && $count === false ) { //phpcs:ignore WordPress.Security.NonceVerification
			$search_term = sanitize_text_field( $search_term ); //phpcs:ignore WordPress.Security.NonceVerification
			$where       .= " AND " . $search_by . " like '%" . $search_term . "%'"; //phpcs:ignore WordPress.Security.NonceVerification
		}
		$result = [];
		if ( $count === false ) {
			$result = BWFAN_Model_Abandonedcarts::get_abandoned_data( $where, $offset, $limit, 'last_modified' );
		}
		$query                 = "SELECT COUNT(*) FROM {$wpdb->prefix}bwfan_abandonedcarts $where";
		$result['total_count'] = $wpdb->get_var( $query ); // WPCS: unprepared SQL OK

		return $result;
	}

	/**
	 * Delete abandoned carts
	 *
	 * @param $ids
	 */
	public static function delete_abandoned_cart( $ids ) {
		$not_deleted_cart = [];
		foreach ( $ids as $id ) {
			$cart_details = BWFAN_Model_Abandonedcarts::get( $id );

			if ( empty( $cart_details ) ) {
				$not_deleted_cart [] = $id;
				continue;
			}

			$where = array(
				'ID' => $id,
			);
			BWFAN_Model_Abandonedcarts::delete_abandoned_cart_row( $where );
		}

		$not_deleted_cart = array_filter( array_values( $not_deleted_cart ) );

		do_action( 'bwfan_bulk_delete_abandoned_carts' );

		if ( ! empty( $not_deleted_cart ) ) {
			return $not_deleted_cart;
		}

		return true;
	}

	/**
	 * Retry abandoned carts
	 *
	 * @param $ids
	 */
	public static function retry_abandoned_cart( $ids ) {

		BWFAN_Common::update_abandoned_rows( $ids, 4 );

		do_action( 'bwfan_bulk_retry_abandoned_carts' );
	}

	/** Made the data for recovered carts screen.
	 * @return array
	 */
	public static function get_recovered_carts( $search = '', $offset = '', $limit = '', $only_count = false ) {
		global $wpdb;
		$where     = '';
		$left_join = '';
		/** Check for search query */
		if ( isset( $search ) && ! empty( $search ) && $only_count === false ) { //phpcs:ignore WordPress.Security.NonceVerification
			$left_join = " LEFT JOIN {$wpdb->prefix}postmeta as m1 ON p.ID = m1.post_id ";
			$where     = ' AND m1.meta_key = "_billing_email" ';
			$where     .= " AND m1.meta_value LIKE '%$search%' "; //phpcs:ignore WordPress.Security.NonceVerification
			$where     .= " AND m.meta_value > 0 ";
		}

		$post_statuses = apply_filters( 'bwfan_recovered_cart_excluded_statuses', array(
			'wc-pending',
			'wc-failed',
			'wc-cancelled',
			'wc-refunded',
			'trash',
			'draft'
		) );
		$post_status   = '(';
		foreach ( $post_statuses as $status ) {
			$post_status .= "'" . $status . "',";
		}
		$post_status .= "'')";
		$found_posts = array();
		if ( $only_count === false ) {
			$query           = $wpdb->prepare( "SELECT p.ID as id FROM {$wpdb->prefix}posts as p LEFT JOIN {$wpdb->prefix}postmeta as m ON p.ID = m.post_id $left_join WHERE p.post_type = %s AND p.post_status NOT IN $post_status AND m.meta_key = %s $where ORDER BY p.post_modified DESC LIMIT $offset,$limit", 'shop_order', '_bwfan_ab_cart_recovered_a_id' );
			$recovered_carts = $wpdb->get_results( $query, ARRAY_A );//phpcs:ignore WordPress.DB.PreparedSQL

			if ( empty( $recovered_carts ) ) {
				return array();
			}
			$items = array();

			foreach ( $recovered_carts as $recovered_cart ) {
				if ( function_exists( 'wc_get_order' ) ) {
					$items[] = wc_get_order( $recovered_cart['id'] );
				}
			}

			$found_posts['items'] = $items;
		}
		$count_query                = $wpdb->prepare( "SELECT COUNT(p.ID) FROM {$wpdb->prefix}posts as p LEFT JOIN {$wpdb->prefix}postmeta as m ON p.ID = m.post_id $left_join WHERE p.post_type = %s AND p.post_status NOT IN $post_status AND m.meta_key = %s $where ", 'shop_order', '_bwfan_ab_cart_recovered_a_id' );
		$found_posts['total_count'] = $wpdb->get_var( $count_query );//phpcs:ignore WordPress.DB.PreparedSQL

		return $found_posts;
	}

	/** task details for abandoned id
	 *
	 * @param $abandoned_id
	 *
	 * @return array
	 */
	public static function get_cart_tasks( $abandoned_id ) {
		global $wpdb;
		$abandoned_tasks = $wpdb->get_results( $wpdb->prepare( "
								SELECT t.ID as id, t.integration_slug as slug, t.integration_action as action, t.automation_id as a_id, t.status as status, t.e_date as date
								FROM {$wpdb->prefix}bwfan_tasks as t
								LEFT JOIN {$wpdb->prefix}bwfan_taskmeta as m
								ON t.ID = m.bwfan_task_id
								WHERE m.meta_key = %s
								AND m.meta_value = %d
								ORDER BY t.e_date DESC
								", 'c_a_id', $abandoned_id ), ARRAY_A );
		$abandoned_logs  = $wpdb->get_results( $wpdb->prepare( "
								SELECT l.ID as id, l.integration_slug as slug, l.integration_action as action, l.automation_id as a_id, l.status as status, l.e_date as date
								FROM {$wpdb->prefix}bwfan_logs as l
								LEFT JOIN {$wpdb->prefix}bwfan_logmeta as m
								ON l.ID = m.bwfan_log_id
								WHERE m.meta_key = %s
								AND m.meta_value = %d
								ORDER BY l.e_date DESC
								", 'c_a_id', $abandoned_id ), ARRAY_A );

		if ( empty( $abandoned_tasks ) && empty( $abandoned_logs ) ) {
			return array();
		}

		BWFAN_Core()->automations->return_all = true;
		$active_automations                   = BWFAN_Core()->automations->get_all_automations();
		BWFAN_Core()->automations->return_all = false;

		if ( ! class_exists( 'BWFCRM_Automations' ) ) {
			return '';
		}

		$crm_automation = new BWFCRM_Automations();

		if ( ! empty( $abandoned_tasks ) ) {
			$tasks = $crm_automation->get_tasks_data( $active_automations, $abandoned_tasks );
			if ( is_array( $tasks ) && count( $tasks ) > 0 ) {
				$crm_automation->get_tasks_items( $active_automations, $tasks );
			}
		}
		if ( ! empty( $abandoned_logs ) ) {
			$logs = $crm_automation->get_tasks_data( $active_automations, $abandoned_logs, 'logs' );
			if ( is_array( $logs ) && count( $logs ) > 0 ) {
				$crm_automation->get_tasks_items( $active_automations, $logs, 'logs' );
			}
		}

		if ( empty( $crm_automation->task_localized ) ) {
			return array();
		}

		krsort( $crm_automation->task_localized['result'] );
		$items = [];
		foreach ( $crm_automation->task_localized['result'] as $value ) {
			$items[] = $crm_automation->task_localized[ $value['type'] ][ $value['id'] ];
		}

		return $items;
	}

	/** getting contact id
	 *
	 * @param $email
	 *
	 * @return int
	 */
	public static function get_contact_id( $email ) {
		$contact_id = 0;
		if ( ! is_email( $email ) ) {
			return $contact_id;
		}

		$contact_obj = bwf_get_contact( null, $email );
		if ( ! $contact_obj instanceof WooFunnels_Contact ) {
			return $contact_id;
		}

		$contact_id = $contact_obj->id;
		if ( is_null( $contact_id ) || empty( $contact_id ) ) {
			return 0;
		}

		return $contact_id;
	}

	/**
	 * @param $contact_id
	 * @param $checkout_data
	 *
	 * @return string[]
	 */
	public static function get_name( $contact_id, $checkout_data ) {
		$data          = array( 'f_name' => '', 'l_name' => '' );
		$contact_array = array();
		if ( ! empty( $contact_id ) ) {
			if ( class_exists( 'BWFCRM_Contact' ) ) {
				$contact        = new BWFCRM_Contact( $contact_id );
				$contact_array  = $contact->get_array();
				$data['f_name'] = $contact_array['f_name'];
				$data['l_name'] = $contact_array['l_name'];

				return $data;
			}

			$contact_array  = ( new WooFunnels_Contact( null, null ) )->get_contact_by_contact_id( $contact_id );
			$data['f_name'] = $contact_array->f_name;
			$data['l_name'] = $contact_array->l_name;

			return $data;
		}

		$checkout_data = json_decode( $checkout_data, true );

		if ( ! isset( $checkout_data['fields'] ) || ! is_array( $checkout_data['fields'] ) ) {
			return $data;
		}

		foreach ( $checkout_data['fields'] as $field_key => $value ) {
			if ( 'billing_first_name' === $field_key ) {
				$data['f_name'] = $value;
			}

			if ( 'billing_last_name' === $field_key ) {
				$data['l_name'] = $value;
			}
		}

		return $data;
	}

	/** Add f_name, l_name, contact_id to carts */
	public static function populate_contact_info( $result ) {
		if ( ! is_array( $result ) || empty( $result ) ) {
			return $result;
		}

		global $wpdb;
		$table  = $wpdb->prefix . 'bwf_contact';
		$emails = array_column( $result, 'email' );
		$emails = array_map( 'trim', array_filter( $emails ) );
		$emails = implode( "','", $emails );

		$sql       = "SELECT id, email, f_name, l_name from $table WHERE email in ('$emails')";
		$db_result = $wpdb->get_results( $sql, ARRAY_A );
		$contacts  = [];

		if ( is_array( $db_result ) && ! empty( $db_result ) ) {
			foreach ( $db_result as $item ) {
				$contacts[ $item['email'] ] = $item;
			}
		}

		foreach ( $result as $index => $item ) {
			$email = $item['email'];
			/** If Contact ID found in DB Results */
			if ( isset( $contacts[ $email ]['id'] ) && ! empty( $contacts[ $email ]['id'] ) ) {
				if ( empty( $result[ $index ]['f_name'] ) ) {
					$result[ $index ]['f_name'] = $contacts[ $email ]['f_name'];
				}

				if ( empty( $result[ $index ]['l_name'] ) ) {
					$result[ $index ]['l_name'] = $contacts[ $email ]['l_name'];
				}

				$result[ $index ]['contact_id'] = $contacts[ $email ]['id'];
			}

			/** Else get first name and last name from Checkout Data */
			if ( ! isset( $item['checkout_data'] ) || empty( $item['checkout_data'] ) ) {
				continue;
			}

			$checkout_data = json_decode( $item['checkout_data'], true );
			if ( ! isset( $checkout_data['fields'] ) || ! is_array( $checkout_data['fields'] ) ) {
				continue;
			}

			foreach ( $checkout_data['fields'] as $field_key => $value ) {
				if ( empty( $result[ $index ]['f_name'] ) && 'billing_first_name' === $field_key && ! empty( $value ) ) {
					$result[ $index ]['f_name'] = $value;
				}

				if ( empty( $result[ $index ]['l_name'] ) && 'billing_last_name' === $field_key && ! empty( $value ) ) {
					$result[ $index ]['l_name'] = $value;
				}
			}

			unset( $result[ $index ]['checkout_data'] );
		}

		return $result;
	}
}
