let mix = require("laravel-mix");
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.browserSync({
	proxy: "dudu.test"
});

mix.webpackConfig(webpack => {
	return {
		module: {
			rules: [
				{
					enforce: "pre",
					test: /\.(js|vue)$/,
					loader: "eslint-loader",
					exclude: /node_modules/
				}
			],

		},
		plugins: [
			new webpack.ProvidePlugin({
				"Quill": "quill/dist/quill.js",
				"window.Quill": "quill/dist/quill.js"
			})
		]
	};
});

// mix.autoload({
// 	windowQuill: ['window.Quill', 'quill/dist/quill.js'],
// 	Quill:['Quill',"quill/dist/quill.js"]
// });

mix.js("resources/assets/js/app.js", "public/js")
	.sass("resources/assets/sass/app.scss", "public/css");

// 版本化，避免浏览器缓存影响
if(mix.inProduction()) {
	mix.version();
}
