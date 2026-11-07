<?php
/**
 * Handles the loading of scripts and styles for the
 * theme through the proper enqueuing methods.
 *
 * @author  Bernskiold Media <info@bernskioldmedia.com>
 * @package BernskioldMedia\BernskioldMedia\EventsCalendarLiveViewer
 * @since   1.0.0
 **/

namespace BernskioldMedia\WP\EventsCalendarLiveViewer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets implements Hookable {

	public static function hooks(): void {
		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue' ] );
	}

	public static function enqueue(): void {
		global $post;

		wp_register_script( 'eclw-live-viewer', Plugin::get_url( 'dist/public/live-player.js' ), [], Plugin::get_version(), true );

		if ( has_block( 'bm/live-viewer', $post->ID ) ) {
			wp_enqueue_script( 'eclw-live-viewer' );

			$polling_interval = (int) apply_filters( 'eclw_polling_interval', MINUTE_IN_SECONDS );

			wp_localize_script(
				'eclw-live-viewer',
				'ECLW',
				[
					'apiUrl'          => get_rest_url( get_current_blog_id(), '/eclw/v1' ),
					'pollingInterval' => $polling_interval * 1000, // In milliseconds.
				]
			);
		}
	}
}
