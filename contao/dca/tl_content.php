<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Add the new field definition
$GLOBALS['TL_DCA']['tl_content']['fields']['ce_data_attributes'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['ce_data_attributes'],
    'inputType' => 'dataAttributeWizard',
    'eval'      => [
        'tl_class'       => 'clr',
        'decodeEntities' => true, // Add this to ensure proper data handling
    ],
    'sql'       => "blob NULL", 
];

// Inject a new legend + field into the 'text' palette only
PaletteManipulator::create()
    ->addLegend('data_attributes_legend', 'expert_legend', PaletteManipulator::POSITION_BEFORE, true)
    ->addField('ce_data_attributes', 'data_attributes_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('text', 'tl_content');
