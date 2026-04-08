(function () {
    'use strict';

    var attributeMap = {};

    var BADGE_SELECT_STYLE = [
        'position:absolute',
        'right:28px',
        'top:50%',
        'transform:translateY(-50%)',
        'pointer-events:none',
        'font-size:11px',
        'font-weight:500',
        'padding:2px 8px',
        'border-radius:10px',
        'background:#E1F5EE',
        'color:#0F6E56',
        'line-height:1.4'
    ].join(';');

    var BADGE_TEXT_STYLE = [
        'position:absolute',
        'right:28px',
        'top:50%',
        'transform:translateY(-50%)',
        'pointer-events:none',
        'font-size:11px',
        'font-weight:500',
        'padding:2px 8px',
        'border-radius:10px',
        'background:#E6F1FB',
        'color:#185FA5',
        'line-height:1.4'
    ].join(';');

    function init() {
        if (typeof window.BcsAttributeMap !== 'undefined') {
            attributeMap = window.BcsAttributeMap;
        } else {
            return;
        }

        // Defer setup so Contao's widget JS finishes rendering rows first.
        // This ensures initAllRows sees the final DOM (correct badge positioning)
        // on both hard reload and Turbo Drive post-save navigation.
        setTimeout(function () {
            var container = document.getElementById('ctrl_ce_data_attributes');
            if (!container) {
                return;
            }

            initAllRows(container);

            var observer = new MutationObserver(function (mutations) {
                for (var i = 0; i < mutations.length; i++) {
                    var added = mutations[i].addedNodes;
                    for (var j = 0; j < added.length; j++) {
                        var node = added[j];
                        if (node.nodeType !== 1) {
                            continue;
                        }
                        var selects = node.querySelectorAll('select[name*="[attribute_id]"]');
                        for (var k = 0; k < selects.length; k++) {
                            // Skip selects already wrapped by us — these are mutations
                            // caused by our own initRow, not a genuinely new row.
                            var alreadyWrapped = selects[k].closest
                                ? selects[k].closest('.bcs-attr-wrap')
                                : selects[k].parentElement.classList.contains('bcs-attr-wrap');
                            if (!alreadyWrapped) {
                                initRow(selects[k]);
                            }
                        }
                    }
                }
            });

            observer.observe(container, { childList: true, subtree: true });

            container.addEventListener('change', function (e) {
                var target = e.target;
                if (target && target.name && target.name.indexOf('[attribute_id]') !== -1) {
                    updateRow(target);
                }
            });
        }, 0);
    }

    function initAllRows(container) {
        var selects = container.querySelectorAll('select[name*="[attribute_id]"]');
        for (var i = 0; i < selects.length; i++) {
            initRow(selects[i]);
        }
    }

    function initRow(select) {
        // If this exact select element has already been processed, skip it.
        // This prevents double-init when both initAllRows and the MutationObserver
        // fire for the same element (e.g. hard reload vs Turbo navigation).
        if (select.dataset.bcsInit) {
            return;
        }
        select.dataset.bcsInit = '1';

        // Contao uses Choices.js which hides the native <select> inside a .choices
        // div. The visible input area is .choices__inner — use that as the
        // positioning context so the badge's top:50% resolves against the correct height.
        var choicesEl = select.closest ? select.closest('.choices') : null;
        var positionTarget = choicesEl
            ? (choicesEl.querySelector('.choices__inner') || choicesEl)
            : null;

        if (positionTarget) {
            if (!positionTarget.classList.contains('bcs-attr-wrap')) {
                positionTarget.classList.add('bcs-attr-wrap');
                positionTarget.style.position = 'relative';
            }
        } else {
            // Fallback: no Choices.js, wrap the native select directly
            if (!select.parentElement.classList.contains('bcs-attr-wrap')) {
                var wrap = document.createElement('div');
                wrap.className = 'bcs-attr-wrap';
                wrap.style.cssText = 'position:relative;display:block;width:100%;';
                select.parentNode.insertBefore(wrap, select);
                wrap.appendChild(select);
            }
        }

        updateRow(select);
    }

    function updateRow(select) {
        var attrId = parseInt(select.value, 10) || 0;
        var wrap = select.closest ? select.closest('.bcs-attr-wrap') : select.parentElement;
        var tr = select.closest ? select.closest('tr') : getParentTr(select);
        if (!tr) {
            return;
        }

        // Find the value cell by name rather than positional index, so column
        // order and any extra handle/button columns don't affect us.
        var valueName = select.name.replace('[attribute_id]', '[value]');
        var valueEl = tr.querySelector('input[name="' + valueName + '"], select.bcs-value-select');
        var valueTd = valueEl
            ? (valueEl.closest ? valueEl.closest('td') : getParentTd(valueEl))
            : null;
        if (!valueTd) {
            return;
        }

        var existingBadge = wrap.querySelector('.bcs-type-badge');
        if (existingBadge) {
            existingBadge.parentNode.removeChild(existingBadge);
        }

        var attr = attrId ? attributeMap[attrId] : null;
        var type = attr ? attr.type : 'freetext';

        if (attrId && attr) {
            var badge = document.createElement('span');
            badge.className = 'bcs-type-badge';
            badge.textContent = type === 'select' ? 'select' : 'text';
            badge.style.cssText = type === 'select' ? BADGE_SELECT_STYLE : BADGE_TEXT_STYLE;
            wrap.appendChild(badge);
        }

        updateValueField(valueTd, attr, type);
    }

    function updateValueField(valueTd, attr, type) {
        var existingInput = valueTd.querySelector('input[type="text"]');
        var existingSelect = valueTd.querySelector('select.bcs-value-select');
        var currentValue = existingInput
            ? existingInput.value
            : (existingSelect ? existingSelect.value : '');

        if (type === 'select' && attr && attr.options && attr.options.length) {
            if (existingSelect) {
                // Repopulate options — attribute may have changed (e.g. after duplicate)
                var previousValue = existingSelect.value;
                existingSelect.innerHTML = '';
                for (var i = 0; i < attr.options.length; i++) {
                    var opt = document.createElement('option');
                    opt.value = attr.options[i].key;
                    opt.textContent = attr.options[i].value || attr.options[i].key;
                    existingSelect.appendChild(opt);
                }
                existingSelect.value = previousValue;
                applySelectStyle(existingSelect);
                return;
            }

            var sel = document.createElement('select');
            sel.className = 'bcs-value-select';

            if (existingInput) {
                sel.name = existingInput.name;
                if (existingInput.id) {
                    sel.id = existingInput.id;
                }
            }

            applySelectStyle(sel);

            for (var i = 0; i < attr.options.length; i++) {
                var opt = document.createElement('option');
                opt.value = attr.options[i].key;
                opt.textContent = attr.options[i].value || attr.options[i].key;
                if (attr.options[i].key === currentValue) {
                    opt.selected = true;
                }
                sel.appendChild(opt);
            }

            if (existingInput) {
                valueTd.replaceChild(sel, existingInput);
            } else {
                valueTd.appendChild(sel);
            }

        } else {
            if (existingInput) {
                removeSelectStyle(existingInput);
                if (!existingInput.value && attr && attr.default) {
                    existingInput.value = attr.default;
                }
                return;
            }

            var inp = document.createElement('input');
            inp.type = 'text';
            inp.className = 'tl_text';

            if (existingSelect) {
                inp.name = existingSelect.name;
                if (existingSelect.id) {
                    inp.id = existingSelect.id;
                }
            }

            inp.value = (attr && attr.default) ? attr.default : currentValue;

            if (existingSelect) {
                valueTd.replaceChild(inp, existingSelect);
            } else {
                valueTd.appendChild(inp);
            }
        }
    }

    function applySelectStyle(el) {
        el.style.backgroundColor = '#E1F5EE';
        el.style.borderColor = '#5DCAA5';
        el.style.color = '#0F6E56';
        el.style.width = '100%';
    }

    function removeSelectStyle(el) {
        el.style.backgroundColor = '';
        el.style.borderColor = '';
        el.style.color = '';
    }

    function getParentTd(el) {
        var node = el.parentElement;
        while (node) {
            if (node.tagName === 'TD') {
                return node;
            }
            node = node.parentElement;
        }
        return null;
    }

    function getParentTr(el) {
        var node = el.parentElement;
        while (node) {
            if (node.tagName === 'TR') {
                return node;
            }
            node = node.parentElement;
        }
        return null;
    }

    // Contao 5 uses Hotwire Turbo Drive. turbo:load fires on both the initial
    // page load and every subsequent Turbo navigation (e.g. after save), so
    // it is the only boot hook we need.
    document.addEventListener('turbo:load', init);

}());
