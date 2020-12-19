require('./admin.scss');

//global.$ = global.jQuery = require('jquery');

import * as EasyMDE from 'easymde';
window.EasyMDE = EasyMDE;

window.addEventListener('load', function () {
  // ...
  aceEditor();
  easyMDEditor();
  showTitlePixelWidth();
  columnSizeManager();
  memorizeOpenPannel();
});

function showTitlePixelWidth() {
  if (!$('.titleToMeasure').length) return;

  var input = document.querySelector('.titleToMeasure');
  var resultWrapper = document.getElementById('titleWidth');
  function updateTitleWidth() {
    resultWrapper.style =
      'font-size:20px;margin:0;padding:0;border:0;font-weight:400;display:inline-block;font-family:arial,sans-serif;line-height: 1.3;';
    resultWrapper.innerHTML = input.value;
    var titleWidth = resultWrapper.offsetWidth;
    resultWrapper.innerHTML = titleWidth + 'px';
    resultWrapper.style = titleWidth > 560 ? 'color:#B0413E' : 'color:#4F805D';
  }
  updateTitleWidth();
  input.addEventListener('input', updateTitleWidth);
}

function columnSizeManager() {
  if (!$('.expandColumnFields').length) return;
  $('.expandColumnFields').on('click', function () {
    $('.columnFields').removeClass('col-md-3').addClass('col-md-6');
    $('.mainFields').removeClass('col-md-9').addClass('col-md-6');
  });
  $('.mainFields').on('click', function () {
    $('.columnFields').removeClass('col-md-6').addClass('col-md-3');
    $('.mainFields').removeClass('col-md-6').addClass('col-md-9');
  });
}

function memorizeOpenPannel() {
  if (!$('.collapse').length) return;

  $('.collapse').on('shown.bs.collapse', function () {
    var active = $(this).attr('id');
    var panels =
      localStorage.panels === undefined
        ? new Array()
        : JSON.parse(localStorage.panels);
    if ($.inArray(active, panels) == -1) panels.push(active);
    localStorage.panels = JSON.stringify(panels);
  });

  $('.collapse').on('hidden.bs.collapse', function () {
    var active = $(this).attr('id');
    var panels =
      localStorage.panels === undefined
        ? new Array()
        : JSON.parse(localStorage.panels);
    var elementIndex = $.inArray(active, panels);
    if (elementIndex !== -1) {
      panels.splice(elementIndex, 1);
    }
    localStorage.panels = JSON.stringify(panels);

    var panels =
      localStorage.panels === undefined
        ? new Array()
        : JSON.parse(localStorage.panels);
    for (var i in panels) {
      if ($('#' + panels[i]).hasClass('collapse')) {
        $('#' + panels[i]).collapse('show');
      }
    }
  });
}

function copyElementText(element) {
  var text = element.innerText;
  var elem = document.createElement('textarea');
  document.body.appendChild(elem);
  elem.value = text;
  elem.select();
  document.execCommand('copy');
  document.body.removeChild(elem);
}

function aceEditor() {
  $('textarea[data-editor="twig"]').each(function () {
    var textarea = $(this);
    var mode = textarea.data('editor');
    var editDiv = $('<div>', {
      position: 'absolute',
      width: textarea.width(),
      height: textarea.height(),
      class: textarea.attr('class'),
    }).insertBefore(textarea);
    textarea.css('display', 'none');
    var editor = ace.edit(editDiv[0]);
    editor.renderer.setShowGutter(textarea.data('gutter'));
    editor.getSession().setValue(textarea.val());
    editor.getSession().setMode('ace/mode/' + mode);
    editor.setFontSize('20px');
    editor.getSession().setUseWrapMode(true);
    //editor.setTheme("ace/theme/idle_fingers");

    // copy back to textarea on form submit...
    textarea.closest('form').submit(function () {
      textarea.val(editor.getSession().getValue());
    });
  });
}

function easyMDEditor() {
  var timeoutPreviewRender = null;
  $('textarea[data-editor="markdown"]').each(function () {
    var editorElement = $(this)[0];
    var editor = new EasyMDE({
      element: editorElement,
      toolbar: [
        'bold',
        'italic',
        'heading-2',
        'heading-3',
        '|',
        'unordered-list',
        'ordered-list',
        '|',
        'link',
        'image',
        'quote',
        'code',
        'side-by-side',
        'fullscreen',
        {
          name: 'guide',
          action: '/admin/markdown-cheatsheet',
          className: 'fa fa-question-circle',
          noDisable: true,
          title: 'Documentation',
          default: true,
        },
      ],
      status: ['autosave', 'lines', 'words', 'cursor'],
      spellChecker: false,
      nativeSpellcheck: true,
      insertTexts: {
        link: ['[', ']()'],
        image: ['![', '](/media/default/...)'],
      },
      //minHeight: "70vh",
      maxHeight: '70vh',
      syncSideBySidePreviewScroll: false,
      previewRender: function (editorContent, preview) {
        $(editorElement).val(editorContent);
        if (!document.getElementById('previewf')) {
          customPreview(editorContent, editorElement, preview);
        }
        document.addEventListener('keyup', function (e) {
          clearTimeout(timeoutPreviewRender);
          timeoutPreviewRender = setTimeout(function () {
            customPreview(editorContent, editorElement, preview);
          }, 1000);
        });
      },
      /**/
    });
  });
}

function customPreview(editorContent, editorElement, preview) {
  var preloadIframeElement = document.querySelector('iframe.load-preview');
  var previewIframeElement = document.querySelector('iframe.preview-visible');

  var scrollTop = preloadIframeElement
    ? previewIframeElement.contentWindow.window.scrollY
    : 0;
  console.log(scrollTop);
  var XHR = new XMLHttpRequest();
  var form = $(editorElement).closest('form');
  var actionUrl = form.attr('action');
  var urlEncodedData = form.serialize() + '&btn_preview';

  createIframes(preview);

  XHR.addEventListener('load', function (event) {
    var preloadIframeElement = preview.querySelector('iframe.load-preview');
    var previewIframeElement = preview.querySelector('iframe.preview-visible');

    preloadIframeElement.srcdoc = XHR.response;
    preloadIframeElement.onload = function () {
      preloadIframeElement.classList.toggle('preview-visible');
      previewIframeElement.classList.toggle('preview-visible');
      preloadIframeElement.classList.toggle('load-preview');
      previewIframeElement.classList.toggle('load-preview');

      preloadIframeElement.contentWindow.scrollTo(0, scrollTop, {
        duration: 0,
      });
      console.log(scrollTop);
      previewIframeElement.contentWindow.scrollTo(0, scrollTop, {
        duration: 0,
      });
    };
  });
  XHR.addEventListener('error', function (event) {
    preview.innerHTML = "Oups! Quelque chose s'est mal passé.";
  });
  XHR.open('POST', actionUrl);
  XHR.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  XHR.send(urlEncodedData);
}

function createIframes(preview) {
  if (!document.getElementById('previewf')) {
    preview.innerHTML =
      '<iframe width=100% height=100% class=preview-visible id=previewf src="about:blank" allowtransparency="true" frameborder="0" border="0" cellspacing="0"></iframe>' +
      '<iframe width=100% height=100% class="load-preview" src="about:blank" allowtransparency="true" frameborder="0" border="0" cellspacing="0"></iframe>';
  }
}
