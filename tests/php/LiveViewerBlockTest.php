<?php

namespace BernskioldMedia\WP\EventsCalendarLiveViewer\Tests;

class LiveViewerBlockTest extends TestCase {

	private function render( string $attributes = '' ): string {
		return do_blocks( '<!-- wp:bm/live-viewer ' . $attributes . ' /-->' );
	}

	public function test_it_shows_a_placeholder_when_there_are_no_events(): void {
		$html = $this->render();

		$this->assertStringContainsString( 'data-event-id="0"', $html );
		$this->assertStringContainsString( 'eclw-player-placeholder', $html );
		$this->assertStringContainsString( 'Check back here later for more events.', $html );
	}

	public function test_it_embeds_the_video_of_the_running_event(): void {
		$event = $this->create_event( -10 * MINUTE_IN_SECONDS, HOUR_IN_SECONDS, 'https://www.youtube.com/watch?v=current' );
		$this->create_event( DAY_IN_SECONDS, HOUR_IN_SECONDS, 'https://www.youtube.com/watch?v=later' );

		$html = $this->render();

		$this->assertMatchesRegularExpression( '/<figure [^>]*class="[^"]*\beclw-player\b/', $html );
		$this->assertStringContainsString( 'data-event-id="' . $event . '"', $html );
		$this->assertStringContainsString( 'watch?v=current', $html );
		$this->assertStringNotContainsString( 'watch?v=later', $html );
		$this->assertStringNotContainsString( 'eclw-player-placeholder', $html );
	}

	public function test_it_embeds_the_next_event_when_nothing_is_running(): void {
		$event = $this->create_event( DAY_IN_SECONDS, HOUR_IN_SECONDS, 'https://www.youtube.com/watch?v=next' );

		$html = $this->render();

		$this->assertStringContainsString( 'data-event-id="' . $event . '"', $html );
		$this->assertStringContainsString( 'watch?v=next', $html );
	}

	public function test_an_event_without_a_video_renders_an_empty_player(): void {
		$event = $this->create_event( -10 * MINUTE_IN_SECONDS );
		delete_post_meta( $event, '_tribe_events_virtual_url' );

		$html = $this->render();

		$this->assertStringContainsString( 'data-event-id="' . $event . '"', $html );
		$this->assertStringNotContainsString( '<iframe', $html );
	}

	public function test_it_passes_the_endpoint_and_polling_interval_to_the_view_module(): void {
		$this->create_event( HOUR_IN_SECONDS );
		add_filter( 'eclw_polling_interval', static fn() => 30 );

		$html = $this->render();

		$this->assertStringContainsString( 'data-endpoint="' . esc_attr( rest_url( 'eclw/v1/next-video' ) ) . '"', $html );
		$this->assertStringContainsString( 'data-polling-interval="30000"', $html );
	}

	public function test_it_keeps_the_anchor_and_custom_class(): void {
		$html = $this->render( '{"anchor":"live","className":"is-style-big"}' );

		// The anchor support only applies to saved markup, so check the class name.
		$this->assertMatchesRegularExpression( '/class="[^"]*\beclw-player\b[^"]*\bis-style-big\b|class="[^"]*\bis-style-big\b[^"]*\beclw-player\b/', $html );
	}

	public function test_rendering_the_block_enqueues_the_view_module(): void {
		$this->render();

		$block = \WP_Block_Type_Registry::get_instance()->get_registered( 'bm/live-viewer' );

		ob_start();
		wp_script_modules()->print_enqueued_script_modules();
		$printed = ob_get_clean();

		$this->assertStringContainsString( 'dist/blocks/live-viewer/view.js', $printed );
		$this->assertContains( $block->view_script_module_ids[0], array_keys( $this->get_enqueued_modules() ) );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function get_enqueued_modules(): array {
		$queue = ( new \ReflectionProperty( \WP_Script_Modules::class, 'queue' ) )->getValue( wp_script_modules() );

		return array_flip( $queue );
	}
}
