<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Util;

class TypeConverter
{
    public const DEFAULT = 1;
    public const BOOL = 2;
    public const INT = 3;
    public const FLOAT = 4;
    public const STRING = 5;

    public function convert($value, int $type = self::DEFAULT)
    {
        switch ($type) {
            case self::BOOL:
                $result = $this->castToBoolean($value);
                break;
            case self::INT:
                $result = $this->castToInteger($value);
                break;
            case self::FLOAT:
                $result = $this->castToFloat($value);
                break;
            case self::STRING:
                $result = $this->castToString($value);
                break;
            default:
                $result = $value;
        }

        return $result;
    }

    protected function castToInteger($value): int
    {
        return (int) $value;
    }

    protected function castToFloat($value): float
    {
        return (float) $value;
    }

    protected function castToBoolean($value): bool
    {
        if (is_string($value)) {
            return filter_var(trim($value), FILTER_VALIDATE_BOOLEAN);
        }
        return (bool) $value;
    }

    protected function castToString($value): string
    {
        return (string) $value;
    }
}
