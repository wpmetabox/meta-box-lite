const path = require( 'path' );
const webpack = require( 'webpack' );

// https://www.cssigniter.com/how-to-use-external-react-components-in-your-gutenberg-blocks/
const externals = {
	react: 'React',
	'react-dom': 'ReactDOM',
	'@wordpress/i18n': 'wp.i18n',
	'@wordpress/element': 'wp.element',
	'@wordpress/components': 'wp.components',
	'@wordpress/compose': 'wp.compose',
	'jquery': 'jQuery',
	'jquery-ui': 'jQuery',
	codemirror: 'wp.CodeMirror',
	clipboard: 'ClipboardJS',
};

const commonModules = {
	rules: [
		{
			test: /\.js$/,
			exclude: /node_modules/,
			use: {
				loader: 'babel-loader',
				options: {
					plugins: [ '@babel/plugin-transform-react-jsx' ]
				}
			}
		},
	]
};

const resolve = {
	roots: [ path.resolve( 'app' ) ]
};

const plugins = [
	new webpack.optimize.LimitChunkCountPlugin( {
		maxChunks: 1
	} )
];

// Main Meta Box Builder app.
const main = {
	entry: './app/App.js',
	output: {
		path: path.resolve( 'assets/js' ),
		filename: 'app.js'
	},
	externals,
	resolve,
	plugins,
	module: commonModules
};

// Settings page app.
const settingsPage = {
	entry: './modules/settings-page/app/App.js',
	output: {
		path: path.resolve( 'modules/settings-page/assets' ),
		filename: 'settings-page.js'
	},
	externals,
	resolve,
	plugins,
	module: commonModules
};

// Relationships app.
const relationships = {
	entry: './modules/relationships/app/App.js',
	output: {
		path: path.resolve( 'modules/relationships/assets' ),
		filename: 'relationships.js'
	},
	externals,
	resolve,
	plugins,
	module: commonModules
};

module.exports = [ main, settingsPage, relationships ];