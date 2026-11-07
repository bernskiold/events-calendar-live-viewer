<?php

namespace BernskioldMedia\WP\EventsCalendarLiveViewer\Blocks;

use BernskioldMedia\WP\EventsCalendarLiveViewer\EventsHelper;
use BernskioldMedia\WP\EventsCalendarLiveViewer\Queries;

defined( 'ABSPATH' ) || exit;

class Live_Viewer {

	public static function render( array $attributes ): string {
		ob_start();
		$event = Queries::get_current_or_next_event();

		if ( $event ) :

			?>
			<figure class="eclw-player" data-event-id="<?php echo esc_attr( $event->ID ); ?>">
				<?php EventsHelper::the_player( $event->ID ); ?>
			</figure>
		<?php else : ?>
			<figure class="eclw-player" data-event-id="0">
				<div class="eclw-player-placeholder">
					Check back here later for more events.
				</div>
			</figure>
		<?php
		endif;

		return ob_get_clean();
	}

}
