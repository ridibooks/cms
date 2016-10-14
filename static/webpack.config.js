const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = {
  entry: {
    tags: './js/app/tags.jsx',
    users: './js/app/users.jsx'
  },
  output: {
    path: path.join(__dirname, 'dist'),
    filename: '[name].js',
  },
  resolve: {
    modulesDirectories: ['node_modules', 'bower_components'],
    extensions: ['', '.js', '.jsx', '.elm']
  },
  module: {
    loaders: [
      {
        test: /\.jsx$/,
        loader: 'babel',
        query: {
          presets: ['es2015', 'react']
        },
        exclude: [/elm-stuff/, /node_modules/, /bower_components/]
      },
      {
        test: /\.elm$/,
        exclude: [/elm-stuff/, /node_modules/, /bower_components/],
        loader: 'elm-webpack'
      },
      {
        test: /\.css$/,
        loader: ExtractTextPlugin.extract('style-loader', 'css-loader'),
      },
      {
        test: /\.(woff|woff2|eot|ttf|svg)$/,
        loader: 'url'
      }
    ],

    noParse: /\.elm$/
  },
  plugins: [
    new webpack.optimize.CommonsChunkPlugin({
      name: 'commons',
      chunks: ['users', 'tags'],
      minChunks: 2
    }),
    new webpack.ResolverPlugin(
      new webpack.ResolverPlugin.DirectoryDescriptionFilePlugin(".bower.json", ["main"])
    ),
    new webpack.ProvidePlugin({
      $: "jquery",
      jQuery: "jquery"
    }),
    new ExtractTextPlugin("styles.css")
  ]
};
