//import 'alpinejs';

require('fslightbox');

import {
  decryptLink,
  readableEmail,
  convertImageLinkToWebPLink,
  replaceOn,
} from './helpers';

function onPageLoaded() {
  onDomChanged();
  new FsLightbox();
}

function onDomChanged() {
  convertImageLinkToWebPLink();
  decryptLink();
  readableEmail('.cea');
  replaceOn();
  refreshFsLightbox();
}

document.addEventListener('DOMContentLoaded', onPageLoaded());

document.addEventListener('DOMChanged', onDomChanged);
