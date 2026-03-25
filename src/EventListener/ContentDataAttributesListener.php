<?php

namespace App\EventListener;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\StringUtil;
use Contao\Template;

#[AsHook('parseTemplate')]
class ContentDataAttributesListener
{
    public function __invoke(Template $template): void
    {
        // Only apply to content element templates
        if (!str_starts_with($template->getName(), 'ce_')) {
            return;
        }

        /** @var ContentModel|null $model */
        $model = $template->arrData['model'] ?? null;

        if (!$model instanceof ContentModel || empty($model->customDataAttributes)) {
            return;
        }

        $pairs = StringUtil::deserialize($model->customDataAttributes, true);
        $attrs = [];

        foreach ($pairs as $pair) {
            $key   = trim($pair['key'] ?? '');
            $value = trim($pair['value'] ?? '');

            if ($key === '') {
                continue;
            }

            // Ensure key is prefixed with data- if not already
            if (!str_starts_with($key, 'data-')) {
                $key = 'data-' . $key;
            }

            $attrs[] = sprintf('%s="%s"', htmlspecialchars($key), htmlspecialchars($value));
        }

        if (!empty($attrs)) {
            // Make attributes available in the template
            $template->customDataAttributes = implode(' ', $attrs);
        }
    }
}
