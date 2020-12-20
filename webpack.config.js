var Encore = require('@symfony/webpack-encore');
var PurgeCssPlugin = require('purgecss-webpack-plugin');
var glob = require('glob-all');

Encore.setOutputPath('./src/Resources/public/')
  .setPublicPath('./')
  .setManifestKeyPrefix('bundles/piedwebcms')

  .cleanupOutputBeforeBuild()
  .enableSassLoader()
  .enableSourceMaps(false)
  .enableVersioning(false)
  .enablePostCssLoader((options) => {
    options.postcssOptions = {
      // the directory where the postcss.config.js file is stored
      path: 'postcss.config.js',
    };
  })
  .disableSingleRuntimeChunk()
  .copyFiles({
    from: './node_modules/ace-builds/src-min-noconflict/',
    // relative to the output dir
    to: 'ace/[name].[ext]',
    // only copy files matching this pattern
    pattern: /\.js$/,
  })
  .addEntry('admin', './src/Extension/Admin/assets/admin.js') // {{ asset('bundles/piedwebcms/admin.js') }}
  .addEntry('page', './src/Resources/assets/page.js') // {{ asset('bundles/piedwebcms/page.js') }}
  .addStyleEntry('tailwind', './src/Resources/assets/tailwind.css');

if (Encore.isProduction()) {
  Encore.addPlugin(
    new PurgeCssPlugin({
      paths: glob.sync([
        'src/Resources/views/**/*.html.twig',
        'src/Extension/Admin/views/*.html.twig',
      ]),
      defaultExtractor: (content) => {
        return content.match(/[\w-/:]+(?<!:)/g) || [];
      },
    })
  );
}

module.exports = Encore.getWebpackConfig();
