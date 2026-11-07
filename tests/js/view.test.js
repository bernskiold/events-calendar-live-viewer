/**
 * External dependencies
 */
import { afterEach, describe, expect, it, vi } from 'vitest';

/**
 * Internal dependencies
 */
import { shouldReload } from '../../blocks/live-viewer/view';

describe( 'shouldReload', () => {
	it( 'reloads when a different event is about to start', () => {
		expect( shouldReload( { id: 9, should_refresh: true }, 5 ) ).toBe(
			true
		);
	} );

	it( 'waits while the next event is not close yet', () => {
		expect( shouldReload( { id: 9, should_refresh: false }, 5 ) ).toBe(
			false
		);
	} );

	it( 'does not reload for the event already shown', () => {
		expect( shouldReload( { id: 5, should_refresh: true }, 5 ) ).toBe(
			false
		);
	} );

	it( 'does not reload when there is no next event', () => {
		expect( shouldReload( { id: null, should_refresh: false }, 5 ) ).toBe(
			false
		);
		expect( shouldReload( null, 5 ) ).toBe( false );
	} );
} );

describe( 'the player', () => {
	const setUp = async ( response, eventId = '5' ) => {
		vi.resetModules();
		vi.useFakeTimers();

		document.body.innerHTML = `<figure class="eclw-player" data-event-id="${ eventId }" data-endpoint="https://example.org/wp-json/eclw/v1/next-video" data-polling-interval="1000"></figure>`;

		vi.stubGlobal(
			'fetch',
			vi.fn( () =>
				Promise.resolve( {
					ok: true,
					json: () => Promise.resolve( response ),
				} )
			)
		);

		const reload = vi.fn();
		vi.spyOn( window, 'location', 'get' ).mockReturnValue( { reload } );

		await import( '../../blocks/live-viewer/view' );

		return reload;
	};

	afterEach( () => {
		vi.useRealTimers();
		vi.unstubAllGlobals();
	} );

	it( 'polls the endpoint and reloads for the next event', async () => {
		const reload = await setUp( { id: 9, should_refresh: true } );

		expect( fetch ).not.toHaveBeenCalled();
		await vi.advanceTimersByTimeAsync( 1000 );

		expect( fetch ).toHaveBeenCalledWith(
			'https://example.org/wp-json/eclw/v1/next-video',
			expect.any( Object )
		);
		expect( reload ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'keeps polling while nothing changes', async () => {
		const reload = await setUp( { id: 9, should_refresh: false } );

		await vi.advanceTimersByTimeAsync( 3000 );

		expect( fetch ).toHaveBeenCalledTimes( 3 );
		expect( reload ).not.toHaveBeenCalled();
	} );

	it( 'keeps polling after a failed request', async () => {
		const reload = await setUp( { id: 9, should_refresh: false } );
		fetch.mockRejectedValueOnce( new Error( 'offline' ) );

		await vi.advanceTimersByTimeAsync( 2000 );

		expect( fetch ).toHaveBeenCalledTimes( 2 );
		expect( reload ).not.toHaveBeenCalled();
	} );

	it( 'does not poll while the placeholder is shown', async () => {
		await setUp( { id: 9, should_refresh: true }, '0' );

		await vi.advanceTimersByTimeAsync( 3000 );

		expect( fetch ).not.toHaveBeenCalled();
	} );
} );
