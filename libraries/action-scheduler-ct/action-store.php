<?php

class BWFAN_AS_CT_Action_Store extends ActionScheduler_Store {

	public function init() {
	}

	public function save_action( ActionScheduler_Action $action, DateTime $date = null ) {
		/** Not scheduling any new action while processing our requests */
	}

	public function fetch_action( $action_id ) {
		$this->log( __FUNCTION__ );
		$this->log( 'running task id: ' . $action_id );

		global $wpdb;

		$cache_key = 'fetch_action_' . $action_id;
		$data      = wp_cache_get( $cache_key, __FUNCTION__ );

		if ( false === $data ) {
			$query = $wpdb->prepare( "SELECT a.*, g.event AS `group` FROM {$wpdb->bwfan_tasks} a LEFT JOIN {$wpdb->bwfan_automations} g ON a.automation_id=g.ID WHERE a.ID=%d", $action_id );
			$data  = $wpdb->get_row( $query ); // WPCS: unprepared SQL OK
			wp_cache_set( $cache_key, $data, __FUNCTION__, ( 60 ) );
		}

		if ( empty( $data ) ) {
			return $this->get_null_action();
		}

		/** Added manually as we are not inserting the data using AS */
		$data->args = '[' . $data->ID . ']';
		$data->hook = 'bwfan_execute_task';

		return $this->make_action_from_db_record( $data );
	}

	protected function log( $message ) {
		BWFAN_Core()->logger->log( $message, 'as-data-store' );
	}

	protected function get_null_action() {
		return new ActionScheduler_NullAction();
	}

	protected function make_action_from_db_record( $data ) {
		$hook = $data->hook;
		$args = json_decode( $data->args, true );

		/** creating fresh schedule */
		$schedule = new ActionScheduler_NullSchedule();
		$group    = $data->group ? $data->group : '';
		if ( $this->verify_status( $data->status ) ) {
			$action = new ActionScheduler_Action( $hook, $args, $schedule, $group );
		} else {
			/** status not 0 - finishing AS action (status won't occur as we are fetching 0 status tasks only) */
			$action = new ActionScheduler_FinishedAction( $hook, $args, $schedule, $group );
		}
		$this->log( 'action class name ' . get_class( $action ) );

		return $action;
	}

	protected function verify_status( $status ) {
		return ( 0 === intval( $status ) ) ? true : false;
	}

	/**
	 * @param string $hook
	 * @param array $params
	 *
	 * @return string
	 */
	public function find_action( $hook, $params = [] ) {
		/** This is invoked during unscheduled or next schedule, we are not doing anything, so blank */

		return '';
	}

	/**
	 * @param array $query
	 * @param string $query_type Whether to select or count the results. Default, select.
	 *
	 * @return null|string|array The IDs of actions matching the query
	 */
	public function query_actions( $query = [], $query_type = 'select' ) {
		global $wpdb;

		/** cleanup call handling */
		if ( isset( $query['status'] ) && in_array( $query['status'], array( 'complete', 'canceled', 'in-progress' ), true ) ) {
			return array();
		}

		if ( 'pending' === $query['status'] ) {
			$query['status'] = '0';
		}

		/** Code is not going to this level as when clean function ran we get only top 4 statuses */
		$sql = $this->get_query_actions_sql( $query, $query_type );

		return ( 'count' === $query_type ) ? $wpdb->get_var( $sql ) : $wpdb->get_col( $sql ); // WPCS: unprepared SQL OK
	}

