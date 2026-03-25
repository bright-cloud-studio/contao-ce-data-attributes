<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_content']['fields']['customDataAttributes'] = [
    'exclude'   => true,
    'inputType' => 'keyValueWizard',
    'eval'      => [
        'tl_class' => 'clr',
    ],
    'sql' => 'text NULL',
];

// Apply to whichever content element palettes you need
$palettes = ['text', 'html', 'image', 'list', 'table', 'accordionStart'];

foreach ($palettes as $palette) {
    PaletteManipulator::create()
        ->addLegend('data_attributes_legend', 'expert_legend', PaletteManipulator::POSITION_AFTER, true)
        ->addField('customDataAttributes', 'data_attributes_legend', PaletteManipulator::POSITION_APPEND)
        ->applyToPalette($palette, 'tl_content');
}
