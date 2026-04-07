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
                        initRow(selects[k]);
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
    }

    function initAllRows(container) {
        var selects = container.querySelectorAll('select[name*="[attribute_id]"]');
        for (var i = 0; i < selects.length; i++) {
            initRow(selects[i]);
        }
    }

    function initRow(select) {
        if (!select.parentElement.classList.contains('bcs-attr-wrap')) {
            var wrap = document.createElement('div');
            wrap.className = 'bcs-attr-wrap';
            wrap.style.cssText = 'position:relative;display:block;width:100%;';
            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(select);
        }
        updateRow(select);
    }

    function updateRow(select) {
        var attrId = parseInt(select.value, 10) || 0;
        var wrap = select.closest ? select.closest('.bcs-attr-wrap') : select.parentElement;
        var td = select.closest ? select.closest('td') : getParentTd(select);
        if (!td) {
            return;
        }
        var tr = td.parentElement;
        if (!tr) {
            return;
        }

        var tds = tr.querySelectorAll('td');
        var valueTd = tds[1];
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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

}());
