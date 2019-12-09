var Encore = require('@symfony/webpack-encore');
var webpack = require('webpack');
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');
const rootImport = require('babel-plugin-root-import');
require('webpack');

/**
const PurgecssPlugin = require('purgecss-webpack-plugin')
var glob = require("glob-all")
/**/

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/assets')
    .setPublicPath('/assets')

    .addEntry('app', './assets/main.js')

    //.splitEntryChunks()
    //.enableSingleRuntimeChunk() normally, don't need by default

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableSassLoader()
    .disableSingleRuntimeChunk()
    .enableVersioning(true)
    .configureBabel((babelConfig) => {
            babelConfig.plugins.push('babel-plugin-root-import')
        }, {
        useBuiltIns: 'usage',
        corejs: 3
    })

    .copyFiles({
        from: './assets/icon',
        pattern: /.*/,
        to: '[path]../[name].[ext]'
    })
    .copyFiles({
        from: './node_modules/piedweb-tyrol-free-bootstrap-4-theme/src/img',
        pattern: /.*title\.(png|jpg|jpeg)$/,
        to: '[path][name].[ext]'
    })
    .addPlugin(new webpack.ProvidePlugin(new UglifyJSPlugin()))
    /**
    .addPlugin(new PurgecssPlugin({
        paths: glob.sync([
            'static/*.html',
            'vendor/piedweb/cms-bundle/src/Resources/views/** /*.html.twig',
            'templates/** /*.html.twig',
        ]),
        whitelistPatternsChildren: [
            /baguette/, /form/, /col/, /pt/, /mt/, ,/pb/, /mb/, /\.p-/, /\.m-/, /fullwidth/, /turbolinks/
        ]
    }))/**/
    .configureFilenames({
         js: '[name].js',
         css: '[name].css',
         images: 'img/[name].[ext]',
         fonts: 'fonts/[hash:8].[name].[ext]'
    })
;

module.exports = Encore.getWebpackConfig();
