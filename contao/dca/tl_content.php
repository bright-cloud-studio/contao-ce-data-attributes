<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\Database;

$GLOBALS['TL_DCA']['tl_content']['fields']['ce_data_attributes'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['ce_data_attributes'],
    'exclude' => true,
    'inputType' => 'rowWizard',
    'eval' => [
        'tl_class' => 'clr',
        'columnFields' => [
            'attribute_id' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['ce_data_key'],
                'inputType' => 'select',
                'options_callback' => static function () {
                    $options = [];

                    $result = Database::getInstance()
                        ->execute("SELECT id, label, attribute_name FROM tl_data_attribute WHERE published='1' ORDER BY label ASC");

                    while ($result->next()) {
                        $options[$result->id] = sprintf('%s [data-%s]', $result->label, $result->attribute_name);
                    }

                    return $options;
                },
                'eval' => [
                    'includeBlankOption' => true,
                    'chosen' => true,
                    'style' => 'width:300px',
                ],
            ],
            'value' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['ce_data_value'],
                'inputType' => 'text',
                'eval' => [
                    'maxlength' => 255,
                    'style' => 'width:300px',
                    'decodeEntities' => true,
                ],
            ],
        ],
    ],
    'save_callback' => [
        [[\Bcs\DataAttributesBundle\Dca\ContentDataAttributesCallback::class, 'validateAndNormalize']],
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
