<?php

namespace Bcs\DataAttributesBundle\Widget;

use Contao\Widget;
use Doctrine\DBAL\Connection;
use Contao\System;

class DataAttributeWizard extends Widget
{
    protected $blnSubmitInput = true;
    protected $strTemplate    = 'be_widget';

    /**
     * Fetch attributes using Doctrine (Contao 5 standard)
     */
    protected function loadAttributes(): array
    {
        /** @var Connection $db */
        $db = System::getContainer()->get('database_connection');
        
        $statement = $db->executeQuery(
            "SELECT id, label, attribute_name, value_type, allowed_values, default_value 
             FROM tl_data_attribute 
             WHERE published='1' 
             ORDER BY label ASC"
        );

        $attributes = [];
        while ($row = $statement->fetchAssociative()) {
            // Contao 5 stores JSON in the DB; Doctrine/DCA might have already 
            // converted allowed_values to an array, but we check to be safe.
            if ($row['allowed_values'] && !is_array($row['allowed_values'])) {
                $row['allowed_values'] = json_decode($row['allowed_values'], true) ?: [];
            } elseif (!$row['allowed_values']) {
                $row['allowed_values'] = [];
            }
            $attributes[$row['id']] = $row;
        }

        return $attributes;
    }

    protected function buildValueField(string $fieldName, int $index, $attrId, ?string $value, array $attributes): string
    {
        $inputName = $fieldName . '[' . $index . '][value]';
        $val = $value ?? '';

        if (!$attrId || !isset($attributes[$attrId])) {
            return '<input type="text" name="' . $inputName . '" value="' . htmlspecialchars($val) . '" class="tl_text">';
        }

        $attr = $attributes[$attrId];

        switch ($attr['value_type']) {
            case 'boolean':
                $trueSelected  = $val === 'true'  ? ' selected' : '';
                $falseSelected = $val === 'false' ? ' selected' : '';
                return '<select name="' . $inputName . '" class="tl_select">
                            <option value="true"'  . $trueSelected  . '>true</option>
                            <option value="false"' . $falseSelected . '>false</option>
                        </select>';

            case 'integer':
                return '<input type="number" name="' . $inputName . '" value="' . htmlspecialchars($val ?: ($attr['default_value'] ?? '')) . '" class="tl_text">';

            case 'select':
                $opts = '';
                foreach ($attr['allowed_values'] as $k => $v) {
                    $sel   = ($val === (string)$k) ? ' selected' : '';
                    $opts .= sprintf('<option value="%s"%s>%s</option>', htmlspecialchars($k), $sel, htmlspecialchars($v));
                }
                return '<select name="' . $inputName . '" class="tl_select">' . $opts . '</select>';

            default:
                $placeholder = $attr['default_value'] ?? '';
                $displayVal  = $val ?: $placeholder;
                return '<input type="text" name="' . $inputName . '" value="' . htmlspecialchars($displayVal) . '" class="tl_text" placeholder="' . htmlspecialchars($placeholder) . '">';
        }
    }

    public function generate(): string
    {
        $attributes = $this->loadAttributes();
        
        // In Contao 5, $this->varValue is usually already an array if the DCA is set to 'json'
        $currentValues = is_array($this->varValue) ? $this->varValue : [];

        if (empty($currentValues)) {
            $currentValues = [['attribute_id' => '', 'value' => '']];
        }

        $fieldName = $this->strName;
        $tableId   = 'da-wizard-' . $this->strId;
        $rows      = '';

        foreach ($currentValues as $i => $row) {
            $selectedAttrId = $row['attribute_id'] ?? '';
            $selectedValue  = $row['value'] ?? '';

            $selectHtml  = '<select name="' . $fieldName . '[' . $i . '][attribute_id]" class="tl_select da-wizard-attr-select" onchange="DataAttributeWizard.updateValueField(this)">';
            $selectHtml .= '<option value="">— Select attribute —</option>';
            foreach ($attributes as $id => $attr) {
                $sel = ((string)$selectedAttrId === (string)$id) ? ' selected' : '';
                $selectHtml .= sprintf(
                    '<option value="%d"%s>%s [data-%s]</option>',
                    $id, $sel, htmlspecialchars($attr['label']), htmlspecialchars($attr['attribute_name'])
                );
            }
            $selectHtml .= '</select>';

            $valueFieldHtml = $this->buildValueField($fieldName, $i, $selectedAttrId, $selectedValue, $attributes);

            $rows .= '<tr class="da-wizard-row">
                        <td style="width:50%">' . $selectHtml . '</td>
                        <td class="da-wizard-value-cell" style="width:45%">' . $valueFieldHtml . '</td>
                        <td style="width:5%;text-align:center"><button type="button" class="tl_submit" style="padding:2px 8px" onclick="DataAttributeWizard.removeRow(this)">−</button></td>
                      </tr>';
        }

        $attrJson = json_encode($attributes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
        
        $html  = '<table class="tl_listing" id="' . $tableId . '" style="width:100%;margin-bottom:8px">
                    <thead><tr><th>Attribute</th><th>Value</th><th></th></tr></thead>
                    <tbody>' . $rows . '</tbody>
                  </table>';
        $html .= '<button type="button" class="tl_submit" onclick="DataAttributeWizard.addRow(\'' . $tableId . '\', \'' . htmlspecialchars(addslashes($fieldName)) . '\')">+ Add Attribute</button>';
        $html .= $this->renderScript($attrJson, $tableId);

        return $html;
    }

    // ... renderScript, addRow, removeRow remain the same as your functional JS logic is sound ...

    public function validate(): void
    {
        $submitted = $this->getPost($this->strName);

        if (!is_array($submitted)) {
            $this->varValue = null;
            return;
        }

        $rows = array_values(
            array_filter($submitted, static fn($row) => !empty($row['attribute_id']))
        );

        // Store as pure array for Contao 5 JSON handling
        $this->varValue = !empty($rows) ? $rows : null;
    }
}
