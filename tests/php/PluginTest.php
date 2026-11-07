<?php

namespace BernskioldMedia\WP\EventsCalendarLiveViewer\Tests;

use BernskioldMedia\WP\EventsCalendarLiveViewer\Plugin;

class PluginTest extends TestCase {

	public function test_the_events_calendar_is_loaded(): void {
		$this->assertTrue( Plugin::has_events_calendar() );
	}

	public function test_it_boots_when_the_events_calendar_is_active(): void {
		$this->assertSame( 1, did_action( 'events_calendar_live_viewer_loaded' ) );
		$this->assertFalse( has_action( 'admin_notices', [ Plugin::class, 'missing_events_calendar_notice' ] ) );
	}

	public function test_it_registers_the_live_viewer_block(): void {
		$block = \WP_Block_Type_Registry::get_instance()->get_registered( 'bm/live-viewer' );

		$this->assertInstanceOf( \WP_Block_Type::class, $block );
		$this->assertSame( 3, $block->api_version );
		$this->assertTrue( $block->is_dynamic() );
		$this->assertNotEmpty( $block->editor_script_handles );
		$this->assertNotEmpty( $block->view_script_module_ids );
	}

	public function test_the_block_assets_point_at_the_build(): void {
		$block  = \WP_Block_Type_Registry::get_instance()->get_registered( 'bm/live-viewer' );
		$script = wp_scripts()->registered[ $block->editor_script_handles[0] ];

		$this->assertStringEndsWith( '/dist/blocks/live-viewer.js', $script->src );
		$this->assertContains( 'wp-blocks', $script->deps );
	}

	public function test_the_block_manifest_matches_block_json(): void {
		$manifest = require dirname( __DIR__, 2 ) . '/blocks/blocks-manifest.php';
		$metadata = wp_json_file_decode( dirname( __DIR__, 2 ) . '/blocks/live-viewer/block.json', [ 'associative' => true ] );

		$this->assertSame( $metadata, $manifest['live-viewer'], 'Run npm run build to regenerate blocks/blocks-manifest.php.' );
	}

	public function test_it_registers_the_rest_route(): void {
		$this->assertArrayHasKey( '/eclw/v1/next-video', rest_get_server()->get_routes() );
	}

	public function test_it_shows_a_notice_and_stays_inactive_without_the_events_calendar(): void {
		add_filter( 'events_calendar_live_viewer_has_events_calendar', '__return_false' );
		remove_all_actions( 'admin_notices' );
		$loaded = did_action( 'events_calendar_live_viewer_loaded' );

		Plugin::boot();

		$this->assertSame( $loaded, did_action( 'events_calendar_live_viewer_loaded' ), 'The plugin booted without its dependency.' );
		$this->assertSame( 10, has_action( 'admin_notices', [ Plugin::class, 'missing_events_calendar_notice' ] ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		ob_start();
		do_action( 'admin_notices' );
		$notice = ob_get_clean();

		$this->assertStringContainsString( 'notice-error', $notice );
		$this->assertStringContainsString( 'The Events Calendar', $notice );
	}

	public function test_the_missing_dependency_notice_is_only_shown_to_administrators(): void {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );

		ob_start();
		Plugin::missing_events_calendar_notice();

		$this->assertSame( '', ob_get_clean() );
	}
}