	/**
	 * Returns the SQL statement to query (or count) actions.
	 *
	 * @param array $query Filtering options
	 * @param string $select_or_count Whether the SQL should select and return the IDs or just the row count
	 *
	 * @return string SQL statement. The returned SQL is already properly escaped.
	 */
	protected function get_query_actions_sql( array $query, $select_or_count = 'select' ) {
		if ( ! in_array( $select_or_count, array( 'select', 'count' ), true ) ) {
			throw new InvalidArgumentException( __( 'Invalid value for select or count parameter. Cannot query actions.', 'action-scheduler' ) );
		}

		$query = wp_parse_args( $query, [
			'hook'             => '',
			'args'             => null,
			'date'             => null,
			'date_compare'     => '<=',
			'modified'         => null,
			'modified_compare' => '<=',
			'group'            => '',
			'status'           => '0',
			'claimed'          => null,
			'per_page'         => 5,
			'offset'           => 0,
			'orderby'          => 'date',
			'order'            => 'ASC',
		] );

		global $wpdb;
		$sql        = ( 'count' === $select_or_count ) ? 'SELECT count(a.ID)' : 'SELECT a.ID ';
		$sql        .= "FROM {$wpdb->bwfan_tasks} a";
		$sql_params = [];

		/** Ignoring group here */

		$sql .= ' WHERE 1=1';

		if ( '' !== $query['status'] ) {
			$sql          .= ' AND a.status=%s';
			$sql_params[] = $query['status'];
		}

		if ( $query['date'] instanceof DateTime ) {
			$date = clone $query['date'];
			$date->setTimezone( new DateTimeZone( 'UTC' ) );
			$date_string  = $date->getTimestamp();
			$comparator   = $this->validate_sql_comparator( $query['date_compare'] );
			$sql          .= " AND a.e_date $comparator %d";
			$sql_params[] = $date_string;
		} elseif ( $query['modified'] instanceof DateTime ) {
			$date = clone $query['modified'];
			$date->setTimezone( new DateTimeZone( 'UTC' ) );
			$date_string  = $date->getTimestamp();
			$comparator   = $this->validate_sql_comparator( $query['modified_compare'] );
			$sql          .= " AND a.e_date $comparator %d";
			$sql_params[] = $date_string;
		}

		if ( true === $query['claimed'] ) {
			$sql .= ' AND a.claim_id != 0';
		} elseif ( false === $query['claimed'] ) {
			$sql .= ' AND a.claim_id = 0';
		} elseif ( ! is_null( $query['claimed'] ) ) {
			$sql          .= ' AND a.claim_id = %d';
			$sql_params[] = $query['claimed'];
		}

		if ( 'select' === $select_or_count ) {
			switch ( $query['orderby'] ) {
				case 'date':
				default:
					$orderby = 'a.e_date';
					break;
			}
			if ( strtoupper( $query['order'] ) === 'ASC' ) {
				$order = 'ASC';
			} else {
				$order = 'DESC';
			}
			$sql .= " ORDER BY $orderby $order";
			if ( $query['per_page'] > 0 ) {
				$sql          .= ' LIMIT %d, %d';
				$sql_params[] = $query['offset'];
				$sql_params[] = $query['per_page'];
			}
		}

		if ( ! empty( $sql_params ) ) {
			$sql = $wpdb->prepare( $sql, $sql_params ); // WPCS: unprepared SQL OK
		}

		return $sql;
	}

	/**
	 * Get a count of all actions in the store, grouped by status
	 * Not in use we are not showing counts of status on a listing page
	 *
	 * @return array Set of 'status' => int $count
	 */
	public function action_counts() {
		$this->log( __FUNCTION__ );

		return [];
	}

	/**
	 * @param string $action_id
	 *
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function cancel_action( $action_id ) {
		$this->log( __FUNCTION__ );
	}

	/**
	 * @param string $action_id
	 */
	public function delete_action( $action_id ) {
		$this->log( __FUNCTION__ );
	}

	/**
	 * don't know where using
	 *
	 * @param string $action_id
	 *
	 * @return DateTime The local date the action is scheduled to run, or the date that it ran.
	 * @throws InvalidArgumentException
	 */
	public function get_date( $action_id ) {
		$date = $this->get_date_gmt( $action_id );
		ActionScheduler_TimezoneHelper::set_local_timezone( $date );

		return $date;
	}

