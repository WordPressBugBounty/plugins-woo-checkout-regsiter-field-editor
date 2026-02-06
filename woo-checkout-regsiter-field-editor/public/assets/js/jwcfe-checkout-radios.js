(function($){
    'use strict';

    var cfg = window.jwcfe_checkout_radios || { selectDataAttr: 'data-jwcfe-type', selectDataVal: 'radio' };

    function safeNameFromId(id) {
        // produce a safe input name from an id (no forbidden chars)
        return id.replace(/[^a-zA-Z0-9_\-:\[\]]/g, '_');
    }

    function convertSelectToRadios($select) {
        if ($select.data('jwcfe-converted')) return; // already converted
        $select.data('jwcfe-converted', true);

        // find or create id
        var id = $select.attr('id');
        if (!id) {
            id = 'jwcfe-' + Math.random().toString(36).substr(2,9);
            $select.attr('id', id);
        }

        // determine name: prefer select.name if present; otherwise derive from id
        var name = $select.attr('name');
        if (!name || name === '') {
            name = safeNameFromId(id);
            // also set the name on the select so server-side receives it (keeps things consistent)
            $select.attr('name', name);
        }

        // link to label for accessibility if exists
        var $label = $select.closest('.wc-blocks-components-select').find('.wc-blocks-components-select__label');
        var labelId = id + '-label';
        if ($label.length) {
            if (!$label.attr('id')) {
                $label.attr('id', labelId);
            } else {
                labelId = $label.attr('id');
            }
        } else {
            labelId = '';
        }

        // create radio container with aria
var $wrapper = $('<div class="jwcfe-radio-replacement" role="radiogroup" />').attr('data-for', id);
if (labelId) {
  $wrapper.attr('aria-labelledby', labelId);
}

// ensure we have the server/React-provided name (use existing select name if present)
var name = $select.attr('name');
if (!name || name === '') {
  name = safeNameFromId(id);
  $select.attr('name', name);
}

$select.find('option').each(function(i, opt){
  var $opt = $(opt);
  var val = $opt.attr('value');

  if ($opt.prop('disabled') || typeof val === 'undefined' || val === '') {
    return;
  }

  var label = $opt.text() || val;
  var radioId = id + '-opt-' + i;

  var $labelEl = $('<label class="jwcfe-radio-item" />').attr('for', radioId);
  var $input = $('<input type="radio" />').attr({
      id: radioId,
      name: name,
      value: val
  });

  if ($opt.prop('selected')) {
      $input.prop('checked', true);
  }

  // sync to select in a way React will notice:
  $input.on('change', function(){
    if (!$(this).is(':checked')) return;

    // 1) set native select value and mark selected option
    try {
      // native element
      var sel = $select[0];
      if (sel) {
        // set the select's value
        sel.value = val;

        // ensure correct option selected (for React consistency)
        for (var j = 0; j < sel.options.length; j++) {
          sel.options[j].selected = (sel.options[j].value == val);
        }

        // 2) dispatch native events so React/Blocks hears it
        var evInput = new Event('input', { bubbles: true });
        var evChange = new Event('change', { bubbles: true });

        sel.dispatchEvent(evInput);
        sel.dispatchEvent(evChange);
      }
    } catch (e) {
      // fallback to jQuery change if native event fails
      $select.val(val).trigger('change');
    }

    // update aria-checked on our radios
    $wrapper.find('input[type="radio"]').each(function(){
      $(this).attr('aria-checked', $(this).is(':checked') ? 'true' : 'false');
    });
  });

  $labelEl.append($input).append(' ').append($('<span class="jwcfe-radio-label-text" />').text(label));
  $wrapper.append($labelEl);
});

// Insert radios after select but DON'T remove the select from DOM.
// Hide visually in a non-destructive way so native validation / React still sees it.
// Use a CSS class to visually hide (keeps element in DOM and available to screen readers)
$select.after($wrapper).addClass('jwcfe-hidden-select');
    }

    function findAndConvert() {
        var attr = cfg.selectDataAttr;
        var val = cfg.selectDataVal;
        if (!attr) return;
        var selector = 'select[' + attr + '="' + val + '"]';
        $(selector).each(function(){
            convertSelectToRadios($(this));
        });
    }

    // Use MutationObserver to catch dynamic mount/update from Blocks React app
    function initObserver() {
        var target = document.body;
        var observer = new MutationObserver(function(mutations){
            findAndConvert();
        });
        observer.observe(target, { childList: true, subtree: true });
        $(function(){ findAndConvert(); });
    }

    initObserver();

    document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-jwcfe-type="radio"]').forEach(selectEl => {
    const wrapper = selectEl.closest('.wc-block-components-select-input');
    if (wrapper) {
      wrapper.classList.add('wc-block-components-radio-input');
      wrapper.classList.remove('wc-block-components-select-input'); // optional
    }
  });
});


})(jQuery);
