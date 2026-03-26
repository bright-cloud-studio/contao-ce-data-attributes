<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Add the new field definition
$GLOBALS['TL_DCA']['tl_content']['fields']['ce_data_attributes'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['ce_data_attributes'],
    'inputType' => 'dataAttributeWizard',
    'eval'      => [
        'tl_class' => 'clr',
        'columnFields' => [ // If this is a KeyValue style wizard, it needs columns
            'key' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['ce_data_key'],
                'inputType' => 'text',
                'eval' => ['style'=>'width:40%', 'nospace'=>true]
            ],
            'value' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['ce_data_value'],
                'inputType' => 'text',
                'eval' => ['style'=>'width:50%']
            ]
        ]
    ],
    // THIS IS THE KEY:
    'sql'       => "blob NULL", 
];

// Inject a new legend + field into the 'text' palette only
PaletteManipulator::create()
    ->addLegend('data_attributes_legend', 'expert_legend', PaletteManipulator::POSITION_BEFORE, true)
    ->addField('ce_data_attributes', 'data_attributes_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('text', 'tl_content');
