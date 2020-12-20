var Encore = require('@symfony/webpack-encore');

Encore.setOutputPath('./src/Resources/public/')
  .setPublicPath('./')
  .setManifestKeyPrefix('bundles/piedwebcms')

  .cleanupOutputBeforeBuild()
  .enableSassLoader()
  .enableSourceMaps(false)
  .enableVersioning(false)
  .disableSingleRuntimeChunk()
  .copyFiles({
    from: './node_modules/ace-builds/src-min-noconflict/',
    // relative to the output dir
    to: 'ace/[name].[ext]',
    // only copy files matching this pattern
    pattern: /\.js$/,
  })
  .addEntry('admin', './src/Extension/Admin/assets/admin.js') // {{ asset('bundles/piedwebcms/admin.js') }}
  .addEntry('page', './src/Resources/assets/page.js'); // {{ asset('bundles/piedwebcms/page.js') }}

module.exports = Encore.getWebpackConfig();
