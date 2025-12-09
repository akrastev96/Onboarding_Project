const path = require('path');
const glob = require("glob");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const ImageminPlugin = require('imagemin-webpack-plugin').default

module.exports = (env) => {
	
	let entryJsAdmin, entryJsPublic;

	if ( env.bundlejs === 'true' ) {
		entryJsAdmin = {
			'public/js/bundle': glob.sync(path.resolve(__dirname, './assets/src/public/js/**/*.js'))
		};

		entryJsPublic = {
			'admin/js/admin-bundle': glob.sync(path.resolve(__dirname, './assets/src/admin/js/**/*.js'))
		};
	} else {
		entryJsAdmin = glob
			.sync(path.resolve(__dirname, 'assets/src/admin/**/*.js'))
			.reduce((x, y) => Object.assign(x, {
				['admin/js/' + path.basename(y, ".js")]: y,
			}), {});

		entryJsPublic = glob
			.sync(path.resolve(__dirname, 'assets/src/public/**/*.js'))
			.reduce((x, y) => Object.assign(x, {
				['public/js/' + path.basename(y, ".js")]: y,
			}), {});
	}
	
	return {
		entry: {
			...entryJsAdmin,
			...entryJsPublic,
			'public/css/master': glob.sync(path.resolve(__dirname, './assets/src/public/sass/*')),
			'admin/css/admin-master': glob.sync(path.resolve(__dirname, './assets/src/admin/sass/*')),
		},
		output: {
			path: path.join(__dirname, './assets/dist'),
			filename: '[name].min.js'
		},
		plugins: [
			new RemoveEmptyScriptsPlugin(),
			new MiniCssExtractPlugin({
				filename: '[name].css'
			}),
			// new ImageminPlugin({
			// 	pngquant: {
			// 		quality: '80'
			// 	},
			// 	externalImages: {
			// 	  context: '.', // Important! This tells the plugin where to "base" the paths at
			// 	  sources: glob.sync(path.resolve(__dirname, './assets/src/images/*')),
			// 	  destination: path.resolve(__dirname,'./assets/dist/images' ),
			// 	  fileName: '[name].[ext]' // (filePath) => filePath.replace('jpg', 'webp') is also possible
			// 	}
			// }),
		],
		module: {
			rules: [
			{
				test: /\.m?js$/,
				exclude: /(node_modules|bower_components)/,
				use: {
				loader: 'babel-loader',
				options: {
					presets: ['@babel/preset-env']
				}
				}
			},
			{
				test: /\.s?[c]ss$/i,
				exclude: /(node_modules|bower_components)/,
				use: [
				MiniCssExtractPlugin.loader,
				'css-loader',
				'sass-loader'
				]
			},
			{
				test: /\.sass$/i,
				exclude: /(node_modules|bower_components)/,
				use: [
				MiniCssExtractPlugin.loader,
				'css-loader',
				{
					loader: 'sass-loader',
					options: {
					sassOptions: { indentedSyntax: true }
					}
				}
				]
			},
			]
		},
		optimization: {
			minimizer: [
			// For webpack@5 you can use the `...` syntax to extend existing minimizers (i.e. `terser-webpack-plugin`), uncomment the next line
			`...`,
			new CssMinimizerPlugin(),
			],
		},
	}
};
