<?php

namespace BernskioldMedia\WP\EventsCalendarLiveViewer\Tests;

use WP_UnitTestCase;

/**
 * Base test case with helpers for creating events in The Events Calendar.
 */
abstract class TestCase extends WP_UnitTestCase {

	/**
	 * The error handler PHPUnit installed for the running test.
	 *
	 * @var callable|null
	 */
	private $phpunit_error_handler;

	public function set_up(): void {
		parent::set_up();

		// PHPUnit turns every deprecation into a failure. Let those raised by
		// The Events Calendar's own files through, see bootstrap.php.
		$this->phpunit_error_handler = set_error_handler(
			function ( int $errno, string $errstr, string $errfile = '', int $errline = 0 ) {
				if ( _eclv_ignore_dependency_deprecations( $errno, $errstr, $errfile ) ) {
						return true;
				}

				return $this->phpunit_error_handler ? ( $this->phpunit_error_handler )( $errno, $errstr, $errfile, $errline ) : false;
			}
		);

		// Keep the suite offline: answer every oEmbed lookup locally.
		add_filter( 'pre_oembed_result', [ $this, 'fake_oembed' ], 10, 2 );
	}

	public function tear_down(): void {
		remove_filter( 'pre_oembed_result', [ $this, 'fake_oembed' ], 10 );
		restore_error_handler();

		parent::tear_down();
	}

	public function fake_oembed( $result, string $url ): string {
		return '<iframe class="fake-embed" src="' . esc_url( $url ) . '"></iframe>';
	}

	/**
	 * Create a published event that starts the given number of seconds from
	 * now (negative for the past) and lasts the given number of seconds.
	 */
	protected function create_event( int $starts_in, int $duration = HOUR_IN_SECONDS, string $video_url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' ): int {
		$timezone = wp_timezone();
		$start    = ( new \DateTimeImmutable( 'now', $timezone ) )->modify( sprintf( '%+d seconds', $starts_in ) );
		$end      = $start->modify( sprintf( '+%d seconds', $duration ) );

		$event = tribe_events()->set_args(
			[
				'title'      => 'Event starting in ' . $starts_in,
				'status'     => 'publish',
				'start_date' => $start->format( 'Y-m-d H:i:s' ),
				'end_date'   => $end->format( 'Y-m-d H:i:s' ),
				'timezone'   => $timezone->getName(),
			]
		)->create();

		$this->assertInstanceOf( \WP_Post::class, $event, 'The Events Calendar could not create the event.' );

		update_post_meta( $event->ID, '_tribe_events_virtual_url', $video_url );

		return $event->ID;
	}
}
