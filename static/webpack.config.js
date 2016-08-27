var path = require('path');
var webpack = require('webpack');

module.exports = {
  entry: {
    users: './js/app/users.js',
    tags: './js/app/tags.js'
  },
  output: {
    path: path.join(__dirname, 'dist'),
    filename: '[name].js',
  },
  resolve: {
    modulesDirectories: ['node_modules', 'bower_components']
  },
  module: {
    loaders: [
      {
        test: /\.js$/,
        loader: 'babel',
        query: {
          presets: ['es2015', 'react']
        },
        exclude: /(node_modules|bower_components)/
      },
      {
        test: /\.(woff|woff2|eot|ttf|svg)$/,
        loader: 'url'
      }
    ]
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
    })
  ]
};
