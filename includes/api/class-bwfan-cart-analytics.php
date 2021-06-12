<?php

class BWFAN_Cart_Analytics {

	private static $ins = null;

	private $filter_date = 7;
	private $no_of_days = 7;
	private $date_rage_search = false;
	private $start_date = '';
	private $end_date = '';
	public static $sql_datetime_format = 'Y-m-d H:i:s';

	private function __construct() {
		$this->end_date   = date( 'Y-m-d', strtotime( "+1 days" ) );
		$this->start_date = date( 'Y-m-d', strtotime( "-{$this->filter_date} days" ) );

	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public static function get_captured_cart( $start_date, $end_date, $interval, $is_interval ) {
		global $wpdb;
		$table          = $wpdb->prefix . 'bwfan_abandonedcarts';
		$date_col       = "last_modified";
		$interval_query = '';
		$group_by       = '';
		$order_by       = ' ID ';

		if ( 'interval' === $is_interval ) {
			$get_interval   = self::get_interval_format_query( $interval, $date_col );
			$interval_query = $get_interval['interval_query'];
			$interval_group = $get_interval['interval_group'];
			$group_by       = "GROUP BY " . $interval_group;
			$order_by       = ' time_interval ';
		}

		$base_query = "SELECT  SUM(total_base) as sum, COUNT(ID) as count " . $interval_query . "  FROM `" . $table . "` WHERE 1=1 AND status != 2  AND `" . $date_col . "` >= '" . $start_date . "' AND `" . $date_col . "` <= '" . $end_date . "'" . $group_by . " ORDER BY " . $order_by . " ASC";
		$data       = $wpdb->get_results( $base_query, ARRAY_A );

		return $data;
	}

	public static function get_lost_cart( $start_date, $end_date, $interval, $is_interval ) {
		global $wpdb;
		$table          = $wpdb->prefix . 'bwfan_abandonedcarts';
		$date_col       = "last_modified";
		$interval_query = '';
		$group_by       = '';
		$order_by       = ' ID ';

		if ( 'interval' === $is_interval ) {
			$get_interval   = self::get_interval_format_query( $interval, $date_col );
			$interval_query = $get_interval['interval_query'];
			$interval_group = $get_interval['interval_group'];
			$group_by       = "GROUP BY " . $interval_group;
			$order_by       = ' time_interval ';
		}

		$base_query = "SELECT  SUM(total_base) as sum, COUNT(ID) as count " . $interval_query . "  FROM " . $table . " WHERE 1=1 AND status = 2  AND " . $date_col . " >= '" . $start_date . "' AND " . $date_col . " <= '" . $end_date . "'" . $group_by . " ORDER BY " . $order_by . " ASC";
		$results    = $wpdb->get_results( $base_query, ARRAY_A );

		return $results;
	}

	private function get_default_data() {
		$dataset     = [];
		$labels      = [];
		$timestamp   = strtotime( $this->end_date );
		$no_of_loops = absint( $this->filter_date );

		for ( $i = $no_of_loops; $i >= 0; $i -- ) {
			$date             = date( 'Y-m-d', strtotime( "-$i days", $timestamp ) );
			$labels[]         = $date;
			$dataset[ $date ] = 0;
		}

		return [ $labels, $dataset ];
	}

	public static function get_recovered_cart( $start_date, $end_date, $interval, $is_interval ) {
		global $wpdb;

		$post_statuses = apply_filters( 'bwfan_recovered_cart_excluded_statuses', array( 'wc-pending', 'wc-failed', 'wc-cancelled', 'wc-refunded', 'trash', 'draft' ) );
		$post_status   = '(';
		foreach ( $post_statuses as $status ) {
			$post_status .= "'" . $status . "',";
		}
		$post_status .= "'')";

		$date_col       = "p.post_date";
		$interval_query = '';
		$group_by       = '';
		$order_by       = ' p.ID ';
		if ( 'interval' === $is_interval ) {
			$get_interval   = self::get_interval_format_query( $interval, $date_col );
			$interval_query = $get_interval['interval_query'];
			$interval_group = $get_interval['interval_group'];
			$group_by       = "GROUP BY " . $interval_group;
			$order_by       = ' time_interval ';
		}

//		$base_query = "SELECT COUNT(p.ID) as order_placed_count" . $interval_query . " FROM {$wpdb->prefix}posts as p LEFT JOIN {$wpdb->prefix}postmeta as m ON p.ID = m.post_id WHERE 1=1 AND p.post_type = 'shop_order' AND p.post_status NOT IN $post_status AND " . $date_col . " >= '" . $start_date . "' AND " . $date_col . " <= '" . $end_date . "' AND m.meta_key = '_bwfan_order_total_base' " . $group_by . " ORDER BY " . $order_by . " ASC";

		$where = "AND p.post_date >= '{$start_date}' AND p.post_date <='{$end_date}'";
		$where .= " AND m2.meta_value > 0";

		$query  = " SELECT COUNT(p.ID) as count, sum(m.meta_value) as sum " . $interval_query . " FROM {$wpdb->prefix}posts as p LEFT JOIN {$wpdb->prefix}postmeta as m ON p.ID = m.post_id LEFT JOIN {$wpdb->prefix}postmeta as m2 ON p.ID = m2.post_id WHERE p.post_type = 'shop_order' AND p.post_status NOT IN $post_status AND m.meta_key = '_bwfan_order_total_base' AND m2.meta_key = '_bwfan_ab_cart_recovered_a_id' $where " . $group_by . " ORDER BY " . $order_by . " ASC";

		$result = $wpdb->get_results( $query, ARRAY_A );

//		$results     = $wpdb->get_results( $wpdb->prepare( " SELECT p.post_date as date, m.meta_value as total FROM {$wpdb->prefix}posts as p LEFT JOIN {$wpdb->prefix}postmeta as m ON p.ID = m.post_id LEFT JOIN {$wpdb->prefix}postmeta as m2 ON p.ID = m2.post_id WHERE p.post_type = 'shop_order' AND p.post_status NOT IN $post_status AND m.meta_key = '_bwfan_order_total_base' AND m2.meta_key = '_bwfan_ab_cart_recovered_a_id' $where ", 'shop_order', '_bwfan_order_total_base', '_bwfan_ab_cart_recovered_a_id' ) ); //phpcs:ignore WordPress.DB.PreparedSQL

		return $result;
	}

	public static function get_recovery_rate( $total_abandoned, $total_recovered ) {
		$total_abandoned = intval( $total_abandoned );
		$total_recovered = intval( $total_recovered );
		$recovery_rate   = 0;

		if ( 0 === $total_recovered ) {
			return 0;
		}

		$total_abandoned += $total_recovered;
		$recovery_rate   = number_format( ( $total_recovered / $total_abandoned ) * 100, 2 );

		return $recovery_rate;
	}

	public function line_chart_data( $captured_cart ) {
		$default_data = $this->get_default_data();
		$dataset      = $default_data[1];
		$revenue_set  = $default_data[1];

		if ( count( $captured_cart['data'] ) > 0 ) {
			$data = $captured_cart['data'];

			foreach ( $data as $item ) {
				$create_time  = $item->last_modified;
				$timestamp    = strtotime( $create_time );
				$created_time = date( 'Y-m-d', $timestamp );

				if ( ! isset( $dataset[ $created_time ] ) ) {
					$dataset[ $created_time ]     = 1;
					$revenue_set[ $created_time ] = $item->total_base;
				} else {
					$dataset[ $created_time ] ++;
					$revenue_set[ $created_time ] += $item->total_base;
				}

				$revenue_set[ $created_time ] = round( $revenue_set[ $created_time ], wc_get_price_decimals() );
			}
		}

		return [
			'labels'  => array_values( $default_data[0] ),
			'data'    => array_values( $dataset ),
			'revenue' => array_values( $revenue_set ),
		];
	}

	/**
	 * Total carts - wc session count - total no of carts made
	 *
	 * @return array
	 */
	public static function get_total_cart_generated( $start_date, $end_date, $interval, $is_interval ) {
		global $wpdb;
		$table          = $wpdb->prefix . 'wfco_report_views';
		$date_col       = "date";
		$interval_query = '';
		$group_by       = '';
		$order_by       = ' id ';

		if ( 'interval' === $is_interval ) {
			$get_interval   = self::get_interval_format_query( $interval, $date_col );
			$interval_query = $get_interval['interval_query'];
			$interval_group = $get_interval['interval_group'];
			$group_by       = "GROUP BY " . $interval_group;
			$order_by       = ' time_interval ';
		}

		$base_query = "SELECT  SUM(no_of_sessions) as total_session " . $interval_query . "  FROM `" . $table . "` WHERE 1=1 AND `" . $date_col . "` >= '" . $start_date . "' AND `" . $date_col . "` <= '" . $end_date . "' and type = 1 and object_id = 0 " . $group_by . " ORDER BY " . $order_by . " ASC";

		$data = $wpdb->get_results( $base_query, ARRAY_A );

		return $data;
	}

	/**
	 * Total orders placed in a particular time period
	 *
	 * @return array
	 */
	public static function get_total_orders_placed( $start_date, $end_date, $interval, $is_interval ) {
		global $wpdb;

		$post_statuses = apply_filters( 'bwfan_recovered_cart_excluded_statuses', array( 'wc-pending', 'wc-failed', 'wc-cancelled' ) );
		$count         = count( $post_statuses );
		$i             = 0;
		$post_status   = '(';
		foreach ( $post_statuses as $status ) {
			$i ++;
			if ( $i !== $count ) {
				$post_status .= "'" . $status . "',";
			} else {
				$post_status .= "'" . $status . "'";
			}
		}
		$post_status .= ')';

		$date_col       = "p.post_date";
		$interval_query = '';
		$group_by       = '';
		$order_by       = ' p.ID ';
		if ( 'interval' === $is_interval ) {
			$get_interval   = self::get_interval_format_query( $interval, $date_col );
			$interval_query = $get_interval['interval_query'];
			$interval_group = $get_interval['interval_group'];
			$group_by       = "GROUP BY " . $interval_group;
			$order_by       = ' time_interval ';
		}

		$base_query = "SELECT COUNT(p.ID) as order_placed_count" . $interval_query . " FROM {$wpdb->prefix}posts as p LEFT JOIN {$wpdb->prefix}postmeta as m ON p.ID = m.post_id WHERE 1=1 AND p.post_type = 'shop_order' AND p.post_status NOT IN $post_status AND " . $date_col . " >= '" . $start_date . "' AND " . $date_col . " <= '" . $end_date . "' AND m.meta_key = '_bwfan_order_total_base' " . $group_by . " ORDER BY " . $order_by . " ASC";
		$orders     = $wpdb->get_results( $base_query, ARRAY_A );

		return $orders;
	}

	/**
	 * @param $interval
	 * @param $table_col
	 *
	 * @return array
	 */
	public static function get_interval_format_query( $interval, $table_col ) {
		$interval_type = self::date_format( $interval );
		$avg           = ( $interval === 'day' ) ? 1 : 0;
		if ( 'YEAR' === $interval_type ) {
			$interval = ", YEAR(" . $table_col . ") ";
			$avg      = 365;
		} elseif ( 'QUARTER' === $interval_type ) {
			$interval = ", CONCAT(YEAR(" . $table_col . "), '-', QUARTER(" . $table_col . ")) ";
			$avg      = 90;
		} elseif ( '%x-%v' === $interval_type ) {
			$first_day_of_week = absint( get_option( 'start_of_week' ) );

			if ( 1 === $first_day_of_week ) {
				$interval = ", DATE_FORMAT(" . $table_col . ", '" . $interval_type . "')";
			} else {
				$interval = ", CONCAT(YEAR(" . $table_col . "), '-', LPAD( FLOOR( ( DAYOFYEAR(" . $table_col . ") + ( ( DATE_FORMAT(MAKEDATE(YEAR(" . $table_col . "),1), '%w') - $first_day_of_week + 7 ) % 7 ) - 1 ) / 7  ) + 1 , 2, '0'))";
			}
			$avg = 7;
		} else {
			$interval = ", DATE_FORMAT( " . $table_col . ", '" . $interval_type . "')";
		}

		$interval       .= " as time_interval ";
		$interval_group = " `time_interval` ";

		return array(
			'interval_query' => $interval,
			'interval_group' => $interval_group,
			'interval_avg'   => $avg,

		);
	}

	/**
	 * @param $interval
	 *
	 * @return mixed|void
	 */
	public static function date_format( $interval ) {
		switch ( $interval ) {
			case 'hour':
				$format = '%Y-%m-%d %H';
				break;
			case 'day':
				$format = '%Y-%m-%d';
				break;
			case 'month':
				$format = '%Y-%m';
				break;
			case 'quarter':
				$format = 'QUARTER';
				break;
			case 'year':
				$format = 'YEAR';
				break;
			default:
				$format = '%x-%v';
				break;
		}

		return apply_filters( 'bwfan_api_date_format_' . $interval, $format, $interval );
	}


	/**
	 * @param $start
	 * @param $end
	 * @param $interval
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function intervals_between( $start, $end, $interval ) {

		switch ( $interval ) {
			case 'hour':
				$interval_type = 'PT60M';
				$format        = 'Y-m-d H';
				break;
			case 'day':
				$interval_type = "P1D";
				$format        = 'Y-m-d';
				break;
			case 'month':
				$interval_type = "P1M";
				$format        = 'Y-m';
				break;
			case 'quarter':
				$interval_type = "P3M";
				$format        = 'Y-m';
				break;
			case 'year':
				$interval_type = "P1Y";
				$format        = 'Y';
				break;
			default:
				$interval_type = "P1W";
				$format        = 'W';
				break;
		}
		$result = array();

		// Variable that store the date interval
		// of period 1 day
		$period = new DateInterval( $interval_type );

		$realEnd = new DateTime( $end );

		$realEnd->add( $period );

		$period   = new DatePeriod( new DateTime( $start ), $period, $realEnd );
		$date_end = date_create( $end );
		$count    = iterator_count( $period );

		if ( 'week' !== $interval && 'day' !== $interval ) {
			$count = $count - 1;
		}

		foreach ( $period as $date ) {
			if ( $count >= 1 ) {
				$new_interval = array();

				if ( 'day' === $interval && $date_end->format( 'Y-m-d' ) < $date->format( 'Y-m-d' ) ) {
					$count --;
					continue;
				}

				if ( 'day' === $interval || 'hour' === $interval ) {
					$new_interval['start_date'] = $date->format( self::$sql_datetime_format );
					$new_interval['end_date']   = $date->format( 'Y-m-d 23:59:59' );
				} else {
					$new_interval['start_date'] = self::maybe_first_date( $date, $format );
					$new_interval['end_date']   = ( $count > 1 ) ? self::maybe_last_date( $date, $format ) : $date_end->format( self::$sql_datetime_format );
				}
				if ( 'week' === $interval ) {
					$year                          = $date->format( 'Y' );
					$new_interval['time_interval'] = $year . '-' . $date->format( $format );
				} else if ( 'quarter' === $interval ) {
					$year                          = $date->format( 'Y' );
					$month                         = $date->format( 'm' );
					$yearQuarter                   = ceil( $month / 3 );
					$new_interval['time_interval'] = $year . '-' . $yearQuarter;
				} else {
					$new_interval['time_interval'] = $date->format( $format );
				}

				$result[] = $new_interval;
			}
			$count --;

		}

		return $result;
	}

	/**
	 * @param $all_data
	 * @param $interval_key
	 * @param $current_interval
	 *
	 * @return array|false
	 */
	public static function maybe_interval_exists( $all_data, $interval_key, $current_interval ) {
		if ( is_array( $all_data ) && count( $all_data ) > 0 ) {

			foreach ( $all_data as $data ) {
				if ( isset( $data[ $interval_key ] ) && $current_interval == $data[ $interval_key ] ) {
					return array( $data );
				}
			}
		}

		return false;
	}

	/**
	 * @param $datetime_string
	 *
	 * @return DateTime
	 * @throws Exception
	 */
	public static function convert_local_datetime_to_gmt( $datetime_string ) {
		$datetime = new DateTime( $datetime_string, new \DateTimeZone( wp_timezone_string() ) );
		$datetime->setTimezone( new DateTimeZone( 'GMT' ) );

		return $datetime;
	}

	/**
	 * @param $newDate
	 * @param $period
	 *
	 * @return mixed
	 */
	public static function maybe_first_date( $newDate, $period ) {
		switch ( $period ) {
			case 'Y':
				$newDate->modify( 'first day of january ' . $newDate->format( 'Y' ) );
				break;
			case 'quarter':
				$month = $newDate->format( 'n' );
				if ( $month < 4 ) {
					$newDate->modify( 'first day of january ' . $newDate->format( 'Y' ) );
				} elseif ( $month > 3 && $month < 7 ) {
					$newDate->modify( 'first day of april ' . $newDate->format( 'Y' ) );
				} elseif ( $month > 6 && $month < 10 ) {
					$newDate->modify( 'first day of july ' . $newDate->format( 'Y' ) );
				} elseif ( $month > 9 ) {
					$newDate->modify( 'first day of october ' . $newDate->format( 'Y' ) );
				}
				break;
			case 'Y-m':
				$newDate->modify( 'first day of this month' );
				break;
			case 'W':
				$newDate->modify( ( $newDate->format( 'w' ) === '0' ) ? self::first_day_of_week() . ' last week' : self::first_day_of_week() . ' this week' );
				break;
		}

		return $newDate->format( self::$sql_datetime_format );

	}

	/**
	 * @param $newDate
	 * @param $period
	 *
	 * @return mixed
	 */
	public static function maybe_last_date( $newDate, $period ) {
		switch ( $period ) {
			case 'Y':
				$newDate->modify( 'last day of december ' . $newDate->format( 'Y' ) );
				break;
			case 'quarter':
				$month = $newDate->format( 'n' );

				if ( $month < 4 ) {
					$newDate->modify( 'last day of march ' . $newDate->format( 'Y' ) );
				} elseif ( $month > 3 && $month < 7 ) {
					$newDate->modify( 'last day of june ' . $newDate->format( 'Y' ) );
				} elseif ( $month > 6 && $month < 10 ) {
					$newDate->modify( 'last day of september ' . $newDate->format( 'Y' ) );
				} elseif ( $month > 9 ) {
					$newDate->modify( 'last day of december ' . $newDate->format( 'Y' ) );
				}
				break;
			case 'Y-m':
				$newDate->modify( 'last day of this month' );
				break;
			case 'W':
				$newDate->modify( ( $newDate->format( 'w' ) === '0' ) ? 'now' : self::last_day_of_week() . ' this week' );
				break;
		}

		return $newDate->format( 'Y-m-d 23:59:59 ' );

	}

	/**
	 * @return string
	 */
	public static function first_day_of_week() {
		$days_of_week = array(
			1 => 'monday',
			2 => 'tuesday',
			3 => 'wednesday',
			4 => 'thursday',
			5 => 'friday',
			6 => 'saturday',
			7 => 'sunday',
		);

		$day_number = absint( get_option( 'start_of_week' ) );

		return $days_of_week[ $day_number ];
	}

	/**
	 * @return string
	 */
	public static function last_day_of_week() {
		$days_of_week = array(
			1 => 'sunday',
			2 => 'saturday',
			3 => 'friday',
			4 => 'thursday',
			5 => 'wednesday',
			6 => 'tuesday',
			7 => 'monday',
		);

		$day_number = absint( get_option( 'start_of_week' ) );

		return $days_of_week[ $day_number ];
	}

	/**
	 * @param int $diff_time
	 *
	 * @return DateTime
	 */
	public static function default_date( $diff_time = 0 ) {
		$now      = time();
		$datetime = new DateTime();
		if ( $diff_time > 0 ) {
			$week_back = $now - $diff_time;
			$datetime->setTimestamp( $week_back );
		}
		$datetime->setTimezone( new DateTimeZone( wp_timezone_string() ) );

		return $datetime;
	}

}
