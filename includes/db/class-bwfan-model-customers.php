<?php

class BWFAN_Model_Customers extends BWFAN_Model {
	static $primary_key = 'ID';

	public static function bwf_get_customer_data_by_id( $id, $by_contact = true ) {
		global $wpdb;

		if ( $by_contact ) {
			$query = $wpdb->prepare( "SELECT COALESCE(customer.total_order_count, 0) as orders, COALESCE(customer.total_order_value, 0) as revenue, contact.f_name, contact.l_name, contact.id as cid, contact.email FROM {$wpdb->prefix}bwf_contact as contact LEFT JOIN {$wpdb->prefix}bwf_wc_customers as customer ON contact.id = customer.cid WHERE contact.id = %d;",  $id );
		} else {
			$query = $wpdb->prepare( "SELECT COALESCE(customer.total_order_count, 0) as orders, COALESCE(customer.total_order_value, 0) as revenue, contact.f_name, contact.l_name, contact.id as cid, contact.email FROM {$wpdb->prefix}bwf_contact as contact LEFT JOIN {$wpdb->prefix}bwf_wc_customers as customer ON contact.id = customer.cid WHERE contact.wpid = %d;", $id );
		}
		$data = $wpdb->get_row($query);
		return $data;
	}

}
