const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const fastGlob = require( 'fast-glob' );
const WebpackBar = require( 'webpackbar' );

/*
 * Matches a block's name in paths in the form
 * ./blocks/<blockName>/index.js
 */
const blockNameRegex = new RegExp( /(?<=blocks\/).*(?=(\/index))/g );

/*
 * One entry point per block editor script, named after the block's directory.
 */
const createBlockEntrypoints = () =>
	fastGlob.sync( './blocks/**/index.js' ).reduce( ( entries, scriptPath ) => {
		const [ blockName ] = scriptPath.match( blockNameRegex );

		return {
			...entries,
			[ blockName ]: scriptPath,
		};
	}, {} );

const blocks = {
	...defaultConfig,
	entry: createBlockEntrypoints(),
	output: {
		path: path.resolve( process.cwd(), 'dist', 'blocks' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
	plugins: [
		...defaultConfig.plugins,
		new WebpackBar( {
			name: 'Blocks',
			color: '#cd1cec',
		} ),
	],
};

const publicScripts = {
	...defaultConfig,
	entry: {
		'live-player': './assets/scripts/src/live-player.js',
	},
	output: {
		path: path.resolve( process.cwd(), 'dist', 'public' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
	plugins: [
		...defaultConfig.plugins,
		new WebpackBar( {
			name: 'Public',
			color: '#5aec1c',
		} ),
	],
};

module.exports = [ publicScripts, blocks ];
