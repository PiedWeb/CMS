import 'core-js/stable';
import 'regenerator-runtime/runtime';
/**
 * List of all functions
 *
 * - liveBlock(attr)
 * - liveForm(attr)
 *
 * -  seasonedBackground
 * - responsiveImage(string)    Relative to Liip filters
 * - uncloakLinks(attr)
 * - convertFormFromRot13(attr)
 * - readableEmail(attr)
 * - convertImageLinkToWebPLink()
 */

/**
 * Live Block Watcher (and button)
 *
 * Fetch (ajax) function permitting to get block via a POST request
 *
 * @param {string} attribute
 */
export function liveBlock(
  liveBlockAttribute = 'data-live',
  liveFormSelector = '.live-form'
) {
  var btnToBlock = function (event, btn) {
    btn.setAttribute(
      liveBlockAttribute,
      btn.getAttribute('src-' + liveBlockAttribute)
    );
    getLiveBlock(btn);
  };

  var getLiveBlock = function (item) {
    fetch(item.getAttribute(liveBlockAttribute), {
      //headers: { "Content-Type": "application/json", Accept: "text/plain" },
      method: 'POST',
      credentials: 'include',
    })
      .then(function (response) {
        return response.text();
      })
      .then(function (body) {
        item.removeAttribute(liveBlockAttribute);
        item.outerHTML = body;
      })
      .then(function () {
        document.dispatchEvent(new Event('DOMChanged'));
      });
  };

  var htmlLoader =
    '<div style="width:1em;height:1em;border: 2px solid #222;border-top-color: #fff;border-radius: 50%;  animation: 1s spin linear infinite;"></div><style>@keyframes spin {from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>';

  var setLoader = function (form) {
    var $submitButton = getSubmitButton(form);
    if ($submitButton !== undefined) {
      var initialButton = $submitButton.outerHTML;
      $submitButton.innerHTML = '';
      $submitButton.outerHTML = htmlLoader;
    }
  };

  var sendForm = function (form, liveFormBlock) {
    setLoader(form);

    var formData = new FormData(form.srcElement);
    fetch(form.srcElement.action, {
      method: 'POST',
      body: formData,
      credentials: 'include',
    })
      .then(function (response) {
        return response.text();
      })
      .then(function (body) {
        liveFormBlock.outerHTML = body;
      })
      .then(function () {
        document.dispatchEvent(new Event('DOMChanged'));
      });
  };

  var getSubmitButton = function (form) {
    if (form.srcElement.querySelector('[type=submit]') !== null) {
      return form.srcElement.querySelector('[type=submit]');
    }
    if (form.srcElement.getElementsByTagName('button') !== null) {
      return form.srcElement.getElementsByTagName('button')[0];
    }
    return null;
  };

  // Listen data-live
  document.querySelectorAll('[' + liveBlockAttribute + ']').forEach((item) => {
    getLiveBlock(item);
  });

  // Listen button src-data-live
  document
    .querySelectorAll('[src-' + liveBlockAttribute + ']')
    .forEach((item) => {
      item.addEventListener('click', (event) => {
        btnToBlock(event, item);
      });
    });

  // Listen live-form
  document.querySelectorAll(liveFormSelector).forEach((item) => {
    if (item.querySelector('form') !== null) {
      item.querySelector('form').addEventListener('submit', (e) => {
        e.preventDefault();
        sendForm(e, item);
      });
    }
  });
}

/**
 * Block to replace Watcher
 * On $event on element find via $attribute, set attribute's content in element.innerHTML
 */
export function replaceOn(attribute = 'replaceBy', eventName = 'click') {
  var loadVideo = function (element) {
    var content = element.getAttribute(attribute);
    if (
      element.classList.contains('hero-banner-overlay-lg') &&
      element.querySelector('picture') &&
      window.innerWidth < 992
    ) {
      element.querySelector('picture').outerHTML = content;
      element.querySelector('.btn-play').outerHTML = ' ';
      element.style.zIndex = '2000';
    } else {
      element.innerHTML = content;
    }
    element.removeAttribute(attribute);
    document.dispatchEvent(new Event('DOMChanged'));
  };

  document
    .querySelectorAll('[' + attribute + ']:not([listen])')
    .forEach(function (element) {
      element.setAttribute('listen', '');
      element.addEventListener(
        eventName,
        function (event) {
          loadVideo(event.currentTarget); //event.currentTarget;
          element.removeAttribute('listen');
        },
        { once: true }
      );
    });
}

/**
 *
 *
 */
export function seasonedBackground() {
  document.querySelectorAll('[x-hash]').forEach(function (element) {
    if (window.location.hash) {
      if (element.getAttribute('x-hash') == window.location.hash.substring(1)) {
        element.parentNode.parentNode
          .querySelectorAll('img')
          .forEach(function (img) {
            img.style = 'display:none';
          });
        element.style = 'display:block';
      }
    }
  });
}

/**
 * Transform image's path (src) produce with Liip to responsive path
 *
 * @param {string} src
 */
