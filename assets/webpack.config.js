
var Encore = require('@symfony/webpack-encore');
var webpack = require('webpack');
var UglifyJSPlugin = require('uglifyjs-webpack-plugin');

Encore
    .setOutputPath('public/assets')
    .setPublicPath('/assets')
    .cleanupOutputBeforeBuild()
    .enableSassLoader()
    .disableSingleRuntimeChunk()
    .enableVersioning(true)
    .addEntry('app', './assets/main.js')
    .copyFiles({
        from: './assets/icon',
        pattern: /(fav|)icon\.(svg|ico)$/,
        to: '[path][name].[ext]'
    })
    .copyFiles({
        from: './node_modules/piedweb-tyrol-free-bootstrap-4-theme/src/img',
        pattern: /.*title\.(png|jpg|jpeg)$/,
        to: '[path][name].[ext]'
    })
    .addPlugin(new webpack.ProvidePlugin(new UglifyJSPlugin()))
    .configureFilenames({
         js: '[name].js',
         css: '[name].css',
         images: 'img/[name].[ext]',
         fonts: 'fonts/[hash:8].[name].[ext]'
    })
;

// Configure this part if you use a CDN
//if (Encore.isProduction()) {
     //Encore.setPublicPath('https://static.myserver.com/');
     //Encore.setManifestKeyPrefix('build/');
//}

module.exports = [Encore.getWebpackConfig()];
