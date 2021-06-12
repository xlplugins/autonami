<?php

class BWF_AS_Action_Store extends ActionScheduler_Store {
	public $bwf_action_data = [];
	public $action_table = '';
	public $claim_table = '';
	public $p_key = '';

	public function init() {
		global $wpdb;
		$this->action_table = BWF_AS_Actions_Crud::_table();
		$this->claim_table  = $wpdb->bwf_action_claim;
		$this->p_key        = BWF_AS_Actions_Crud::$primary_key;
	}

	public function save_action( ActionScheduler_Action $action, DateTime $date = null ) {
		/** Not scheduling any new action while processing our requests */
	}

	public function fetch_action( $action_id ) {
		global $wpdb;

		if ( empty( $action_id ) ) {
			return $this->get_null_action();
		}

		$this->log( 'fetch running action id: ' . $action_id );

		/** Changing status to running i.e. 1 on action */
		$wpdb->update( $this->action_table, [ 'status' => 1 ], [ $this->p_key => $action_id ], [ '%s' ], [ '%d' ] );

		$data = $this->get_action_data( $action_id );

		if ( empty( $data ) ) {
			return $this->get_null_action();
		}

		/** Scheduling recurring action if possible */
		$this->schedule_recurring_action( $action_id );

		/** Fetching action data again as status may be altered */
		$data = $this->get_action_data( $action_id );

		/** If hook not present, return null action */
		if ( empty( $data->hook ) ) {
			return $this->get_null_action();
		}

		return $this->make_action_from_db_record( $data );
	}

	protected function get_null_action() {
		return new ActionScheduler_NullAction();
	}

	public function log( $msg ) {
		BWF_Logger::get_instance()->log( WooFunnels_AS_DS::$unique . ' - ' . $msg, 'woofunnel-as' );
	}

	protected function get_action_data( $action_id ) {
		$cache_key = 'bwf_fetch_action_' . $action_id;

		if ( isset( $this->bwf_action_data[ $action_id ] ) ) {
			return $this->bwf_action_data[ $action_id ];
		}

		$data = wp_cache_get( $cache_key, __CLASS__ );
		if ( false === $data ) {
			$data = BWF_AS_Actions_Crud::get_single_action( $action_id );

			/** Saving data to local scope and cache */
			if ( is_object( $data ) ) {
				$this->bwf_action_data[ $action_id ] = $data;
				wp_cache_set( $cache_key, $data, __CLASS__, ( 60 ) );
			}
		}

		return $data;
	}

	/**
	 * Helper method to schedule recurring action before execution itself.
	 * This make sure recurring action should be scheduled.
	 *
	 * @param $action_id
	 */
	protected function schedule_recurring_action( $action_id ) {
		global $wpdb;

		$data = $this->get_action_data( $action_id );

		/** Checking if recurring action */
		if ( false === $this->action_is_recurring( $data ) ) {
			return;
		}

		$args  = ( is_array( $data->args ) && count( $data->args ) > 0 ) ? $data->args : array();
		$group = ( ! empty( $data->group_slug ) ) ? $data->group_slug : '';

		/** Checking if already running then change the status to failed and schedule new action */
		$count = bwf_scheduled_action_count( $data->hook, $args, $group, '1', 'recurring' );
		if ( false === $count || 1 < $count ) {
			$this->log( __FUNCTION__ . ' id ' . $action_id . ', recurring action already running count: ' . $count );

			/** Changing status to failed i.e. 2 on action */
			$wpdb->update( $this->action_table, [ 'status' => 2 ], [ $this->p_key => $action_id ], [ '%s' ], [ '%d' ] );

			/** Modify action object cache data status */
			if ( isset( $this->bwf_action_data[ $action_id ] ) ) {
				$this->bwf_action_data[ $action_id ]->status = '2';
			}

			/** Needs to re-schedule */
		} else {
			/** Checking already scheduled actions count */
			$count = bwf_scheduled_action_count( $data->hook, $args, $group, '0', 'recurring' );
			$this->log( __FUNCTION__ . ' id ' . $action_id . ', already schedule count: ' . $count );
			/**
			 * Do not create schedule action if we already have min one pending action to run.
			 */
			if ( 1 <= $count ) {
				return;
			}
		}
		/** Scheduling new action */
		$curr_time = current_time( 'mysql', 1 );
		$exec_time = time() + (int) $data->recurring_interval;
		$new_data  = array(
			'c_date'             => $curr_time,
			'e_time'             => $exec_time,
			'hook'               => $data->hook,
			'recurring_interval' => (int) $data->recurring_interval,
		);
		if ( is_array( $data->args ) && count( $data->args ) > 0 ) {
			$new_data['args'] = wp_json_encode( $data->args );
		}
		if ( ! empty( $data->group_slug ) ) {
			$new_data['group_slug'] = $data->group_slug;
		}

		BWF_AS_Actions_Crud::insert( $new_data );
	}

