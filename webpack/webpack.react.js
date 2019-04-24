var debug = process.env.NODE_ENV !== 'production'
var webpack = require('webpack')
var path = require('path')
var BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin
var ExtractTextPlugin = require('extract-text-webpack-plugin')

/**
 * Internal dependencies
 */
const camelCaseDash = (string) => {
  return string.replace(
    /-([a-z])/g,
    (match, letter) => letter.toUpperCase()
  )
}

/**
 * Converts @wordpress/* string request into request object.
 *
 * Note this isn't the same as camel case because of the
 * way that numbers don't trigger the capitalized next letter.
 *
 * @example
 * formatRequest( '@wordpress/api-fetch' );
 * // { this: [ 'wp', 'apiFetch' ] }
 * formatRequest( '@wordpress/i18n' );
 * // { this: [ 'wp', 'i18n' ] }
 *
 * @param {string} request Request name from import statement.
 * @return {Object} Request object formatted for further processing.
 */
const formatRequest = (request) => {
  // '@wordpress/api-fetch' -> [ '@wordpress', 'api-fetch' ]
  const [, name] = request.split('/')

  // { this: [ 'wp', 'apiFetch' ] }
  return {
    this: ['wp', camelCaseDash(name)],
  }
}

const wordpressExternals = (context, request, callback) => {
  if (/^@wordpress\//.test(request)) {
    callback(null, formatRequest(request), 'this')
  } else {
    callback()
  }
}

const externals = [
  {
    react: 'React',
    'react-dom': 'ReactDOM',
    moment: 'moment',
    jquery: 'jQuery',
    lodash: 'lodash',
    'lodash-es': 'lodash',

    // Distributed NPM packages may depend on Babel's runtime regenerator.
    // In a WordPress context, the regenerator is assigned to the global
    // scope via the `wp-polyfill` script. It is reassigned here as an
    // externals to reduce the size of generated bundles.
    //
    // See: https://github.com/WordPress/gutenberg/issues/13890
    '@babel/runtime/regenerator': 'regeneratorRuntime',
  },
  wordpressExternals,
]

const dir = __dirname + '/../'

let config = {
  context: dir,
  entry: {
    'gutenberg-block': './js/src/modules/gutenberg-block/index.js',
  },
  output: {
    path: dir + 'js/build',
    filename: '[name].min.js',
  },
  devtool: debug ? 'inline-sourcemap' : false,
  module: {
    rules: [
      {
        test: /\.(scss|sass)$/,
        use: ExtractTextPlugin.extract({
          fallback: 'style-loader',
          use: [
            {loader: 'css-loader', options: {minimize: true}},
            'fast-sass-loader'
          ]
        })
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        exclude: /(node_modules|bower_components)/,
        options: {
          babelrc: false,
          "plugins": [
            ["transform-react-jsx", {
              pragma: "wp.element.createElement"
            }]
          ],
          "presets": [
            ["env", {"loose": true, "modules": false}],
            ["es2015", {"loose": true, "modules": false}],
            "stage-2"
          ],
        }
      }
    ]
  },
  resolve: {
    alias: {
      '@rebelcode/std-lib': '@rebelcode/std-lib/dist/std-lib.umd.js',
      'lodash-es': 'lodash',
      app: path.resolve(dir, 'js/src'),
      css: path.resolve(dir, 'css'),
    },
    extensions: ['*', '.js', '.json']
  },
  externals,
  plugins: [
    new ExtractTextPlugin(debug ? './../../css/build/[name].min.css' : { // define where to save the file
      filename: './../../css/build/[name].min.css',
      allChunks: true
    }),
    new webpack.optimize.ModuleConcatenationPlugin(),
    !debug && new webpack.optimize.UglifyJsPlugin({
      sourcemap: false,
      compress: {
        warnings: false,
        pure_funcs: [
          'console.log', 'console.info', 'console.warn'
        ]
      },
    }),
    !debug && new webpack.LoaderOptionsPlugin({
      minimize: true
    })
  ].filter( Boolean )
}

module.exports = config
