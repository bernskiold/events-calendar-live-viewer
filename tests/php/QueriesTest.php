<?php

namespace BernskioldMedia\WP\EventsCalendarLiveViewer\Tests;

use BernskioldMedia\WP\EventsCalendarLiveViewer\EventsHelper;
use BernskioldMedia\WP\EventsCalendarLiveViewer\Queries;

class QueriesTest extends TestCase {

	public function test_there_is_no_event_without_events(): void {
		$this->assertNull( Queries::get_current_event() );
		$this->assertNull( Queries::get_next_event() );
		$this->assertNull( Queries::get_current_or_next_event() );
		$this->assertFalse( EventsHelper::should_load_next() );
	}

	public function test_it_finds_the_event_that_is_running_now(): void {
		$current = $this->create_event( -10 * MINUTE_IN_SECONDS );
		$this->create_event( -3 * HOUR_IN_SECONDS ); // Already over.

		$this->assertSame( $current, Queries::get_current_event()->ID );
	}

	public function test_it_finds_the_next_upcoming_event(): void {
		$this->create_event( -10 * MINUTE_IN_SECONDS );
		$later = $this->create_event( 2 * DAY_IN_SECONDS );
		$next  = $this->create_event( DAY_IN_SECONDS );

		$this->assertSame( $next, Queries::get_next_event()->ID );
		$this->assertNotSame( $later, Queries::get_next_event()->ID );
	}

	public function test_it_shows_the_current_event_until_the_next_one_is_close(): void {
		$current = $this->create_event( -10 * MINUTE_IN_SECONDS );
		$this->create_event( 30 * MINUTE_IN_SECONDS );

		$this->assertFalse( EventsHelper::should_load_next() );
		$this->assertSame( $current, Queries::get_current_or_next_event()->ID );
	}

	public function test_it_switches_to_the_next_event_shortly_before_it_starts(): void {
		$this->create_event( -10 * MINUTE_IN_SECONDS );
		$next = $this->create_event( 2 * MINUTE_IN_SECONDS );

		$this->assertTrue( EventsHelper::should_load_next() );
		$this->assertSame( $next, Queries::get_current_or_next_event()->ID );
	}

	public function test_it_falls_back_to_the_next_event_when_nothing_is_running(): void {
		$next = $this->create_event( 3 * HOUR_IN_SECONDS );

		$this->assertSame( $next, Queries::get_current_or_next_event()->ID );
	}

	public function test_the_switch_over_window_is_filterable(): void {
		$this->create_event( 30 * MINUTE_IN_SECONDS );

		$this->assertSame( 5 * MINUTE_IN_SECONDS, EventsHelper::get_seconds_before_to_load_next() );
		$this->assertFalse( EventsHelper::should_load_next() );

		add_filter( 'eclw_seconds_before_to_load_next', static fn() => HOUR_IN_SECONDS );

		$this->assertTrue( EventsHelper::should_load_next() );
	}

	public function test_it_reads_the_event_times_and_video_url(): void {
		$event = $this->create_event( HOUR_IN_SECONDS, 2 * HOUR_IN_SECONDS, 'https://vimeo.com/76979871' );

		$start = EventsHelper::get_start_timestamp( $event );
		$end   = EventsHelper::get_end_timestamp( $event );

		$this->assertEqualsWithDelta( time() + HOUR_IN_SECONDS, $start, 5 );
		$this->assertSame( 2 * HOUR_IN_SECONDS, $end - $start );
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', EventsHelper::get_start_time( $event ) );
		$this->assertSame( 'https://vimeo.com/76979871', EventsHelper::get_embed_url( $event ) );
	}

	public function test_the_switch_over_does_not_depend_on_the_site_time_zone(): void {
		update_option( 'timezone_string', 'Pacific/Auckland' );

		$this->create_event( 30 * MINUTE_IN_SECONDS );
		$this->assertFalse( EventsHelper::should_load_next() );

		$soon = $this->create_event( 2 * MINUTE_IN_SECONDS );
		$this->assertTrue( EventsHelper::should_load_next() );
		$this->assertEqualsWithDelta( time() + 2 * MINUTE_IN_SECONDS, EventsHelper::get_start_timestamp( $soon ), 5 );
	}

	public function test_an_event_without_a_video_has_no_embed_url(): void {
		$event = $this->create_event( HOUR_IN_SECONDS );
		delete_post_meta( $event, '_tribe_events_virtual_url' );

		$this->assertNull( EventsHelper::get_embed_url( $event ) );
	}
}
