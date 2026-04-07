<?php

declare(strict_types=1);

namespace Bcs\DataAttributesBundle\Dca;

use Contao\DataContainer;
use Contao\Database;
use Contao\StringUtil;

class ContentDataAttributesCallback
{
    public function validateAndNormalize(mixed $value, ?DataContainer $dc = null): string
    {
        if ('' === $value || null === $value) {
            return '';
        }

        $rows = StringUtil::deserialize($value, true);

        if (!is_array($rows) || [] === $rows) {
            return '';
        }

        $attributeRows = Database::getInstance()
            ->execute("SELECT id, label, value_type, allowed_values, default_value, published FROM tl_data_attribute WHERE published='1'")
            ->fetchAllAssoc();

        $attributes = [];

        foreach ($attributeRows as $row) {
            $allowedValues = StringUtil::deserialize($row['allowed_values'] ?? null, true);

            $attributes[(int) $row['id']] = [
                'id'             => (int) $row['id'],
                'label'          => (string) $row['label'],
                'value_type'     => (string) ($row['value_type'] ?? 'freetext'),
                'allowed_values' => is_array($allowedValues) ? $allowedValues : [],
                'default_value'  => (string) ($row['default_value'] ?? ''),
            ];
        }

        $normalized = [];
        $usedAttributeIds = [];

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $attributeId = isset($row['attribute_id']) ? (int) $row['attribute_id'] : 0;
            $rawValue = trim((string) ($row['value'] ?? ''));

            if (0 === $attributeId && '' === $rawValue) {
                continue;
            }

            if (!$attributeId || !isset($attributes[$attributeId])) {
                throw new \RuntimeException(sprintf('Row %d contains an invalid attribute.', $index + 1));
            }

            if (isset($usedAttributeIds[$attributeId])) {
                throw new \RuntimeException(sprintf(
                    'The attribute "%s" has been selected more than once.',
                    $attributes[$attributeId]['label']
                ));
            }

            $definition = $attributes[$attributeId];
            $normalizedValue = $this->normalizeValue($rawValue, $definition);

            $usedAttributeIds[$attributeId] = true;

            $normalized[] = [
                'attribute_id' => $attributeId,
                'value'        => $normalizedValue,
            ];
        }

        if ([] === $normalized) {
            return '';
        }

        return serialize($normalized);
    }


    private function normalizeValue(string $rawValue, array $definition): string
    {
        $label         = $definition['label'] ?? 'Attribute';
        $type          = $definition['value_type'] ?? 'freetext';
        $allowedValues = $definition['allowed_values'] ?? [];

        if ('' === $rawValue) {
            return '';
        }

        switch ($type) {
            case 'integer':
                if (!preg_match('/^-?\d+$/', $rawValue)) {
                    throw new \RuntimeException(sprintf('The value for "%s" must be an integer.', $label));
                }

                return (string) (int) $rawValue;

            case 'boolean':
                $value = strtolower($rawValue);

                if (\in_array($value, ['1', 'true', 'yes'], true)) {
                    return 'true';
                }

                if (\in_array($value, ['0', 'false', 'no'], true)) {
                    return 'false';
                }

                throw new \RuntimeException(sprintf('The value for "%s" must be true or false.', $label));

            case 'select':
                $validKeys = array_column($allowedValues, 'key');

                if (!in_array($rawValue, $validKeys, true)) {
                    throw new \RuntimeException(sprintf('The value for "%s" is not one of the allowed options.', $label));
                }

                return $rawValue;

            case 'freetext':
            default:
                return $rawValue;
        }
    }
}
