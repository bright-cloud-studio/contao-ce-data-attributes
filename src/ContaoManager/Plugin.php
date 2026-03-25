<?php

/**
 * @copyright  Bright Cloud Studio
 * @author     Bright Cloud Studio
 * @package    Contao Content Element Data Attributes
 * @license    LGPL-3.0+
 * @see	       https://github.com/bright-cloud-studio/contao-ce-data-attributes
 */

namespace Bcs\DataAttributesBundle\ContaoManager;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create('Bcs\DataAttributesBundle\BcsDataAttributesBundle')
                ->setLoadAfter(['Contao\CoreBundle\ContaoCoreBundle', 'ErdmannFreunde\ThemeToolbox\ErdmannFreundeThemeToolboxBundle']),
        ];
    }
}
