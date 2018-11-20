
/**
 * Fetch (ajax) function permitting to get block via a POST request
 * (prevent from spam)
 *
 * @param {string} attribute
 */
export function getBlockFromSky(attribute = 'data-sky') {
    document.querySelectorAll('['+attribute+']').forEach(item => {
        fetch(item.getAttribute(attribute), {
          headers: {"Content-Type": "application/json", "Accept": "text/plain"},
          method: "POST",
          // Later: maybe implement sending data form data-post
          // body: JSON.stringify({"contact": (document.getElementById("contact") !== null ? 1: 0)}),
          credentials: "same-origin"
        }).then(function(response) {
          return response.text()
        }).then(function(body) {
            item.removeAttribute('data-sky');
            item.innerHTML = body;

            // add export function to reDo on document dom ready
            if (typeof onPageLoaded === 'function') onPageLoaded();
            if (typeof onDomLoaded === 'function')  onDomLoaded();
        });
    });
}


/**
 * ajaxify-form
 */
export function formToSky(userOptions = {}) {


    var options = {
        selector: '.ajax-form', // selector for ajax form
    };
    for (var attrname in userOptions) {
        options[attrname] = userOptions[attrname];
    }

  document.querySelectorAll(options.selector).forEach(item => {
      if (item.querySelector('form') !== null) {
          item.querySelector('form').addEventListener("submit", e => {
            e.preventDefault();
            sendFormToSky(e);
          });
       }
    });

    sendFormToSky = function(form) {
        var $submitButton = getSubmitButton(form);
        if ($submitButton !== null) {
            var initialButton = getSubmitButton(form).outerHTML;
            $submitButton.outerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        }

          //var formData = new FormData();
          var toSend = '';
          for (var i = 0; i < form.srcElement.length; ++i) {
                toSend += encodeURI(form.srcElement[i].name)+"="+encodeURI(form.srcElement[i].value)+'&';
          }

        var xmlhttp = new XMLHttpRequest();
        xmlhttp.addEventListener("load", function() {
            form.srcElement.outerHTML = xmlhttp.responseText;
            formToSky();
        }, false);
        xmlhttp.open("POST",form.srcElement.action,false);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(toSend);
    }

    renderError = function(error) {
        var msg = '';
        for (var key in error) {
           if (error.hasOwnProperty(key)) {
              var obj = error[key];
              for (var prop in obj) {
                 if (obj.hasOwnProperty(prop)) {
                     msg += key + " : " + obj[prop] + '<br>';
                 }
              }
           }
        }
        return msg;
    }

    getSubmitButton = function(form) {
        if (form.srcElement.querySelector('[type=submit]') !== null) {
            return form.srcElement.querySelector('[type=submit]');
        }
        if (form.srcElement.getElementsByTagName('button') !== null) {
            return form.srcElement.getElementsByTagName('button');
        }
        return null;
    }
}