export function responsiveImage(src) {
  var screenWidth = window.innerWidth;
  if (screenWidth <= 576) {
    src = src.replace('/default/', '/xs/');
  } else if (screenWidth <= 768) {
    src = src.replace('/default/', '/sm/');
  } else if (screenWidth <= 992) {
    src = src.replace('/default/', '/md/');
  } else if (screenWidth <= 1200) {
    src = src.replace('/default/', '/lg/');
  } else {
    // 1200+
    src = src.replace('/default/', '/xl/');
  }

  return src;
}

/**
 * Convert elements wich contain attribute (data-href) in normal link (a href)
 * You can use a callback function to decrypt the link (eg: rot13ToText ;-))
 *
 * @param {string}  attribute
 */
export async function decryptLink(attribute = 'data-rot') {
  var convertLink = function (element) {
    // fix "bug" with img
    if (element.getAttribute(attribute) === null) {
      var element = element.closest('[' + attribute + ']');
    }
    if (element.getAttribute(attribute) === null) return;
    var link = document.createElement('a');
    var href = element.getAttribute(attribute);
    element.removeAttribute(attribute);
    for (var i = 0, n = element.attributes.length; i < n; i++) {
      link.setAttribute(
        element.attributes[i].nodeName,
        element.attributes[i].nodeValue
      );
    }
    link.innerHTML = element.innerHTML;
    link.setAttribute(
      'href',
      responsiveImage(convertShortchutForLink(rot13ToText(href)))
    );
    element.parentNode.replaceChild(link, element);
    return link;
  };

  var convertThemAll = function (attribute) {
    [].forEach.call(
      document.querySelectorAll('[' + attribute + ']'),
      function (element) {
        convertLink(element);
      }
    );
  };

  var fireEventLinksBuilt = async function (element, event) {
    await document.dispatchEvent(new Event('DOMChanged'));

    var clickEvent = new Event(event.type);
    element.dispatchEvent(clickEvent);
  };

  var convertLinkOnEvent = async function (event) {
    // convert them all if it's an image (thanks this bug), permit to use gallery (baguetteBox)
    if (event.target.tagName == 'IMG') {
      await convertThemAll(attribute);
      var element = event.target;
    } else {
      var element = convertLink(event.target);
    }
    fireEventLinksBuilt(element, event);
  };

  [].forEach.call(
    document.querySelectorAll('[' + attribute + ']'),
    function (element) {
      element.addEventListener(
        'touchstart',
        function (e) {
          convertLinkOnEvent(e);
        },
        { once: true, passive: true }
      );
      element.addEventListener(
        'click',
        function (e) {
          convertLinkOnEvent(e);
        },
        { once: true }
      );
      element.addEventListener(
        'mouseover',
        function (e) {
          convertLinkOnEvent(e);
        },
        { once: true }
      );
    }
  );
}

/**
 * Convert action attr encoded in rot 13 to normal action with default attr `data-frot`
 *
 * @param {string}  attribute
 */
export function convertFormFromRot13(attribute = 'data-frot') {
  [].forEach.call(
    document.querySelectorAll('[' + attribute + ']'),
    function (element) {
      var action = element.getAttribute(attribute);
      element.removeAttribute(attribute);
      element.setAttribute(
        'action',
        convertShortchutForLink(rot13ToText(action))
      );
    }
  );
}

export function convertShortchutForLink(str) {
  if (str.charAt(0) == '-') {
    return str.replace('-', 'http://');
  }
  if (str.charAt(0) == '_') {
    return str.replace('_', 'https://');
  }
  if (str.charAt(0) == '@') {
    return str.replace('@', 'mailto:');
  }
  return str;
}

/**
 * readableEmail(selector) Transform an email encoded with rot13 in a readable mail (and add mailto:)
 *
 * @param {string}  text
 */
export function readableEmail(selector) {
  document.querySelectorAll(selector).forEach(function (item) {
    var mail = rot13ToText(item.textContent);
    item.innerHTML = '<a href="mailto:' + mail + '">' + mail + '</a>';
    if (selector.charAt(0) == '.') {
      item.classList.remove(selector.substring(1));
    }
  });
}

/**
 * Decode rot13
 *
 * @param {string}  str
 */
export function rot13ToText(str) {
  return str.replace(/[a-zA-Z]/g, function (c) {
    return String.fromCharCode(
      (c <= 'Z' ? 90 : 122) >= (c = c.charCodeAt(0) + 13) ? c : c - 26
    );
  });
}

export function testWebPSupport() {
  var elem = document.createElement('canvas');

  if (elem.getContext && elem.getContext('2d')) {
    return elem.toDataURL('image/webp').indexOf('data:image/webp') == 0;
  }

  return false;
}

/**
 * Used in ThemeComponent
 */
export function convertImageLinkToWebPLink() {
  var switchToWebP = function () {
    [].forEach.call(document.querySelectorAll('a[dwl]'), function (element) {
      var href = responsiveImage(element.getAttribute('dwl'));
      element.setAttribute('href', href);
      element.removeAttribute('dwl');
    });
  };

  if (testWebPSupport()) switchToWebP();
}
