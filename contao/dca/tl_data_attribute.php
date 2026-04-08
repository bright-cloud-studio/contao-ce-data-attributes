<?php

use Contao\DataContainer;
use Contao\DC_Table;

/* Table tl_data_attribute */
$GLOBALS['TL_DCA']['tl_data_attribute'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => DC_Table::class,
        'switchToEdit'                => false,
        'enableVersioning'            => true,
        'sql' => array
        (
            'keys' => array
            (
                'id'                  => 'primary'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => DataContainer::MODE_SORTED,
            'flag'                    => DataContainer::SORT_ASC,
            'fields'                  => array('category ASC'),
            'rootPaste'               => false,
            'showRootTrails'          => false,
            'icon'                    => 'pagemounts.svg',
            'panelLayout'             => 'filter;sort,search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('label', 'attribute_name'),
            'format'                  => '%s <span style="color:#999">[data-%s]</span>'
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_data_attribute']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'toggle' => array
            (
                'href'                => 'act=toggle&field=published',
                'icon'                => 'visible.svg',
                'toggleField'         => 'published',
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_data_attribute']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                => array('value_type'),
        'default'                     => '{data_attribute_legend},label,attribute_name,category;{value_legend},value_type;{description_legend},description;{publish_legend},published;'
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'value_type_freetext'         => 'default_value',
        'value_type_select'           => 'allowed_values',
        'value_type_boolean'          => '',
    ),

    // Fields
    'fields' => array
    (
        // Contao Fields
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'label' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_data_attribute']['label'],
            'inputType'               => 'text',
            'default'                 => '',
            'filter'                  => false,
            'search'                  => true,
            'sorting'                 => true,
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'attribute_name' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_data_attribute']['attribute_name'],
            'inputType'               => 'text',
            'default'                 => '',
            'filter'                  => false,
            'search'                  => true,
            'eval'                    => array('mandatory'=>true, 'maxlength'=>128, 'rgxp'=>'alias', 'tl_class'=>'w50'),
            'sql'                     => "varchar(128) NOT NULL default ''"
        ),
        'category' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_data_attribute']['category'],
            'inputType'               => 'text',
            'default'                 => '',
            'filter'                  => true,
            'search'                  => false,
            'sorting'                 => true,
            'eval'                    => array('maxlength'=>128, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(128) NOT NULL default ''"
        ),
        'value_type' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_data_attribute']['value_type'],
            'inputType'               => 'radio',
            'default'                 => 'freetext',
            'options'                 => array('freetext', 'select'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_data_attribute']['value_type_options'],
            'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr'),
            'sql'                     => "varchar(16) NOT NULL default 'freetext'"
        ),
        'allowed_values' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_data_attribute']['allowed_values'],
            'inputType'               => 'keyValueWizard',
            'eval'                    => array('tl_class'=>'clr'),
            'sql'                     => "blob NULL"
        ),
        'default_value' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_data_attribute']['default_value'],
            'inputType'               => 'text',
            'default'                 => '',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'description' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_data_attribute']['description'],
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'', 'style'=>'height:60px', 'tl_class'=>'clr'),
            'sql'                     => "text NULL"
        ),
        'published' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_data_attribute']['published'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange'=>false, 'doNotCopy'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
        )
    )
);
