<?php
// This file is generated. Do not modify it manually.
return array(
	'live-viewer' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'bm/live-viewer',
		'title' => 'Live Viewer',
		'icon' => 'video-alt',
		'description' => 'A live player for The Events Calendar.',
		'category' => 'media',
		'textdomain' => 'events-calendar-live-viewer',
		'keywords' => array(
			'events',
			'video',
			'live'
		),
		'attributes' => array(
			
		),
		'supports' => array(
			'anchor' => true,
			'html' => false
		),
		'editorScript' => 'file:../../dist/blocks/live-viewer.js',
		'viewScriptModule' => 'file:../../dist/blocks/live-viewer/view.js',
		'render' => 'file:./render.php'
	)
);
