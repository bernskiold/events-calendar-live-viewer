/**
 * Keeps the live viewer on the right event.
 *
 * Polls the REST API and reloads the page once the next event is about to
 * start, so the server renders its video. The endpoint and the polling
 * interval come from the block's data attributes.
 */

/**
 * Whether the page should reload to show the next event.
 *
 * @param {Object}      response                The next-video REST response.
 * @param {number|null} response.id             The next event's ID.
 * @param {boolean}     response.should_refresh Whether the next event is about to start.
 * @param {number}      eventId                 The event currently shown.
 * @return {boolean} True to reload.
 */
export const shouldReload = ( response, eventId ) =>
	Boolean( response?.id ) &&
	response.id !== eventId &&
	Boolean( response.should_refresh );

const watch = ( player ) => {
	const eventId = parseInt( player.dataset.eventId, 10 ) || 0;
	const endpoint = player.dataset.endpoint;
	const interval = parseInt( player.dataset.pollingInterval, 10 ) || 60000;

	// Without an event there is nothing to move on from.
	if ( ! eventId || ! endpoint ) {
		return;
	}

	const check = async () => {
		try {
			const response = await fetch( endpoint, {
				headers: { Accept: 'application/json' },
				cache: 'no-store',
			} );

			if (
				response.ok &&
				shouldReload( await response.json(), eventId )
			) {
				window.location.reload();
				return;
			}
		} catch {
			// A failed check is retried on the next tick.
		}

		window.setTimeout( check, interval );
	};

	window.setTimeout( check, interval );
};

// Only one player per page can reload it, so watch the first one.
const player = document.querySelector( '.eclw-player' );

if ( player ) {
	watch( player );
}
