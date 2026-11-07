<?php
/**
 * Live Viewer block.
 *
 * @package BernskioldMedia\WP\EventsCalendarLiveViewer
 */

namespace BernskioldMedia\WP\EventsCalendarLiveViewer\Blocks;

use BernskioldMedia\WP\EventsCalendarLiveViewer\EventsHelper;
use BernskioldMedia\WP\EventsCalendarLiveViewer\Queries;
use BernskioldMedia\WP\EventsCalendarLiveViewer\Rest\NextVideo;

defined( 'ABSPATH' ) || exit;

/**
 * Renders the player for the current or next event. The block's view
 * module polls the REST API and reloads the page when the next event
 * is about to start.
 */
class Live_Viewer {

	/**
	 * Render the block. Called from blocks/live-viewer/render.php.
	 *
	 * @param  array  $attributes  The block attributes.
	 */
	public static function render( array $attributes = [] ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Part of the render contract.
		$event    = Queries::get_current_or_next_event();
		$event_id = $event ? $event->ID : 0;

		$wrapper_attributes = self::get_wrapper_attributes(
			[
				'class'                 => 'eclw-player',
				'data-event-id'         => (string) $event_id,
				'data-endpoint'         => esc_url_raw( rest_url( NextVideo::NAMESPACE . NextVideo::ROUTE ) ),
				'data-polling-interval' => (string) ( self::get_polling_interval() * 1000 ),
			]
		);

		if ( $event ) {
			$content = EventsHelper::get_player( $event_id );
		} else {
			$content = sprintf(
				'<div class="eclw-player-placeholder">%s</div>',
				esc_html__( 'Check back here later for more events.', 'events-calendar-live-viewer' )
			);
		}

		return sprintf( '<figure %s>%s</figure>', $wrapper_attributes, $content );
	}

	/**
	 * The wrapper attributes, including those from block supports
	 * (anchor, class name) when rendering as a block.
	 *
	 * @param  array<string, string>  $attributes  Attributes to add.
	 */
	private static function get_wrapper_attributes( array $attributes ): string {
		$html = get_block_wrapper_attributes( $attributes );

		// Outside of a block render, core returns nothing.
		if ( '' !== $html ) {
			return $html;
		}

		$parts = [];

		foreach ( $attributes as $name => $value ) {
			$parts[] = sprintf( '%s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}

		return implode( ' ', $parts );
	}

	/**
	 * How often, in seconds, the player checks for the next event.
	 */
	public static function get_polling_interval(): int {
		return max( 1, (int) apply_filters( 'eclw_polling_interval', MINUTE_IN_SECONDS ) );
	}
}
