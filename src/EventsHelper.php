<?php

namespace BernskioldMedia\WP\EventsCalendarLiveViewer;

class EventsHelper {

	public static function get_start_time( int $post_id ): ?string {
		return tribe_get_start_date( $post_id, true, 'Y-m-d H:i:s' );
	}

	public static function get_start_timestamp( int $post_id ): ?int {
		return tribe_get_start_date( $post_id, true, 'U' );
	}

	public static function get_end_time( int $post_id ): ?string {
		return tribe_get_end_date( $post_id, true, 'Y-m-d H:i:s' );
	}

	public static function get_end_timestamp( int $post_id ): ?int {
		return tribe_get_end_date( $post_id, true, 'U' );
	}

	public static function get_embed_url( int $post_id ): ?string {
		$meta = get_post_meta( $post_id, '_tribe_events_virtual_url', true );

		return is_string( $meta ) ? $meta : null;
	}

	public static function the_player( int $post_id ): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oEmbed HTML from a provider WordPress trusts.
		echo wp_oembed_get(
			self::get_embed_url( $post_id ),
			[
				'width'  => 1280,
				'height' => 720,
			]
		);
	}

	public static function should_load_next(): bool {
		$post = Queries::get_next_event();

		if ( ! $post ) {
			return false;
		}

		$seconds_before = self::get_seconds_before_to_load_next();
		$time_left      = self::get_start_timestamp( $post->ID ) - current_time( 'timestamp' );

		return $time_left <= $seconds_before;
	}

	public static function get_seconds_before_to_load_next(): int {
		return apply_filters( 'eclw_seconds_before_to_load_next', MINUTE_IN_SECONDS * 5 );
	}
}
