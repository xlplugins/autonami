<?php

use WP_CLI\ExitException;
use function WP_CLI\Utils\get_flag_value;

/**
 * Commands for the Custom Action Scheduler
 */
class BWF_AS_CLI extends WP_CLI_Command {

	/**
	 * Run the Autonami tasks
	 *
	 * ## OPTIONS
	 *
	 * [--size=<size>]
	 * : The maximum number of tasks to run. Defaults to 50.
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Keyed arguments.
	 *
	 * @throws ExitException When an error occurs.
	 */
	public function run( $args, $assoc_args ) {
		// Handle passed arguments.
		$size = absint( get_flag_value( $assoc_args, 'size', 50 ) );

		$tasks_completed = 0;

		try {

			if ( ! class_exists( 'ActionScheduler_QueueRunner' ) ) {
				$this->print_custom_error( '1' );
			}

			$global_settings = BWFAN_Common::get_global_settings();
			if ( 1 == $global_settings['bwfan_sandbox_mode'] || ( defined( 'BWFAN_SANDBOX_MODE' ) && true === BWFAN_SANDBOX_MODE ) ) {
				$this->print_custom_error( '2' );
			}

			/** Custom queue cleaner instance */
			$cleaner = new ActionScheduler_QueueCleaner( null, $size );

			/** Queue runner instance */
			$runner = new ActionScheduler_WPCLI_QueueRunner( null, null, $cleaner );

			/** Run Action Scheduler worker */
			// Determine how many tasks will be run in the first batch.
			$total = $runner->setup( $size );

			WP_CLI::log( "Current batch size is: " . $size );

			$this->print_total_tasks( $total );
			$tasks_completed = $runner->run();
		} catch ( Exception $e ) {
			$this->print_error( $e );
		}

		$this->print_success( $tasks_completed );
	}

	protected function print_custom_error( $type ) {
		switch ( $type ) {
			case '1':
				$msg = 'ActionScheduler_QueueRunner class not found.';
				break;
			case '2':
				$msg = 'Autonami Sandbox mode is ON.';
				break;
			default:
				$msg = 'Some error occurred';
		}
		WP_CLI::error( sprintf( /* translators: %s refers to the exception error message. */ $msg ) );
	}

	/**
	 * Print WP CLI message about how many tasks are about to be processed.
	 *
	 * @param int $total
	 */
	protected function print_total_tasks( $total ) {
		WP_CLI::log( sprintf( /* translators: %d refers to how many scheduled tasks were found to run */ _n( 'Found %d scheduled task', 'Found %d scheduled tasks', $total, 'action-scheduler' ), number_format_i18n( $total ) ) );
	}

	/**
	 * Convert an exception into a WP CLI error.
	 *
	 * @param Exception $e The error object.
	 *
	 * @throws ExitException
	 */
	protected function print_error( Exception $e ) {
		WP_CLI::error( sprintf( /* translators: %s refers to the exception error message. */ __( 'There was an error running the action scheduler: %s', 'action-scheduler' ), $e->getMessage() ) );
	}

	/**
	 * Print a success message with the number of completed tasks.
	 *
	 * @param int $tasks_completed
	 */
	protected function print_success( $tasks_completed ) {
		WP_CLI::success( sprintf( /* translators: %d refers to the total number of tasks completed */ _n( '%d scheduled task completed.', '%d scheduled tasks completed.', $tasks_completed, 'action-scheduler' ), number_format_i18n( $tasks_completed ) ) );
	}
}
