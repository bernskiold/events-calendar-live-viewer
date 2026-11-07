const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );
const fastGlob = require( 'fast-glob' );
const WebpackBar = require( 'webpackbar' );

/*
 * `defaultConfig.plugins` holds shared plugin *instances*. The view module
 * build runs in module mode, which would flip a shared
 * DependencyExtractionWebpackPlugin into module externals for the editor
 * build too. Give each config its own instance.
 */
const withFreshExternals = ( plugins ) =>
	plugins.map( ( plugin ) =>
		plugin?.constructor?.name === 'DependencyExtractionWebpackPlugin'
			? new DependencyExtractionWebpackPlugin()
			: plugin
	);

/*
 * One entry point per block file of the given name, keyed by the block's
 * directory: ./blocks/<blockName>/<file>.js.
 */
const createEntrypoints = ( file, nameFor ) =>
	fastGlob
		.sync( `./blocks/*/${ file }.js` )
		.reduce( ( entries, scriptPath ) => {
			const blockName = path.basename( path.dirname( scriptPath ) );

			return {
				...entries,
				[ nameFor( blockName ) ]: scriptPath,
			};
		}, {} );

/*
 * Block editor scripts, loaded through `editorScript` in block.json.
 */
const blocks = {
	...defaultConfig,
	entry: createEntrypoints( 'index', ( name ) => name ),
	output: {
		path: path.resolve( process.cwd(), 'dist', 'blocks' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
	plugins: [
		...withFreshExternals( defaultConfig.plugins ),
		new WebpackBar( {
			name: 'Blocks',
			color: '#cd1cec',
		} ),
	],
};

/*
 * Front-end block code, built as ES modules for `viewScriptModule`.
 */
const blockViewModules = {
	...defaultConfig,
	entry: createEntrypoints( 'view', ( name ) => `${ name }/view` ),
	output: {
		path: path.resolve( process.cwd(), 'dist', 'blocks' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
		module: true,
		library: { type: 'module' },
		environment: { module: true, dynamicImport: true },
	},
	experiments: {
		...( defaultConfig.experiments || {} ),
		outputModule: true,
	},
	externalsType: 'module',
	plugins: [
		...withFreshExternals( defaultConfig.plugins ),
		new WebpackBar( {
			name: 'Block Views',
			color: '#5aec1c',
		} ),
	],
};

module.exports = [ blocks, blockViewModules ];
