<?php

namespace Bcs\DataAttributesBundle\Widget;

use Contao\Database;
use Contao\Widget;

class DataAttributeWizard extends Widget
{
    protected $blnSubmitInput = true;
    protected $strTemplate    = 'be_widget';

    /**
     * Return all published data attributes from the database.
     */
    protected function loadAttributes(): array
    {
        $db     = Database::getInstance();
        $result = $db->execute(
            "SELECT id, label, attribute_name, value_type, allowed_values, default_value
             FROM tl_data_attribute
             WHERE published='1'
             ORDER BY label ASC"
        );

        $attributes = [];
        while ($result->next()) {
            $row = $result->row();
            if ($row['allowed_values']) {
                $row['allowed_values'] = unserialize($row['allowed_values']);
            } else {
                $row['allowed_values'] = [];
            }
            $attributes[$result->id] = $row;
        }

        return $attributes;
    }

    /**
     * Render the value input appropriate for the attribute's value_type.
     */
    protected function buildValueField(string $fieldName, int $index, $attrId, string $value, array $attributes): string
    {
        $inputName = $fieldName . '[' . $index . '][value]';

        if (!$attrId || !isset($attributes[$attrId])) {
            return '<input type="text" name="' . $inputName . '" value="' . htmlspecialchars($value) . '" class="tl_text">';
        }

        $attr = $attributes[$attrId];

        switch ($attr['value_type']) {
            case 'boolean':
                $trueSelected  = $value === 'true'  ? ' selected' : '';
                $falseSelected = $value === 'false' ? ' selected' : '';
                return '<select name="' . $inputName . '" class="tl_select">
                    <option value="true"'  . $trueSelected  . '>true</option>
                    <option value="false"' . $falseSelected . '>false</option>
                </select>';

            case 'integer':
                return '<input type="number" name="' . $inputName . '" value="' . htmlspecialchars($value ?: $attr['default_value']) . '" class="tl_text">';

            case 'select':
                $opts = '';
                foreach ($attr['allowed_values'] as $k => $v) {
                    $sel   = ($value === $k) ? ' selected' : '';
                    $opts .= sprintf('<option value="%s"%s>%s</option>', htmlspecialchars($k), $sel, htmlspecialchars($v));
                }
                return '<select name="' . $inputName . '" class="tl_select">' . $opts . '</select>';

            default: // freetext
                $placeholder = $attr['default_value'] ?? '';
                $val         = $value ?: $placeholder;
                return '<input type="text" name="' . $inputName . '" value="' . htmlspecialchars($val) . '" class="tl_text" placeholder="' . htmlspecialchars($placeholder) . '">';
        }
    }

    public function generate(): string
    {
        $attributes    = $this->loadAttributes();
        $currentValues = [];

        if ($this->varValue) {
            $currentValues = unserialize($this->varValue) ?: [];
        }

        // Always show at least one empty row
        if (empty($currentValues)) {
            $currentValues = [['attribute_id' => '', 'value' => '']];
        }

        $fieldName = $this->strName;
        $tableId   = 'da-wizard-' . $this->strId;
        $rows      = '';

        foreach ($currentValues as $i => $row) {
            $selectedAttrId = $row['attribute_id'] ?? '';
            $selectedValue  = $row['value'] ?? '';

            // Attribute dropdown
            $selectHtml  = '<select name="' . $fieldName . '[' . $i . '][attribute_id]" class="tl_select da-wizard-attr-select" onchange="DataAttributeWizard.updateValueField(this)">';
            $selectHtml .= '<option value="">— Select attribute —</option>';
            foreach ($attributes as $id => $attr) {
                $sel         = ((string) $selectedAttrId === (string) $id) ? ' selected' : '';
                $selectHtml .= sprintf(
                    '<option value="%d"%s>%s [data-%s]</option>',
                    $id,
                    $sel,
                    htmlspecialchars($attr['label']),
                    htmlspecialchars($attr['attribute_name'])
                );
            }
            $selectHtml .= '</select>';

            $valueFieldHtml = $this->buildValueField($fieldName, $i, $selectedAttrId, $selectedValue, $attributes);

            $rows .= '<tr class="da-wizard-row">';
            $rows .= '  <td style="width:50%">' . $selectHtml . '</td>';
            $rows .= '  <td class="da-wizard-value-cell" style="width:45%">' . $valueFieldHtml . '</td>';
            $rows .= '  <td style="width:5%;text-align:center"><button type="button" class="tl_submit" style="padding:2px 8px" onclick="DataAttributeWizard.removeRow(this)">−</button></td>';
            $rows .= '</tr>';
        }

        // JSON-encode attributes for use by JS
        $attrJson = json_encode($attributes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);

        $html  = '<table class="tl_listing" id="' . $tableId . '" style="width:100%;margin-bottom:8px">';
        $html .= '  <thead><tr>';
        $html .= '    <th>Attribute</th>';
        $html .= '    <th>Value</th>';
        $html .= '    <th></th>';
        $html .= '  </tr></thead>';
        $html .= '  <tbody>' . $rows . '</tbody>';
        $html .= '</table>';
        $html .= '<button type="button" class="tl_submit" onclick="DataAttributeWizard.addRow(\'' . $tableId . '\', \'' . htmlspecialchars(addslashes($fieldName)) . '\')">+ Add Attribute</button>';

        $html .= $this->renderScript($attrJson, $tableId);

        return $html;
    }

