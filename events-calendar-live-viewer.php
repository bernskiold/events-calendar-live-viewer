<?php
/**
 * Plugin Name: Events Calendar Live Viewer
 * Plugin URI:        https://github.com/bernskiold/events-calendar-live-viewer
 * Description:       An auto-updating live viewer for virtual events in The Events Calendar.
 * Version:           1.0.0
 * Requires at least: 7.0
 * Tested up to:      7.1
 * Requires PHP:      8.2
 * Requires Plugins:  the-events-calendar
 * Author:            Bernskiold
 * Author URI:        https://bernskiold.com
 * License:           GPL-3.0-or-later
 * Text Domain:       events-calendar-live-viewer
 * Domain Path:       /languages/
 *
 * @package BernskioldMedia\WP\EventsCalendarLiveViewer
 */

use BernskioldMedia\WP\EventsCalendarLiveViewer\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoloader
 *
 * The plugin has no runtime Composer dependencies, so when it is installed
 * from a release ZIP without a vendor directory we load our own classes.
 */
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
} else {
	spl_autoload_register(
		static function ( string $class_name ): void {
			$prefix = 'BernskioldMedia\\WP\\EventsCalendarLiveViewer\\';

			if ( ! str_starts_with( $class_name, $prefix ) ) {
				return;
			}

			$file = __DIR__ . '/src/' . str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) ) . '.php';

			if ( file_exists( $file ) ) {
				require $file;
			}
		}
	);
}

/**
 * Basic Constants
 */
define( 'EVENTS_CALENDAR_LIVE_VIEWER_FILE_PATH', __FILE__ );

/**
 * Initialize and boot the plugin.
 *
 * @return Plugin
 */
function events_calendar_live_viewer() {
	return Plugin::instance();
}

events_calendar_live_viewer();
