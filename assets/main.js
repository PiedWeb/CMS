/**
 * Import CSS
 */
require('~/assets/main.scss');

import {
  uncloakLinks,
  readableEmail,
  convertImageLinkToWebPLink,
} from 'piedweb-cms-js-helpers/src/helpers';

function onPageLoaded() {
  uncloakLinks();
  readableEmail('.cea');
  //backgroundLazyLoad(function (src) { return responsiveImage(src) }); // see devoluix-theme
}

function onDomChanged() {
  baguetteBox.run('.mimg', {});
  convertImageLinkToWebPLink();
  uncloakLinks();
  readableEmail('.cea');
}

document.addEventListener('DOMContentLoaded', onPageLoaded());

document.addEventListener('linksBuilt', onDomChanged);
