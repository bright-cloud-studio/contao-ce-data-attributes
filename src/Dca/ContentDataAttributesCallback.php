<?php

declare(strict_types=1);

namespace Bcs\DataAttributesBundle\Dca;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\Database;
use Contao\Message;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ContentDataAttributesCallback
{
    public function validateAndNormalize(mixed $value): array
    {
        if (!\is_array($value)) {
            return [];
        }

        $attributeRows = Database::getInstance()
            ->execute("SELECT id, name, value_type, allowed_values, is_required FROM tl_data_attribute WHERE published=1")
            ->fetchAllAssoc();

        $attributes = [];

        foreach ($attributeRows as $row) {
            $allowedValues = [];

            if (!empty($row['allowed_values'])) {
                $decoded = json_decode((string) $row['allowed_values'], true);

                if (\is_array($decoded)) {
                    $allowedValues = $decoded;
                }
            }

            $attributes[(int) $row['id']] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'value_type' => (string) $row['value_type'],
                'allowed_values' => $allowedValues,
                'is_required' => (bool) $row['is_required'],
            ];
        }

        $normalized = [];
        $usedAttributeIds = [];

        foreach ($value as $index => $row) {
            $attributeId = isset($row['attribute_id']) ? (int) $row['attribute_id'] : 0;
            $rawValue = isset($row['value']) ? trim((string) $row['value']) : '';

            if (0 === $attributeId && '' === $rawValue) {
                continue;
            }

            if (!$attributeId || !isset($attributes[$attributeId])) {
                throw new \RuntimeException(sprintf('Row %d contains an invalid attribute.', $index + 1));
            }

            if (isset($usedAttributeIds[$attributeId])) {
                throw new \RuntimeException(sprintf(
                    'The attribute "%s" has been selected more than once.',
                    $attributes[$attributeId]['name']
                ));
            }

            $definition = $attributes[$attributeId];
            $normalizedValue = $this->normalizeValue($rawValue, $definition, $index);

            $usedAttributeIds[$attributeId] = true;

            $normalized[] = [
                'attribute_id' => $attributeId,
                'value' => $normalizedValue,
            ];
        }

        return $normalized;
    }

    private function normalizeValue(string $rawValue, array $definition, int $index): string
    {
        $label = $definition['name'] ?? ('Row '.($index + 1));
        $type = $definition['value_type'] ?? 'text';
        $required = (bool) ($definition['is_required'] ?? false);
        $allowedValues = $definition['allowed_values'] ?? [];

        if ($required && '' === $rawValue) {
            throw new \RuntimeException(sprintf('The attribute "%s" requires a value.', $label));
        }

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
                if (!\array_key_exists($rawValue, $allowedValues) && !\in_array($rawValue, $allowedValues, true)) {
                    throw new \RuntimeException(sprintf('The value for "%s" is not one of the allowed options.', $label));
                }

                return $rawValue;

            case 'textarea':
            case 'text':
            default:
                return $rawValue;
        }
    }
}