	/**
	 * Helper method that checks if an action is recurring.
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	protected function action_is_recurring( $data ) {
		if ( ! is_object( $data ) ) {
			return false;
		}
		if ( (int) $data->recurring_interval < 1 ) {
			return false;
		}

		return true;
	}

	/**
	 * Initiate action class object with needful data
	 *
	 * @param $data
	 *
	 * @return ActionScheduler_Action|ActionScheduler_FinishedAction
	 */
	protected function make_action_from_db_record( $data ) {
		$hook = $data->hook;
		$args = ( is_array( $data->args ) && count( $data->args ) > 0 ) ? $data->args : [];

		/** creating fresh schedule */
		$schedule = new ActionScheduler_NullSchedule();
		$group    = ( ! empty( $data->group_slug ) ) ? $data->group_slug : '';
		if ( $this->verify_status( $data->status ) ) {
			$action = new ActionScheduler_Action( $hook, $args, $schedule, $group );
		} else {
			/** status not 0 - finishing AS action (status won't occur as we are fetching 0 status actions only) */
			$action = new ActionScheduler_FinishedAction( $hook, $args, $schedule, $group );
		}

		return $action;
	}

	/**
	 * Helper method: If pending action then bool true otherwise false
	 *
	 * @param $status
	 *
	 * @return bool
	 */
	protected function verify_status( $status ) {
		return ( 0 == $status || 1 == $status ) ? true : false;
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
	 * @param string $query_type
	 *
	 * @return array|string ids array or count
	 */
	public function query_actions( $query = [], $query_type = 'select' ) {
		global $wpdb;

		/** cleanup call handling */
		if ( ! isset( $query['status'] ) || in_array( $query['status'], array( 'complete', 'canceled', 'failed' ), true ) ) {
			return array();
		}

		if ( 'pending' === $query['status'] ) {
			$query['status'] = '0';
		} elseif ( 'in-progress' === $query['status'] ) {
			$query['status'] = '1';
		}

		/** Code will through in case of pending status i.e. 0 */
		$sql = $this->get_query_actions_sql( $query, $query_type );

		$value = ( 'count' === $query_type ) ? $wpdb->get_var( $sql ) : $wpdb->get_col( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL

		$this->log( __FUNCTION__ . ': status ' . $query['status'] . ' - (' . $this->get_as_defined_status_val( $query['status'] ) . ')' . ' query result: ' . implode( ',', $value ) );

		return $value;
	}

	/**
	 * Returns the SQL statement to query (or count) actions.
	 *
	 * @param array $query Filtering options
	 * @param string $select_or_count Whether the SQL should select and return the IDs or just the row count
	 *
	 * @return string SQL statement. The returned SQL is already properly escaped.
	 * 'status'           => ActionScheduler_Store::STATUS_PENDING,
	 * 'modified'         => $cutoff,
	 * 'modified_compare' => '<=',
	 * 'claimed'          => true,
	 * 'per_page'         => $this->get_batch_size(),
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
		$sql        = 'SELECT ';
		$sql        .= ( 'count' === $select_or_count ) ? "count({$this->p_key})" : "{$this->p_key} ";
		$sql        .= "FROM {$this->action_table}";
		$sql_params = [];

		$sql .= ' WHERE 1=1';

		if ( ! empty( $query['group'] ) ) {
			$sql          .= ' AND group_slug=%s';
			$sql_params[] = $query['group'];
		}

		if ( $query['hook'] ) {
			$sql          .= ' AND hook=%s';
			$sql_params[] = $query['hook'];
		}
		if ( ! is_null( $query['args'] ) ) {
			$sql          .= ' AND args=%s';
			$sql_params[] = wp_json_encode( $query['args'] );
		}
		/** 0 or 1 in our case */
		if ( '' !== $query['status'] ) {
			$sql          .= ' AND status=%s';
			$sql_params[] = $query['status'];
		}

		if ( $query['date'] instanceof DateTime ) {
			$date = clone $query['date'];
			$date->setTimezone( new DateTimeZone( 'UTC' ) );
			$date_string  = $date->format( 'Y-m-d H:i:s' );
			$comparator   = $this->validate_sql_comparator( $query['date_compare'] );
			$sql          .= " AND e_time $comparator %s";
			$sql_params[] = $date_string;
		}

		if ( $query['claimed'] === true ) {
			$sql .= ' AND claim_id != 0';
		} elseif ( $query['claimed'] === false ) {
			$sql .= ' AND claim_id = 0';
		} elseif ( ! is_null( $query['claimed'] ) ) {
			$sql          .= ' AND claim_id = %d';
			$sql_params[] = $query['claimed'];
		}

		if ( $query['modified'] instanceof DateTime ) {
			$modified = clone $query['modified'];
			$modified->setTimezone( new DateTimeZone( 'UTC' ) );
			$date_string  = $modified->getTimestamp();
			$comparator   = $this->validate_sql_comparator( $query['modified_compare'] );
			$sql          .= " AND e_time $comparator %s";
			$sql_params[] = $date_string;
		}

		if ( 'select' === $select_or_count ) {
			switch ( $query['orderby'] ) {
				case 'date':
				default:
					$orderby = 'e_time';
					break;
			}
			$order = ( strtoupper( $query['order'] ) === 'ASC' ) ? 'ASC' : 'DESC';

			$sql .= " ORDER BY $orderby $order";
			if ( $query['per_page'] > 0 ) {
				$sql          .= ' LIMIT %d, %d';
				$sql_params[] = $query['offset'];
				$sql_params[] = $query['per_page'];
			}
		}

		if ( ! empty( $sql_params ) ) {
			$sql = $wpdb->prepare( $sql, $sql_params ); //phpcs:ignore WordPress.DB.PreparedSQL
		}

		return $sql;
	}

	protected function get_as_defined_status_val( $status ) {
		switch ( $status ) {
			case '0':
				return 'pending';
			case '1':
				return 'in-progress';
			case '2':
				return 'canceled';
		}

		return $status;
	}

	/**
	 * Get a count of all actions in the store, grouped by status; used in native actions listing. Not used in our scope.
	 */
	public function action_counts() {
		return [];
	}

	/**
	 * @param string $action_id
	 *
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function cancel_action( $action_id ) {
		$this->log( __FUNCTION__ . ' id ' . $action_id );

		return;
	}

	/**
	 * @param string $action_id
	 */
	public function delete_action( $action_id ) {
		$this->log( __FUNCTION__ . ' id ' . $action_id );

		return;
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
	 * @param string $action_id
	 *
	 * @return DateTime The GMT date the action is scheduled to run, or the date that it ran.
	 * @throws InvalidArgumentException
	 */
	protected function get_date_gmt( $action_id ) {

		$record = BWF_AS_Actions_Crud::get_single_action( $action_id );
		if ( empty( $record ) ) {
			throw new InvalidArgumentException( sprintf( __( 'Unidentified action %s', 'action-scheduler' ), $action_id ) );
		}
		if ( $this->verify_status( $record->status ) ) {
			return as_get_datetime_object( $record->e_time );
		}
	}

	/**
	 * @param int $max_actions
	 * @param DateTime $before_date Jobs must be schedule before this date. Defaults to now.
	 *
	 * @return ActionScheduler_ActionClaim
	 */
	public function stake_claim( $max_actions = 10, DateTime $before_date = null, $hooks = array(), $group = '' ) {
		$claim_id = $this->generate_claim_id();
		$this->log( __FUNCTION__ . ' claim id: ' . $claim_id );
		$this->claim_actions( $claim_id, $max_actions, $before_date, $hooks, $group );
		$action_ids = $this->find_actions_by_claim_id( $claim_id );

		return new ActionScheduler_ActionClaim( $claim_id, $action_ids );
	}

	/**
	 * Generate claim id of current date time
	 *
	 * @return int
	 */
	protected function generate_claim_id() {

		global $wpdb;
		$now = as_get_datetime_object();
		$wpdb->insert( $this->claim_table, [
			'date' => $now->format( 'Y-m-d H:i:s' ),
		] );

		return $wpdb->insert_id;
	}

	/**
	 * Claim actions which are executable based on given inputs
	 *
	 * @param string $claim_id
	 * @param int $limit
	 * @param DateTime $before_date Should use UTC timezone.
	 *
	 * @return int The number of actions that were claimed
	 * @throws RuntimeException
	 *
	 */
	protected function claim_actions( $claim_id, $limit, DateTime $before_date = null, $hooks = array(), $group = '' ) {
		global $wpdb;

		/** can't use $wpdb->update() because of the <= condition */
		$update = "SELECT {$this->p_key} FROM {$this->action_table}";
		$params = [];

		$where    = 'WHERE `claim_id` = 0 AND `e_time` <= %s AND `status` = 0';
		$params[] = time();

		if ( ! empty( $group ) ) {
			$where    .= ' AND `group` = %s';
			$params[] = $group;
		}

		$order    = 'ORDER BY `e_time` ASC LIMIT %d';
		$params[] = $limit;

		$sql = $wpdb->prepare( "{$update} {$where} {$order}", $params ); //phpcs:ignore WordPress.DB.PreparedSQL

		$action_ids = $wpdb->get_results( $sql, ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL

		if ( ! is_array( $action_ids ) || count( $action_ids ) == 0 ) {
			return 0;
		}

		$action_ids = array_column( $action_ids, $this->p_key );

		/** Update call */
		$type   = array_fill( 0, count( $action_ids ), '%d' );
		$format = implode( ', ', $type );
		$query  = "UPDATE {$this->action_table} SET `claim_id` = %d WHERE {$this->p_key} IN ({$format})";
		$params = array( $claim_id );
		$params = array_merge( $params, $action_ids );
		$sql    = $wpdb->prepare( $query, $params ); //phpcs:ignore WordPress.DB.PreparedSQL

		$rows_affected = $wpdb->query( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL
		if ( $rows_affected === false ) {
			throw new RuntimeException( __( 'Unable to claim actions. Database error.', 'action-scheduler' ) );
		}

		return (int) $rows_affected;
	}

	/**
	 * Get Actions against a claim_id
	 *
	 * @param string $claim_id
	 *
	 * @return $array
	 */
	public function find_actions_by_claim_id( $claim_id ) {
		global $wpdb;

		$cache_key = 'bwf_action_ids_for_claim_id_' . $claim_id;

		$cache_available = wp_cache_get( $cache_key, __CLASS__ );
		if ( false !== $cache_available ) {
			return $cache_available;
		}

		$sql        = "SELECT `{$this->p_key}` FROM {$this->action_table} WHERE claim_id=%d ORDER BY e_time ASC";
		$sql        = $wpdb->prepare( $sql, $claim_id ); //phpcs:ignore WordPress.DB.PreparedSQL
		$action_ids = $wpdb->get_col( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL

		$return = array_map( 'intval', $action_ids );
		wp_cache_set( $cache_key, $return, __CLASS__, ( HOUR_IN_SECONDS / 4 ) );

		$this->log( 'Found ids: ' . implode( ', ', $return ) );

		return $return;
	}

	/**
	 * Return unique claim id counts
	 *
	 * @return int
	 */
	public function get_claim_count() {
		global $wpdb;

		/** status 0 have actions which are executable */
		$sql = "SELECT COUNT(DISTINCT claim_id) FROM {$this->action_table} WHERE claim_id != 0 AND status = 0";

		return (int) $wpdb->get_var( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL
	}

	/**
	 * Return an action's claim ID, as stored in the claim_id column
	 *
	 * @param string $action_id
	 *
	 * @return mixed
	 */
	public function get_claim_id( $action_id ) {
		$this->log( __FUNCTION__ . ' ' . $action_id );

		$claim_id = BWF_AS_Actions_Crud::get_single_action( $action_id, 'claim_id' );

		return (int) $claim_id;
	}

	/**
	 * Releasing the claim
	 *
	 * @param ActionScheduler_ActionClaim $claim
	 */
	public function release_claim( ActionScheduler_ActionClaim $claim ) {
		$this->log( __FUNCTION__ . ' id ' . $claim->get_id() );

		global $wpdb;
		$wpdb->update( $this->action_table, [ 'claim_id' => 0 ], [ 'claim_id' => $claim->get_id() ], [ '%d' ], [ '%d' ] );

		$wpdb->delete( $this->claim_table, [ 'id' => $claim->get_id() ], [ '%d' ] );
	}

	/**
	 * Unclaim pending actions that have not been run within a given time limit.
	 * Default 300
	 * Called inside reset_timeouts method
	 *
	 * @param string $action_id
	 *
	 * @return void
	 */
	public function unclaim_action( $action_id ) {
		$this->log( __FUNCTION__ . ' id ' . $action_id );

		global $wpdb;
		$wpdb->update( $this->action_table, [ 'claim_id' => 0 ], [ $this->p_key => $action_id ], [ '%s' ], [ '%d' ] );
	}

	/**
	 * @param string $action_id
	 * @param null $e
	 */
	public function mark_failure( $action_id, $e = null ) {
		$this->log( __FUNCTION__ . ' for action id ' . $action_id );

		/** Log failure data */
		$this->log_failure_data( $action_id );

		/** Deleting existing action */
		BWF_AS_Actions_Crud::delete( $action_id );
	}

	/**
	 * Helper method
	 * Log failure action data in bwf logs
	 *
	 * @param $action_id
	 */
	protected function log_failure_data( $action_id ) {
		$data = $this->get_action_data( $action_id );

		$log_arr = array(
			'action_id'      => $action_id,
			'creation_date'  => $data->c_date,
			'execution_time' => $data->e_time,
			'hook'           => $data->hook,
			'arguments'      => $data->args,
			'group'          => $data->group_slug,
			'recurring'      => $data->recurring_interval,
			'error'          => error_get_last(),
		);

		/** updating logs force */
		add_filter( 'bwf_logs_allowed', array( $this, 'overriding_bwf_logging' ), 99999, 2 );
		BWF_Logger::get_instance()->log( print_r( $log_arr, true ), 'woofunnel-failed-actions' );
		remove_filter( 'bwf_logs_allowed', array( $this, 'overriding_bwf_logging' ), 99999, 2 );
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
		$this->log( __FUNCTION__ . ' for action id ' . $action_id );

		/** Deleting existing action */
		BWF_AS_Actions_Crud::delete( $action_id );
	}

	public function get_status( $action_id ) {
		$this->log( __FUNCTION__ . ' of action id ' . $action_id );

		$status = BWF_AS_Actions_Crud::get_action_status( $action_id );
		if ( null === $status ) {
			throw new InvalidArgumentException( __( 'Invalid action ID. No status found.', 'action-scheduler' ) );
		} else {
			return $this->get_as_defined_status_val( $status );
		}
	}

	public function overriding_bwf_logging( $value, $filename ) {
		return true;
	}

	/**
	 * Cancel pending actions by hook.
	 *
	 * @param string $hook
	 *
	 * @since 3.0.0 Action Scheduler and 1.9.15 Core
	 */
	public function cancel_actions_by_hook( $hook ) {
		return;
	}

	/**
	 * Cancel pending actions by group.
	 *
	 * @param string $group
	 *
	 * @since 3.0.0 Action Scheduler and 1.9.15 Core
	 */
	public function cancel_actions_by_group( $group ) {
		return;
	}

}