    protected function renderScript(string $attrJson, string $tableId): string
    {
        return <<<JS
<script>
(function() {
    if (typeof DataAttributeWizard !== 'undefined') return;

    window.DataAttributeWizard = {
        attributes: {$attrJson},

        updateValueField: function(select) {
            var row       = select.closest('tr');
            var tbody     = row.closest('tbody');
            var index     = Array.prototype.indexOf.call(tbody.children, row);
            var table     = row.closest('table');
            var fieldName = table.dataset.fieldname;
            var attrId    = select.value;
            var valueCell = row.querySelector('.da-wizard-value-cell');

            valueCell.innerHTML = DataAttributeWizard.buildValueHtml(attrId, fieldName, index, '');
        },

        buildValueHtml: function(attrId, fieldName, index, value) {
            var name = fieldName + '[' + index + '][value]';
            var attr = DataAttributeWizard.attributes[attrId];

            if (!attr) {
                return '<input type="text" name="' + name + '" value="" class="tl_text">';
            }

            if (attr.value_type === 'boolean') {
                return '<select name="' + name + '" class="tl_select">'
                    + '<option value="true">true</option>'
                    + '<option value="false">false</option>'
                    + '</select>';
            }

            if (attr.value_type === 'integer') {
                return '<input type="number" name="' + name + '" value="' + (attr.default_value || '') + '" class="tl_text">';
            }

            if (attr.value_type === 'select') {
                var opts = '';
                var av   = attr.allowed_values || {};
                for (var k in av) {
                    opts += '<option value="' + k + '">' + av[k] + '</option>';
                }
                return '<select name="' + name + '" class="tl_select">' + opts + '</select>';
            }

            // freetext default
            var def = attr.default_value || '';
            return '<input type="text" name="' + name + '" value="' + def + '" class="tl_text" placeholder="' + def + '">';
        },

        addRow: function(tableId, fieldName) {
            var tbody = document.getElementById(tableId).querySelector('tbody');
            var index = tbody.children.length;

            var selectHtml = '<select name="' + fieldName + '[' + index + '][attribute_id]" class="tl_select da-wizard-attr-select" onchange="DataAttributeWizard.updateValueField(this)">'
                + '<option value="">— Select attribute —</option>';
            for (var id in DataAttributeWizard.attributes) {
                var a = DataAttributeWizard.attributes[id];
                selectHtml += '<option value="' + id + '">' + a.label + ' [data-' + a.attribute_name + ']</option>';
            }
            selectHtml += '</select>';

            var tr = document.createElement('tr');
            tr.className = 'da-wizard-row';
            tr.innerHTML = '<td style="width:50%">' + selectHtml + '</td>'
                + '<td class="da-wizard-value-cell" style="width:45%"><input type="text" name="' + fieldName + '[' + index + '][value]" value="" class="tl_text"></td>'
                + '<td style="width:5%;text-align:center"><button type="button" class="tl_submit" style="padding:2px 8px" onclick="DataAttributeWizard.removeRow(this)">−</button></td>';
            tbody.appendChild(tr);
        },

        removeRow: function(btn) {
            var tbody = btn.closest('tbody');
            btn.closest('tr').remove();
            // Re-index all remaining rows so POST names stay sequential
            Array.prototype.forEach.call(tbody.children, function(tr, i) {
                tr.querySelectorAll('select, input').forEach(function(el) {
                    el.name = el.name.replace(/\[\d+\]/, '[' + i + ']');
                });
            });
        }
    };

    // Store fieldname on the table element so JS can access it without globals
    document.getElementById('{$tableId}').dataset.fieldname = document.getElementById('{$tableId}').closest('.widget').querySelector('[name*="[attribute_id]"]')?.name?.replace(/\[\d+\]\[attribute_id\]/, '') || '';
})();
</script>
JS;
    }

    /**
     * Validate and serialize submitted rows, stripping any empty attribute selections.
     */
    public function validate(): void
    {
        $submitted = $this->getPost($this->strName);

        if (!is_array($submitted)) {
            $this->varValue = '';
            return;
        }

        // Strip rows with no attribute selected
        $rows = array_values(
            array_filter($submitted, static fn($row) => !empty($row['attribute_id']))
        );

        $this->varValue = !empty($rows) ? serialize($rows) : '';
    }
}