	/**
	 * modified by a
	 *
	 * @param string $action_id
	 *
	 * @return DateTime The GMT date the action is scheduled to run, or the date that it ran.
	 * @throws InvalidArgumentException
	 */
	protected function get_date_gmt( $action_id ) {

		global $wpdb;
		$record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->bwfan_tasks} WHERE ID=%d", $action_id ) );
		if ( empty( $record ) ) {
			throw new InvalidArgumentException( sprintf( __( 'Unidentified action %s', 'action-scheduler' ), $action_id ) );
		}
		if ( $this->verify_status( $record->status ) ) {
			return as_get_datetime_object( $record->e_date );
		}
	}

	/**
	 * @param int $max_actions
	 * @param DateTime $before_date Jobs must be schedule before this date. Defaults to now.
	 *
	 * @return ActionScheduler_ActionClaim
	 */
	public function stake_claim( $max_actions = 10, DateTime $before_date = null, $hooks = array(), $group = '' ) {
		$this->log( __FUNCTION__ );
		$claim_id = $this->generate_claim_id();
		$this->claim_actions( $claim_id, $max_actions, $before_date, $hooks, $group );
		$action_ids = $this->find_actions_by_claim_id( $claim_id );

		return new ActionScheduler_ActionClaim( $claim_id, $action_ids );
	}

	/**
	 * Generating claim id - current date time
	 * modified by a
	 * @return int
	 */
	protected function generate_claim_id() {

		global $wpdb;
		$now = as_get_datetime_object();
		$wpdb->insert( $wpdb->bwfan_task_claim, [
			'date_created_gmt' => $now->format( 'Y-m-d H:i:s' ),
		] );

		return $wpdb->insert_id;
	}

	/**
	 *
	 * @param string $claim_id
	 * @param int $limit
	 * @param DateTime $before_date Should use UTC timezone.
	 *
	 * @return int The number of actions that were claimed
	 * @throws RuntimeException
	 * @todo there we need to add group sorting
	 *
	 */
	protected function claim_actions( $claim_id, $limit, DateTime $before_date = null, $hooks = array(), $group = '' ) {
		global $wpdb;

		/** can't use $wpdb->update() because of the <= condition */
		$update = "SELECT t.`ID` FROM {$wpdb->bwfan_tasks} AS t INNER JOIN {$wpdb->bwfan_automations} AS aut ON t.`automation_id` = aut.`ID`";
		$params = [];

		$where    = 'WHERE t.`claim_id` = 0 AND t.`e_date` <= %s AND t.`status` = 0 AND aut.`status` = 1';
		$params[] = time();

		$order    = 'ORDER BY t.`e_date` ASC, t.`priority` DESC LIMIT %d';
		$params[] = $limit;

		$sql = $wpdb->prepare( "{$update} {$where} {$order}", $params ); //phpcs:ignore WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders

		$task_ids = $wpdb->get_results( $sql, ARRAY_A ); // WPCS: unprepared SQL OK

		if ( ! is_array( $task_ids ) || count( $task_ids ) === 0 ) {
			return 0;
		}

		$task_ids = array_column( $task_ids, 'ID' );

		/** Update call */
		$type   = array_fill( 0, count( $task_ids ), '%d' );
		$format = implode( ', ', $type );
		$query  = "UPDATE {$wpdb->bwfan_tasks} SET `claim_id` = %d WHERE `ID` IN ({$format})";
		$params = array( $claim_id );
		$params = array_merge( $params, $task_ids );
		$sql    = $wpdb->prepare( $query, $params ); // WPCS: unprepared SQL OK

		$rows_affected = $wpdb->query( $sql ); // WPCS: unprepared SQL OK
		if ( false === $rows_affected ) {
			throw new RuntimeException( __( 'Unable to claim actions. Database error.', 'action-scheduler' ) );
		}

		return (int) $rows_affected;
	}

	/**
	 * Get Actions against a claim_id
	 *
	 * @param string $claim_id
	 *
	 * @return Array
	 */
	public function find_actions_by_claim_id( $claim_id ) {
		$this->log( __FUNCTION__ . ' ' . $claim_id );

		global $wpdb;

		$cache_key = 'action_id_for_claim_id_' . $claim_id;

		$cache_available = wp_cache_get( $cache_key, __FUNCTION__ );
		if ( false !== $cache_available ) {
			return $cache_available;
		}

		$sql = "SELECT ID FROM {$wpdb->bwfan_tasks} WHERE claim_id=%d ORDER BY e_date ASC, priority DESC";
		$sql = $wpdb->prepare( $sql, $claim_id ); // WPCS: unprepared SQL OK

		$action_ids = $wpdb->get_col( $sql ); // WPCS: unprepared SQL OK

		$return = array_map( 'intval', $action_ids );

		$this->log( 'found ' . count( $return ) . ' tasks (' . implode( ',', $return ) . ') against claim id ' . $claim_id );

		wp_cache_set( $cache_key, $return, __FUNCTION__, ( HOUR_IN_SECONDS / 4 ) );

		return $return;
	}

	/**
	 * Return unique claim id counts
	 *
	 * @return int
	 */
	public function get_claim_count() {
		$this->log( __FUNCTION__ );
		global $wpdb;

		/** passing status 0 as those tasks needs to execute */
		$sql = "SELECT COUNT(DISTINCT claim_id) FROM {$wpdb->bwfan_tasks} WHERE claim_id != 0 AND status = 0";

		return (int) $wpdb->get_var( $sql ); // WPCS: unprepared SQL OK
	}

	/**
	 * Return an action's claim ID, as stored in the claim_id column
	 *
	 * @param string $action_id
	 *
	 * @return mixed
	 */
	public function get_claim_id( $action_id ) {
		$this->log( __FUNCTION__ );

		global $wpdb;

		$sql = "SELECT claim_id FROM {$wpdb->bwfan_tasks} WHERE ID=%d";
		$sql = $wpdb->prepare( $sql, $action_id ); // WPCS: unprepared SQL OK

		return (int) $wpdb->get_var( $sql ); // WPCS: unprepared SQL OK
	}

	/**
	 * Releasing the claim
	 *
	 * @param ActionScheduler_ActionClaim $claim
	 */
	public function release_claim( ActionScheduler_ActionClaim $claim ) {
		$this->log( __FUNCTION__ . ' ' . $claim->get_id() );

		global $wpdb;
		$wpdb->update( $wpdb->bwfan_tasks, [
			'claim_id' => 0,
		], [
			'claim_id' => $claim->get_id(),
		], [ '%d' ], [ '%d' ] );

		$wpdb->delete( $wpdb->bwfan_task_claim, [
			'claim_id' => $claim->get_id(),
		], [ '%d' ] );
	}

	/**
	 * Unclaiming actions
	 *
	 * @param string $action_id
	 *
	 * @return void
	 */
	public function unclaim_action( $action_id ) {
		$this->log( __FUNCTION__ );

		global $wpdb;
		$wpdb->update( $wpdb->bwfan_tasks, [
			'claim_id' => 0,
		], [
			'ID' => $action_id,
		], [ '%s' ], [ '%d' ] );
	}

	/**
	 * Run when a task is failed
	 *
	 * @param string $task_id
	 */
	public function mark_failure( $task_id ) {
		$this->log( __FUNCTION__ );

		/** Increasing attempt count */
		$task_details  = BWFAN_Model_Tasks::get_task_with_data( $task_id );
		$attempt_count = ( intval( $task_details['attempts'] ) > 0 ) ? intval( $task_details['attempts'] ) : 0;

		$data  = array(
			'e_date'   => time() + 30,
			'attempts' => ( $attempt_count + 1 ),
		);
		$where = array(
			'ID' => $task_id,
		);
		BWFAN_Model_Tasks::update( $data, $where );

		/** Update failure rough message */
		$task_log_message = [];
		if ( isset( $task_details['meta']['task_message'] ) && ! empty( $task_details['meta']['task_message'] ) ) {
			$task_log_message = $task_details['meta']['task_message'];
		}
		$new_attempt_count = BWFAN_Common::add_ordinal_number_suffix( $attempt_count + 1 );
		$new_log           = sprintf( __( '%s attempt failed.' ), $new_attempt_count );
		BWFAN_Core()->tasks->update_task_logs( $task_id, $task_log_message, $new_log );
	}

	/**
	 * @param string $action_id
	 *
	 * @return void
	 */
	public function log_execution( $action_id ) {
		/** no need to log as we are managing logs differently, even attempts */
	}

	/**
	 * @param string $action_id
	 */
	public function mark_complete( $action_id ) {
		/** no need to mark anything complete */
	}

	public function get_status( $action_id ) {
		$this->log( __FUNCTION__ );

		global $wpdb;
		$sql    = "SELECT status FROM {$wpdb->bwfan_tasks} WHERE ID=%d";
		$sql    = $wpdb->prepare( $sql, $action_id ); // WPCS: unprepared SQL OK
		$status = $wpdb->get_var( $sql ); // WPCS: unprepared SQL OK
		if ( $status === null ) {
			throw new InvalidArgumentException( __( 'Invalid action ID. No status found.', 'action-scheduler' ) );
		} else {
			return $this->get_as_defined_status_val( $status );
		}
	}

	protected function get_as_defined_status_val( $status ) {
		switch ( $status ) {
			case '0':
				return 'pending';
		}

		return $status;
	}

	/**
	 * Cancel pending actions by hook.
	 *
	 * @param string $hook
	 *
	 * @since 3.0.0 Action Scheduler and 1.0.8 Autonami
	 */
	public function cancel_actions_by_hook( $hook ) {
		return;
	}

	/**
	 * Cancel pending actions by group.
	 *
	 * @param string $group
	 *
	 * @since 3.0.0 Action Scheduler and 1.0.8 Autonami
	 */
	public function cancel_actions_by_group( $group ) {
		return;
	}

}
