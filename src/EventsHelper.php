<?php
/**
 * Helpers for reading event data from The Events Calendar.
 *
 * @package BernskioldMedia\WP\EventsCalendarLiveViewer
 */

namespace BernskioldMedia\WP\EventsCalendarLiveViewer;

defined( 'ABSPATH' ) || exit;

class EventsHelper {

	/**
	 * The start time in the event's own time zone, formatted Y-m-d H:i:s.
	 */
	public static function get_start_time( int $post_id ): ?string {
		$time = tribe_get_start_date( $post_id, true, 'Y-m-d H:i:s' );

		return is_string( $time ) && '' !== $time ? $time : null;
	}

	/**
	 * The Unix timestamp the event starts at.
	 */
	public static function get_start_timestamp( int $post_id ): ?int {
		return self::get_utc_timestamp( $post_id, '_EventStartDateUTC' );
	}

	/**
	 * The end time in the event's own time zone, formatted Y-m-d H:i:s.
	 */
	public static function get_end_time( int $post_id ): ?string {
		$time = tribe_get_end_date( $post_id, true, 'Y-m-d H:i:s' );

		return is_string( $time ) && '' !== $time ? $time : null;
	}

	/**
	 * The Unix timestamp the event ends at.
	 */
	public static function get_end_timestamp( int $post_id ): ?int {
		return self::get_utc_timestamp( $post_id, '_EventEndDateUTC' );
	}

	/**
	 * The video URL set by Virtual Events.
	 */
	public static function get_embed_url( int $post_id ): ?string {
		$meta = get_post_meta( $post_id, '_tribe_events_virtual_url', true );

		return is_string( $meta ) && '' !== $meta ? $meta : null;
	}

	/**
	 * The embed HTML for the event's video, or an empty string
	 * when the event has no video WordPress can embed.
	 */
	public static function get_player( int $post_id ): string {
		$url = self::get_embed_url( $post_id );

		if ( ! $url ) {
			return '';
		}

		$html = wp_oembed_get(
			$url,
			[
				'width'  => 1280,
				'height' => 720,
			]
		);

		return is_string( $html ) ? $html : '';
	}

	public static function the_player( int $post_id ): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oEmbed HTML from a provider WordPress trusts.
		echo self::get_player( $post_id );
	}

	/**
	 * Whether the next event starts soon enough that viewers
	 * should be moved over to it.
	 */
	public static function should_load_next(): bool {
		$post = Queries::get_next_event();

		if ( ! $post ) {
			return false;
		}

		$starts_at = self::get_start_timestamp( $post->ID );

		if ( null === $starts_at ) {
			return false;
		}

		return $starts_at - time() <= self::get_seconds_before_to_load_next();
	}

	public static function get_seconds_before_to_load_next(): int {
		return (int) apply_filters( 'eclw_seconds_before_to_load_next', MINUTE_IN_SECONDS * 5 );
	}

	/**
	 * Read one of the UTC dates The Events Calendar stores with each event.
	 *
	 * Comparing these with time() keeps the switch-over correct whatever
	 * the site's or the event's time zone is.
	 */
	private static function get_utc_timestamp( int $post_id, string $meta_key ): ?int {
		$date = get_post_meta( $post_id, $meta_key, true );

		if ( ! is_string( $date ) || '' === $date ) {
			return null;
		}

		try {
			return ( new \DateTimeImmutable( $date, new \DateTimeZone( 'UTC' ) ) )->getTimestamp();
		} catch ( \Exception $e ) {
			return null;
		}
	}
}
