<?php
/**
 * Main plugin class.
 *
 * @package BernskioldMedia\WP\EventsCalendarLiveViewer
 */

namespace BernskioldMedia\WP\EventsCalendarLiveViewer;

use BernskioldMedia\WP\EventsCalendarLiveViewer\Rest\NextVideo;

defined( 'ABSPATH' ) || exit;

/**
 * Boots the plugin when The Events Calendar is available.
 */
class Plugin {

	public const TEXTDOMAIN = 'events-calendar-live-viewer';

	/**
	 * Bumped by the release workflow.
	 */
	private static string $version = '2.0.0';

	/**
	 * Classes whose hooks are registered when the plugin boots.
	 *
	 * @var class-string<Hookable>[]
	 */
	private const BOOT = [
		NextVideo::class,
	];

	private static ?self $instance = null;

	public static function instance(): self {
		return self::$instance ??= new self();
	}

	public function __construct() {
		add_action( 'init', [ self::class, 'load_languages' ] );

		// The Events Calendar loads after us alphabetically, so wait
		// until all plugins are loaded before checking for it.
		add_action( 'plugins_loaded', [ self::class, 'boot' ] );
	}

	/**
	 * Register the hooks, or tell administrators what is missing.
	 */
	public static function boot(): void {
		if ( ! self::has_events_calendar() ) {
			add_action( 'admin_notices', [ self::class, 'missing_events_calendar_notice' ] );

			return;
		}

		add_action( 'init', [ self::class, 'register_blocks' ] );

		foreach ( self::BOOT as $class ) {
			$class::hooks();
		}

		do_action( 'events_calendar_live_viewer_loaded' );
	}

	/**
	 * Cloning is forbidden.
	 */
	private function __clone() {
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		throw new \LogicException( 'Cannot unserialize a singleton.' );
	}

	public static function get_path( string $file = '' ): string {
		return untrailingslashit( plugin_dir_path( EVENTS_CALENDAR_LIVE_VIEWER_FILE_PATH ) ) . '/' . $file;
	}

	public static function get_url( string $file = '' ): string {
		return untrailingslashit( plugin_dir_url( EVENTS_CALENDAR_LIVE_VIEWER_FILE_PATH ) ) . '/' . $file;
	}

	public static function get_version(): string {
		return self::$version;
	}

	public static function get_textdomain(): string {
		return self::TEXTDOMAIN;
	}

	/**
	 * Whether The Events Calendar is loaded and provides the API we use.
	 */
	public static function has_events_calendar(): bool {
		$available = class_exists( 'Tribe__Events__Main' ) && function_exists( 'tribe_events' );

		return (bool) apply_filters( 'events_calendar_live_viewer_has_events_calendar', $available );
	}

	/**
	 * Tell administrators that the plugin is inactive without The Events Calendar.
	 */
	public static function missing_events_calendar_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			wp_kses(
				sprintf(
					/* translators: 1: This plugin's name. 2: The name of the required plugin. */
					__( '<strong>%1$s</strong> is not running because it requires <em>%2$s</em>. Please install and activate it.', 'events-calendar-live-viewer' ),
					'Events Calendar Live Viewer',
					'The Events Calendar'
				),
				[
					'strong' => [],
					'em'     => [],
				]
			)
		);
	}

	/**
	 * Load translations, first from the global languages
	 * directory and then from the plugin.
	 */
	public static function load_languages(): void {
		$textdomain = self::TEXTDOMAIN;
		$locale     = is_admin() ? get_user_locale() : get_locale();
		$locale     = apply_filters( 'plugin_locale', $locale, $textdomain ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter.

		unload_textdomain( $textdomain );
		load_textdomain( $textdomain, WP_LANG_DIR . "/{$textdomain}/{$textdomain}-{$locale}.mo" );
		load_plugin_textdomain( $textdomain, false, dirname( plugin_basename( EVENTS_CALENDAR_LIVE_VIEWER_FILE_PATH ) ) . '/languages' );
	}

	/**
	 * Register every block in /blocks from the manifest that
	 * `npm run build` generates from their block.json files.
	 */
	public static function register_blocks(): void {
		wp_register_block_types_from_metadata_collection(
			self::get_path( 'blocks' ),
			self::get_path( 'blocks/blocks-manifest.php' )
		);
	}
}
