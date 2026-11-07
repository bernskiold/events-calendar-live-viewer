<?php
/**
 * Hookable interface.
 *
 * @package BernskioldMedia\WP\EventsCalendarLiveViewer
 */

namespace BernskioldMedia\WP\EventsCalendarLiveViewer;

defined( 'ABSPATH' ) || exit;

/**
 * A class that registers its WordPress hooks when the plugin boots.
 */
interface Hookable {

	/**
	 * Register the class' actions and filters.
	 */
	public static function hooks(): void;
}
