<?php

namespace BernskioldMedia\WP\EventsCalendarLiveViewer\Tests;

use WP_REST_Request;

class RestTest extends TestCase {

	private function get_next_video(): \WP_REST_Response {
		// Anonymous visitors poll this endpoint, so it must be public.
		wp_set_current_user( 0 );

		return rest_get_server()->dispatch( new WP_REST_Request( 'GET', '/eclw/v1/next-video' ) );
	}

	public function test_it_reports_nothing_when_there_is_no_upcoming_event(): void {
		$response = $this->get_next_video();

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame(
			[
				'id'             => null,
				'start_time'     => null,
				'end_time'       => null,
				'should_refresh' => false,
			],
			$response->get_data()
		);
	}

	public function test_it_reports_the_next_event_without_refreshing_too_early(): void {
		$event = $this->create_event( DAY_IN_SECONDS );

		$data = $this->get_next_video()->get_data();

		$this->assertSame( $event, $data['id'] );
		$this->assertSame( tribe_get_start_date( $event, true, 'Y-m-d H:i:s' ), $data['start_time'] );
		$this->assertSame( tribe_get_end_date( $event, true, 'Y-m-d H:i:s' ), $data['end_time'] );
		$this->assertFalse( $data['should_refresh'] );
	}

	public function test_it_asks_the_player_to_refresh_when_the_next_event_is_about_to_start(): void {
		$event = $this->create_event( MINUTE_IN_SECONDS );

		$data = $this->get_next_video()->get_data();

		$this->assertSame( $event, $data['id'] );
		$this->assertTrue( $data['should_refresh'] );
	}

	public function test_it_only_accepts_get_requests(): void {
		$response = rest_get_server()->dispatch( new WP_REST_Request( 'POST', '/eclw/v1/next-video' ) );

		$this->assertSame( 404, $response->get_status() );
	}
}
