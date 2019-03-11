var debug = process.env.NODE_ENV !== 'production'
var webpack = require('webpack')
var BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin
var ExtractTextPlugin = require('extract-text-webpack-plugin')
var VueLoaderPlugin = require('vue-loader/lib/plugin')

function makePlugins (plugins) {
  let base = [
    new webpack.optimize.CommonsChunkPlugin({
      name: 'wpra-vendor',
      filename: 'wpra-vendor.min.js',
      minChunks: function(module){
        return module.context && module.context.includes('node_modules');
      }
    }),
    new webpack.optimize.CommonsChunkPlugin({
      name: 'wpra-manifest',
      filename: 'wpra-manifest.min.js',
      minChunks: Infinity
    }),
  ]
  return base.concat(plugins)
}

let config = {
  context: __dirname,
  entry: {
    intro: './js/src/intro/index.js',
    plugins: './js/src/plugins/index.js',
    templates: './js/src/templates/index.js',
    update: './css/src/update/index.scss',
  },
  output: {
    path: __dirname + '/js',
    filename: '[name].min.js',
    library: 'WPRA',
    libraryTarget: 'umd'
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
        test: /\.vue$/,
        loader: 'vue-loader',
      },
      {
        test: /\.html$/,
        loader: 'vue-template-compiler-loader'
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        exclude: /(node_modules|bower_components)/,
      }
    ]
  },
  resolve: {
    alias: {
      'vue$': 'vue/dist/vue.esm.js',
      '@rebelcode/std-lib': '@rebelcode/std-lib/dist/std-lib.umd.js',
    },
    extensions: ['*', '.js', '.vue', '.json']
  },
  plugins: debug ? makePlugins([
    new ExtractTextPlugin('./../css/[name].min.css'),
    new VueLoaderPlugin(),
  ]) : makePlugins([
    new ExtractTextPlugin({ // define where to save the file
      filename: './../css/[name].min.css',
      allChunks: true
    }),
    new VueLoaderPlugin(),
    new webpack.optimize.ModuleConcatenationPlugin(),
    // new webpack.optimize.OccurrenceOrderPlugin(),
    new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
    // new BundleAnalyzerPlugin(),
    new webpack.optimize.UglifyJsPlugin({
      sourcemap: false,
      compress: {
        warnings: false,
        pure_funcs: [
          'console.log', 'console.info', 'console.warn'
        ]
      },
    }),
    new webpack.LoaderOptionsPlugin({
      minimize: true
    })
  ])
}

module.exports = config
