<?php

// contao/dca/tl_content.php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addField('my_custom_field', 'text', PaletteManipulator::POSITION_AFTER)
    ->applyToPalette('text', 'tl_content');

$GLOBALS['TL_DCA']['tl_content']['fields']['my_custom_field'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['my_custom_field'],
    'inputType' => 'text',
    'eval'      => [
        'maxlength' => 255,
        'tl_class'  => 'w50',
    ],
    'sql'       => [
        'type'    => 'string',
        'length'  => 255,
        'default' => '',
    ],
];
