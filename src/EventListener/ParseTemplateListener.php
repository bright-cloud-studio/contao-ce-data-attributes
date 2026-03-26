<?php

namespace Bcs\DataAttributesBundle\EventListener;

use Contao\Database;
use Contao\Template;

class ParseTemplateListener
{
    public function __invoke(Template $template): void
    {
        // Only process content element templates
        if (!str_starts_with($template->getName(), 'ce_')) {
            return;
        }

        $raw = $template->ce_data_attributes ?? null;

        if (empty($raw)) {
            $template->dataAttributeString = '';
            return;
        }

        $rows = unserialize($raw);

        if (!is_array($rows) || empty($rows)) {
            $template->dataAttributeString = '';
            return;
        }

        $db     = Database::getInstance();
        $parts  = [];

        foreach ($rows as $row) {
            $attrId = (int) ($row['attribute_id'] ?? 0);
            $value  = (string) ($row['value'] ?? '');

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
                htmlspecialchars($value)
            );
        }

        $template->dataAttributeString = implode(' ', $parts);
    }
}
