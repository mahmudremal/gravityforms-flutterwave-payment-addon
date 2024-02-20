/**
 * Webpack configuration.
 */
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const cssnano = require('cssnano'); // https://cssnano.co/
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
// const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
// var UglifyJS = require("uglify-es");
const CopyPlugin = require('copy-webpack-plugin'); // https://webpack.js.org/plugins/copy-webpack-plugin/
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const TerserPlugin = require("terser-webpack-plugin");
// JS Directory path.
const JS_DIR = path.resolve(__dirname, 'src/js');
const IMG_DIR = path.resolve(__dirname, 'src/img');
const LIB_DIR = path.resolve(__dirname, 'src/library');
const BUILD_DIR = path.resolve(__dirname, 'build');
const SRC_DIR = path.resolve(__dirname, 'src');
const entry = {
	public: JS_DIR + '/public.js',
	admin: JS_DIR + '/admin.js',
	// single: JS_DIR + '/single.js',
	// editor: JS_DIR + '/editor.js',
	// blocks: JS_DIR + '/blocks.js',
	// author: JS_DIR + '/author.js',
	// search: JS_DIR + '/search.js',
};
const output = {
	path: BUILD_DIR,
	filename: 'js/[name].js',
};
/**
 * Note: argv.mode will return 'development' or 'production'.
 */
const plugins = (argv) => [
	new CleanWebpackPlugin({
		cleanStaleWebpackAssets: ('production' === argv.mode ) // Automatically remove all unused webpack assets on rebuild, when set to true in production. (https://www.npmjs.com/package/clean-webpack-plugin#options-and-defaults-optional)
	}),
	new MiniCssExtractPlugin({
		filename: 'css/[name].css'
	}),
	new CopyPlugin({
		patterns: [
			{from: LIB_DIR, to: BUILD_DIR + '/library'},
			{from: SRC_DIR + '/icons', to: BUILD_DIR + '/icons'},
		]
	}),
	new DependencyExtractionWebpackPlugin({
		injectPolyfill: true,
		combineAssets: true,
	})
];
const rules = [
	{
		test: /\.js$/,
		include: [ JS_DIR ],
		exclude: /node_modules/,
		use: 'babel-loader'
	},
	{
		// test: /\.scss$/,
		test: /\.(css|scss)$/,
		exclude: /node_modules/,
		use: [
			MiniCssExtractPlugin.loader,
			'css-loader',
			'sass-loader',
		]
	},
	{
		test: /\.(png|jpg|svg|jpeg|gif|ico)$/,
		use: {
			loader: 'file-loader',
			options: {
				name: '[path][name].[ext]',
				publicPath: 'production' === process.env.NODE_ENV ? '../' : '../../'
			}
		}
	},
	{
		test: /\.(ttf|otf|eot|svg|woff(2)?)(\?[a-z0-9]+)?$/,
		exclude: [ IMG_DIR, /node_modules/ ],
		use: {
			loader: 'file-loader',
			options: {
				name: '[path][name].[ext]',
				publicPath: 'production' === process.env.NODE_ENV ? '../' : '../../'
			}
		}
	},
	{
		test: /\.twig$/,
		exclude: [ IMG_DIR, /node_modules/ ],
		use: {
			loader: 'twig-loader',
			// options: {
			// 	// See options section below
			// 	autoescape: true
			// },
		}
	},
	{
        test: /\.txt$/i,
        use: [
          {
            loader: 'raw-loader',
            options: {
              esModule: false,
            },
          },
        ],
	},
];
/**
 * Since you may have to disambiguate in your webpack.config.js between development and production builds,
 * you can export a function from your webpack configuration instead of exporting an object
 *
 * @param {string} env environment (See the environment options CLI documentation for syntax examples. https://webpack.js.org/api/cli/#environment-options)
 * @param argv options map (This describes the options passed to webpack, with keys such as output-filename and optimize-minimize)
 * @return {{output: *, devtool: string, entry: *, optimization: {minimizer: [*, *]}, plugins: *, module: {rules: *}, externals: {jquery: string}}}
 *
 * @see https://webpack.js.org/configuration/configuration-types/#exporting-a-function
 */
module.exports = (env, argv) => ({
	entry: entry,
	output: output,
	/**
	 * A full SourceMap is emitted as a separate file (e.g.  main.js.map)
	 * It adds a reference comment to the bundle so development tools know where to find it.
	 * set this to false if you don't need it
	 */
	devtool: 'source-map',
	module: {
		rules: rules,
	},
	optimization: {
		// minimize: true,
		minimizer: [
			new OptimizeCssAssetsPlugin({
				cssProcessor: cssnano
			}),
			// new UglifyJsPlugin({
			// 	cache: false,
			// 	parallel: true,
			// 	sourceMap: false
			// }),
			new TerserPlugin({
				parallel: true,
				sourceMap: false,
				extractComments: true,
				// test: /\.js(\?.*)?$/i,
				minify: TerserPlugin.uglifyJsMinify,
				terserOptions: {ecma: 6}
			})
		]
	},
	plugins: plugins(argv),
	externals: {
		jquery: 'jQuery'
	},
	// avoids error messages
	// node: {fs: 'empty'}
});
