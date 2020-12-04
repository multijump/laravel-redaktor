<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Guard;

final class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * @var array $allowedTypes
     * @var mixed $actual
     */
    public function __construct(array $allowedTypes, $actual)
    {
        if (empty($allowedTypes)) {
            throw new \UnexpectedValueException('$allowedTypes parameter must specify at least one type.');
        }

        parent::__construct(
            sprintf(
                'Instance of %s expected. Got %s.',
                self::toString($allowedTypes),
                is_object($actual)
                    ? get_class($actual)
                    : gettype($actual)
            )
        );
    }

    private static function toString(array $allowedTypes): string
    {
        $lastAllowedType = array_pop($allowedTypes);

        return empty($allowedTypes)
            ? $lastAllowedType
            : implode(', ', $allowedTypes) . " or $lastAllowedType";
    }
}
