<?php

/**
 * @copyright  Bright Cloud Studio
 * @author     Bright Cloud Studio
 * @package    Contao Content Element Data Attributes
 * @license    LGPL-3.0+
 * @see        https://github.com/bright-cloud-studio/contao-ce-data-attributes
 */

namespace Bcs\DataAttributesBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BcsDataAttributesBundle extends Bundle
{
    /**
     * Override getPath() to return the bundle root (parent of src/).
     * This tells Contao to look for resources in contao/dca/,
     * contao/languages/, and contao/templates/ at the bundle root,
     * which correctly preserves subdirectory structure for Twig templates.
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
