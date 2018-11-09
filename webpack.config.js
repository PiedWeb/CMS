var Encore = require('@symfony/webpack-encore');
var webpack = require('webpack');
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');
require('webpack');

const webpack = require('webpack');
const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CleanWebpackPlugin = require('clean-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin')
const FaviconsWebpackPlugin = require('favicons-webpack-plugin')
const WebpackPwaManifest = require('webpack-pwa-manifest')

Encore
    .setOutputPath('./public/assets')
    .setPublicPath('/public/assets')
    .cleanupOutputBeforeBuild()
    .enableSassLoader()
    //.enableVersioning(false)
    .addEntry('app', './src/Resources/public/main.js')
    .addPlugin(new webpack.ProvidePlugin(new UglifyJSPlugin()))
    //.addEntry('banner-bg', './assets/img/g/default-banner-xl.jpg')
    .configureFilenames({
         js: '[name].js',
         css: '[name].css',
         images: 'img/[name].[ext]',
         fonts: 'fonts/[hash:8].[name].[ext]'
    })
;

if (Encore.isProduction()) {
     //Encore.setPublicPath('https://static.myserver.com/');
     //Encore.setManifestKeyPrefix('build/');
}

const mainConfig = Encore.getWebpackConfig();
frontEndConfig.name = 'main';

module.exports = [mainConfig];
