<?php

/**
 * @copyright  Bright Cloud Studio
 * @author     Bright Cloud Studio
 * @package    Contao Content Element Data Attributes
 * @license    LGPL-3.0+
 * @see        https://github.com/bright-cloud-studio/contao-ce-data-attributes
 */

namespace Bcs\DataAttributesBundle\Twig\Extension;

use Contao\StringUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DataAttributesExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('data_attributes', [$this, 'parseDataAttributes']),
        ];
    }

    /**
     * Deserializes a keyValueWizard value and returns a normalized
     * associative array suitable for use with attrs().mergeWith().
     *
     * Keys are automatically prefixed with "data-" if not already present.
     */
    public function parseDataAttributes(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        $pairs = StringUtil::deserialize($value, true);
        $result = [];

        foreach ($pairs as $pair) {
            $key = trim($pair['key'] ?? '');

            if ($key === '') {
                continue;
            }

            if (!str_starts_with($key, 'data-')) {
                $key = 'data-' . $key;
            }

            $result[$key] = trim($pair['value'] ?? '');
        }

        return $result;
    }
}
