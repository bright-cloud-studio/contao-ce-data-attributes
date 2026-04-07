<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\Database;

$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/bcsdataattributes/js/ce-data-attributes.js|static';

$GLOBALS['TL_DCA']['tl_content']['fields']['ce_data_attributes'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['ce_data_attributes'],
    'exclude' => true,
    'inputType' => 'rowWizard',
    'fields' => [
        'attribute_id' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['ce_data_key'],
            'inputType' => 'select',
            'options_callback' => static function () {
                $db = \Contao\Database::getInstance();

                $result = $db->execute(
                    "SELECT id, label, attribute_name, value_type, allowed_values, default_value FROM tl_data_attribute WHERE published='1' ORDER BY label ASC"
                );

                $options = [];
                $map     = [];

                while ($result->next()) {
                    $options[$result->id] = sprintf('%s [data-%s]', $result->label, $result->attribute_name);

                    $allowedValues = \Contao\StringUtil::deserialize($result->allowed_values, true);
                    $optionsList   = [];

                    if (is_array($allowedValues)) {
                        foreach ($allowedValues as $entry) {
                            if (!isset($entry['key'])) {
                                continue;
                            }
                            $optionsList[] = [
                                'key'   => (string) $entry['key'],
                                'value' => (string) ($entry['value'] ?? $entry['key']),
                            ];
                        }
                    }

                    $map[(int) $result->id] = [
                        'type'    => $result->value_type ?: 'freetext',
                        'default' => (string) $result->default_value,
                        'options' => $optionsList,
                    ];
                }

                $json = json_encode($map, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
                $GLOBALS['TL_MOOTOOLS'][] = '<script>window.BcsAttributeMap=' . $json . ';</script>';

                return $options;
            },
            'eval' => [
                'includeBlankOption' => true,
                'chosen' => true,
            ],
        ],
        'value' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['ce_data_value'],
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 255,
                'decodeEntities' => true,
            ],
        ],
    ],
    'eval' => [
        'tl_class' => 'clr',
    ],
    'save_callback' => [
        [\Bcs\DataAttributesBundle\Dca\ContentDataAttributesCallback::class, 'validateAndNormalize'],
    ],
    'sql' => [
        'type' => 'blob',
        'notnull' => false,
    ],
];

PaletteManipulator::create()
    ->addLegend('data_attributes_legend', 'expert_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('ce_data_attributes', 'data_attributes_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('text', 'tl_content');
