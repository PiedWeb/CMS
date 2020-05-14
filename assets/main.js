/**
 * Import CSS
 */
require("~/assets/main.scss");

import {
  getBlockFromSky,
  formToSky,
  convertImgLinkToResponsiveImgLink
} from "~/node_modules/piedweb-cms-js-helpers/src/helpers.js";

import {
  convertInLinks,
  convertInLinksFromRot13,
  convertImageLinkToWebPLink,
  allClickable,
  readableEmail
} from "~/node_modules/piedweb-tyrol-free-bootstrap-4-theme/src/js/helpers.js";


function onDomLoaded() {
  convertInLinks();
  convertInLinksFromRot13();
  getBlockFromSky();
  readableEmail(".cea");
  formToSky();
  convertImgLinkToResponsiveImgLink();
}

document.addEventListener("DOMContentLoaded", function() {
  onDomLoaded();
  convertImageLinkToWebPLink();
});
