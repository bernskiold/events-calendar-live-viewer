<?php

namespace BernskioldMedia\WP\EventsCalendarLiveViewer\Rest;

use BernskioldMedia\WP\EventsCalendarLiveViewer\EventsHelper;
use BernskioldMedia\WP\EventsCalendarLiveViewer\Hookable;
use BernskioldMedia\WP\EventsCalendarLiveViewer\Queries;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * Tells the front-end player which event is up next, and
 * whether it is time to reload to show it.
 */
class NextVideo implements Hookable {

	public const NAMESPACE = 'eclw/v1';
	public const ROUTE     = '/next-video';

	public static function hooks(): void {
		add_action( 'rest_api_init', [ self::class, 'register_routes' ] );
	}

	public static function register_routes(): void {
		register_rest_route( self::NAMESPACE, self::ROUTE, [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ self::class, 'get_next_video' ],
			'permission_callback' => '__return_true',
		] );
	}

	public static function get_next_video(): WP_REST_Response {
		$post = Queries::get_next_event();

		if ( ! $post ) {
			$data = [
				'id'             => null,
				'start_time'     => null,
				'end_time'       => null,
				'should_refresh' => false,
			];
		} else {
			$data = [
				'id'             => $post->ID,
				'start_time'     => EventsHelper::get_start_time( $post->ID ),
				'end_time'       => EventsHelper::get_end_time( $post->ID ),
				'should_refresh' => EventsHelper::should_load_next(),
			];
		}

		return new WP_REST_Response( $data, 200 );
	}
}
