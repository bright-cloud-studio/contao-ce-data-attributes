<?php

// Existing backend module
$GLOBALS['BE_MOD']['content']['data_attributes'] = [
    'tables' => ['tl_data_attribute'],
];

// Register the custom wizard widget
$GLOBALS['BE_FFL']['dataAttributeWizard'] = \Bcs\DataAttributesBundle\Widget\DataAttributeWizard::class;

// Register the frontend parseTemplate hook
$GLOBALS['TL_HOOKS']['parseTemplate'][] = [\Bcs\DataAttributesBundle\EventListener\ParseTemplateListener::class, '__invoke'];
