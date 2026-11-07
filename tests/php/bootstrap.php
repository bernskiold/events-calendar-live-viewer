<?php
/**
 * PHPUnit bootstrap for the WordPress integration test suite.
 *
 * Install WordPress, its test library and The Events Calendar with
 * `bin/install-wp-tests.sh`, export the variables it prints and run
 * `composer test`.
 *
 * @package BernskioldMedia\WP\EventsCalendarLiveViewer
 */

$_plugin_dir = dirname( __DIR__, 2 );

// Locate the WordPress test library. Honour WP_TESTS_DIR, then the
// wp-phpunit package's WP_PHPUNIT__DIR and finally the system temp dir.
$_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: getenv( 'WP_PHPUNIT__DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Point the WP test suite at the Yoast PHPUnit polyfills bundled via Composer.
if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_plugin_dir . '/vendor/yoast/phpunit-polyfills' );
}

$_functions = "{$_tests_dir}/includes/functions.php";

if ( ! file_exists( $_functions ) ) {
	echo "Could not find the WordPress test library at {$_tests_dir}." . PHP_EOL;
	echo 'Run bin/install-wp-tests.sh and export the variables it prints.' . PHP_EOL;
	exit( 1 );
}

/**
 * Find The Events Calendar, downloading it from wordpress.org when
 * neither TEC_PLUGIN_DIR nor a previous download provides it.
 */
function _eclv_events_calendar_file(): string {
	$dir = getenv( 'TEC_PLUGIN_DIR' );

	if ( ! $dir ) {
		$dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/events-calendar-live-viewer-tec/the-events-calendar';
	}

	$file = rtrim( $dir, '/\\' ) . '/the-events-calendar.php';

	if ( file_exists( $file ) ) {
		return $file;
	}

	$parent = dirname( $dir );
	$zip    = $parent . '/the-events-calendar.zip';

	if ( ! is_dir( $parent ) ) {
		mkdir( $parent, 0777, true );
	}

	echo 'Downloading The Events Calendar from wordpress.org...' . PHP_EOL;
	copy( 'https://downloads.wordpress.org/plugin/the-events-calendar.zip', $zip );

	$archive = new ZipArchive();

	if ( true !== $archive->open( $zip ) ) {
		echo 'Could not download The Events Calendar. Set TEC_PLUGIN_DIR to an existing copy.' . PHP_EOL;
		exit( 1 );
	}

	$archive->extractTo( $parent );
	$archive->close();
	unlink( $zip );

	return $file;
}

$_tec_file = _eclv_events_calendar_file();

/*
 * The Events Calendar's bundled libraries still trigger deprecations on newer
 * PHP versions (implicitly nullable parameters and the like). They are not
 * ours to fix, so ignore deprecations raised from its files while keeping
 * every other notice, warning and deprecation fatal for the suite.
 */
define( 'ECLV_TESTS_TEC_DIR', (string) realpath( dirname( $_tec_file ) ) );

function _eclv_ignore_dependency_deprecations( int $errno, string $errstr, string $errfile ): bool {
	return in_array( $errno, [ E_DEPRECATED, E_USER_DEPRECATED ], true )
		&& str_starts_with( $errfile, ECLV_TESTS_TEC_DIR );
}

set_error_handler(
	static function ( int $errno, string $errstr, string $errfile = '', int $errline = 0 ) {
		// Returning false hands everything else to PHP's own handler.
		return _eclv_ignore_dependency_deprecations( $errno, $errstr, $errfile );
	}
);

// Give access to tests_add_filter() before WordPress loads.
require_once $_functions;

/**
 * Load The Events Calendar and then the plugin under test. PluginTest
 * covers the plugin without its dependency through a filter.
 */
tests_add_filter(
	'muplugins_loaded',
	static function () use ( $_tec_file, $_plugin_dir ): void {
		require $_tec_file;
		require $_plugin_dir . '/events-calendar-live-viewer.php';
	}
);

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";

// PHPUnit only converts errors to exceptions when no other handler is set,
// so hand over now that WordPress and The Events Calendar have loaded.
// TestCase keeps filtering The Events Calendar's deprecations from here on.
restore_error_handler();
