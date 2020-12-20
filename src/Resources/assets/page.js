/**
 * Import CSS
 */
require('./page.scss');

import {
  uncloakLinks,
  readableEmail,
  convertImageLinkToWebPLink,
  replaceOn,
} from 'piedweb-cms-js-helpers/src/helpers';

import GLightbox from 'glightbox';

function onPageLoaded() {
  onDomChanged();
}

function onDomChanged() {
  GLightbox({ autoplayVideos: true });
  convertImageLinkToWebPLink();
  uncloakLinks();
  readableEmail('.cea');
  replaceOn();
}

document.addEventListener('DOMContentLoaded', onPageLoaded());

document.addEventListener('linksBuilt', onDomChanged);
