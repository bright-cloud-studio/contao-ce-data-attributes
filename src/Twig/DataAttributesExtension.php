<?php

namespace Bcs\DataAttributesBundle\Twig;

use Contao\Database;
use Contao\StringUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DataAttributesExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'ce_data_attributes',
                [$this, 'buildAttributeString'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function buildAttributeString(mixed $raw): string
    {
        if (empty($raw)) {
            return '';
        }

        $rows = StringUtil::deserialize($raw, true);

        if (!is_array($rows) || empty($rows)) {
            return '';
        }

        $db    = Database::getInstance();
        $parts = [];

        foreach ($rows as $row) {
            $attrId = (int) ($row['attribute_id'] ?? 0);
            $val    = (string) ($row['value'] ?? '');

            if (!$attrId) {
                continue;
            }

            $attr = $db->prepare(
                "SELECT attribute_name FROM tl_data_attribute WHERE id=? AND published='1'"
            )->limit(1)->execute($attrId);

            if ($attr->numRows < 1) {
                continue;
            }

            $parts[] = sprintf(
                'data-%s="%s"',
                htmlspecialchars($attr->attribute_name),
                htmlspecialchars($val)
            );
        }

        return implode(' ', $parts);
    }
}
