<?php

/**
 * Not saving any log as no do_action left in action data store
 *
 * Class BWFAN_AS_CT_Log_Store
 */
class BWFAN_AS_CT_Log_Store extends ActionScheduler_Logger {

	public function log( $action_id, $message, DateTime $date = null ) {

	}

	public function get_entry( $entry_id ) {
		return new ActionScheduler_NullLogEntry();
	}

	public function get_logs( $action_id ) {
		return array();
	}

	public function init() {
	}

	public function clear_deleted_action_logs( $action_id ) {
	}

}
