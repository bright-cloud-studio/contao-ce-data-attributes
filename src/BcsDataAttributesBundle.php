<?php

/**
 * @copyright  Bright Cloud Studio
 * @author     Bright Cloud Studio
 * @package    Contao Content Element Data Attributes
 * @license    LGPL-3.0+
 * @see        https://github.com/bright-cloud-studio/contao-ce-data-attributes
 */

namespace Bcs\DataAttributesBundle;

use Bcs\DataAttributesBundle\DependencyInjection\BcsDataAttributesExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BcsDataAttributesBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new BcsDataAttributesExtension();
    }
}
