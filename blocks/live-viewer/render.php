<?php
/**
 * Server-side render for the Live Viewer block.
 *
 * @package BernskioldMedia\WP\EventsCalendarLiveViewer
 *
 * @var array $attributes The block attributes.
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped while rendering.
echo \BernskioldMedia\WP\EventsCalendarLiveViewer\Blocks\Live_Viewer::render( $attributes );
