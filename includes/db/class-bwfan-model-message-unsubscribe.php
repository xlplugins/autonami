<?php

class BWFAN_Model_Message_Unsubscribe extends BWFAN_Model {
	static $primary_key = 'ID';

	/**
	 * @param $where
	 * @param bool $single_row
	 *
	 * @return array|object|string|void|null
	 */
	public static function get_message_unsubscribe_row( $where, $single_row = true ) {
		global $wpdb;

		if ( empty( $where ) || ! is_array( $where ) ) {
			return '';
		}
		if ( true === $single_row ) {
			return $wpdb->get_row( self::prepare_message_unsubscribe_sql( $where ), ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL
		} else {
			return $wpdb->get_results( self::prepare_message_unsubscribe_sql( $where ), ARRAY_A );
		}
	}

	/**
	 * @param $data
	 *
	 * @return string
	 *
	 * Function to create query to get data from table
	 */
	private static function prepare_message_unsubscribe_sql( $data ) {
		global $wpdb;
		$where      = '';
		$count      = count( $data );
		$i          = 0;
		$table_name = $wpdb->prefix . 'bwfan_message_unsubscribe';

		foreach ( $data as $key => $value ) {
			$i ++;

			if ( 'string' === gettype( $value ) ) {
				$where .= '`' . $key . '` = ' . "'" . $value . "'";
			} elseif ( is_array( $value ) ) {
				$where .= '`' . $key . "` IN ('" . implode( "','", $value ) . "')";
			} else {
				$where .= '`' . $key . '` = ' . $value;
			}

			if ( $i < $count ) {
				$where .= ' AND ';
			}
		}

		return 'SELECT * FROM ' . $table_name . " WHERE $where";
	}

	/**
	 * @param $data
	 *
	 * @return array|false|int|void
	 *
	 * Delete row from table
	 */
	public static function delete_message_unsubscribe_row( $data ) {
		if ( ! is_array( $data ) || empty( $data ) ) {
			return;
		}

		global $wpdb;
		$where      = '';
		$count      = count( $data );
		$i          = 0;
		$table_name = $wpdb->prefix . 'bwfan_message_unsubscribe';

		foreach ( $data as $key => $value ) {
			$i ++;

			if ( 'string' === gettype( $value ) ) {
				$where .= '`' . $key . '` = ' . "'" . $value . "'";
			} else {
				$where .= '`' . $key . '` = ' . $value;
			}

			if ( $i < $count ) {
				$where .= ' AND ';
			}
		}

		return $wpdb->query( 'DELETE FROM ' . $table_name . " WHERE $where" ); // WPCS: unprepared SQL OK
	}

	public static function delete_unsubscribers( $emails_or_phones = [] ) {
		global $wpdb;
		if ( ! is_array( $emails_or_phones ) || empty( $emails_or_phones ) ) {
			return false;
		}

		$placeholders = [];
		$where_args   = [];
		foreach ( $emails_or_phones as $recipient ) {
			if ( empty( $recipient ) ) {
				continue;
			}

			$placeholders[] = "%s";
			$where_args[]   = $recipient;
		}

		$placeholders = implode( ',', $placeholders );
		$table        = self::_table();
		$sql          = $wpdb->prepare( "DELETE FROM $table WHERE `recipient` IN ($placeholders)", $where_args );
		$wpdb->query( $sql );

		return true;
	}

	public static function add_unsubscribers( $emails_or_phones = [], $object_id = 0, $object_type = 3 ) {
		$unsubscribers = array_map( function ( $recipient ) use ( $object_id, $object_type ) {
			return array(
				'recipient'     => $recipient,
				'mode'          => is_email( $recipient ) ? 1 : 2,
				'c_date'        => current_time( 'mysql', 1 ),
				'automation_id' => ! empty( $object_id ) ? absint( $object_id ) : 0,
				'c_type'        => ! empty( $object_type ) ? absint( $object_type ) : 3
			);
		}, $emails_or_phones );

		BWFAN_Model_Message_Unsubscribe::insert_multiple( $unsubscribers, array( 'recipient', 'mode', 'c_date', 'automation_id', 'c_type' ), [ '%s', '%d', '%s', '%d', '%d' ] );

		return true;
	}
}
