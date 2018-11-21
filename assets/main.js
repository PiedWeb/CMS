/**
 * File from piedweb-tyrol-free-bootstrap-4-theme
 * Feel Free to edit it, it's yours now
 */
/**
 * Import CSS
 */
require("~/node_modules/piedweb-tyrol-free-bootstrap-4-theme/src/scss/main.scss");

//import BootstrapCookieConsent from "bootstrap-cookie-consent";

import baguetteBox from "baguettebox.js";

var bsn = require("bootstrap.native/dist/bootstrap-native-v4");

import {
  convertImgLinkToResponsiveImgLink,
  responsiveImage
} from "~/node_modules/piedweb-tyrol-free-bootstrap-4-theme/src/js/helpers-pwcms.js";

import {
  getBlockFromSky,
  formToSky
} from "~/vendor/piedweb/cms-bundle/src/Resources/assets/helpers.js";

import {
  imgLazyLoad,
  backgroundLazyLoad,
  convertInLinks,
  convertInLinksFromRot13,
  clickable,
  resizeWithScreenHeight,
  wideImgCentered,
  smoothScroll,
  rot13ToText,
  readableEmail,
  applySmoothScroll,
  addAClassOnScroll,
  allClickable
} from "~/node_modules/piedweb-tyrol-free-bootstrap-4-theme/src/js/helpers.js";

function onPageLoaded() {
  allClickable(".clickable");
  imgLazyLoad();
  convertInLinks();
  baguetteBox.run(".mimg", {
    captions: function(element) {
      return element.getElementsByTagName("img")[0].alt;
    }
  });
  getBlockFromSky();
}
// onDomLoaded fire before onPageLoeded
function onDomLoaded() {
  readableEmail(".cea");
  backgroundLazyLoad();
  applySmoothScroll();
  formToSky();
}

document.addEventListener("DOMContentLoaded", function() {
  onDomLoaded();
});

window.onload = function () {
    addAClassOnScroll(".navbar", "nostick", 50);
    onPageLoaded();
  /**
  new BootstrapCookieConsent({
    services: ["StatistiquesAnonymes", "YouTube"],
    services_descr: {
      StatistiquesAnonymes:
        "Nous permet d'améliorer le site en fonction de son utilisation",
      YouTube: "Affiche les vidéos du service youtube.com"
    },
    method: "bsn"
  });
  /**/
}
