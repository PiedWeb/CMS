/**
 * Import CSS
 */
require("~/assets/main.scss");

/**
 * Create JS
 *
 * You can find two functions :
 * - onPageLoaded
 * - onDomLoaded
 * The second one is called each time we change something in the DOM (for example in getBlockFromSky)
 * // Todo: check if getBlockFromSky can call onDomLoaded
 */
//import BootstrapCookieConsent from "bootstrap-cookie-consent";

import baguetteBox from "baguettebox.js";

var bsn = require("bootstrap.native/dist/bootstrap-native-v4");

import {
  getBlockFromSky,
  formToSky,
  convertImgLinkToResponsiveImgLink
} from "~/node_modules/piedweb-cms-js-helpers/src/helpers.js";

import {
  fixedNavBar,
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


function onDomLoaded() {
  allClickable(".clickable");
  imgLazyLoad();
  convertInLinks();
  convertInLinksFromRot13();
  baguetteBox.run(".mimg", {
    captions: function(element) {
      return element.getElementsByTagName("img")[0].alt;
    }
  });
  getBlockFromSky();
  addAClassOnScroll(".navbar", "nostick", 50);
  fixedNavBar();
  readableEmail(".cea");
  backgroundLazyLoad();
  applySmoothScroll();
  formToSky();
  convertImgLinkToResponsiveImgLink();
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

function onDomChanged()
{
    baguetteBox.run(".mimg", {});
}

document.addEventListener("DOMContentLoaded", function() {
  onDomLoaded();
});

document.addEventListener('linksBuilt', onDomChanged);
