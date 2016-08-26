//var BowerWebpackPlugin = require("bower-webpack-plugin");

module.exports = {
  entry: './js/app.js',
  output: {
    path: './dist',
    filename: 'app.bundle.js'
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
  //plugins: [new BowerWebpackPlugin()]
};
