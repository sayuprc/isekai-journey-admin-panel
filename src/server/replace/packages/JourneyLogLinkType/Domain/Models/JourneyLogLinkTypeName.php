<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Domain\Models;

use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\ValueObjects\String\StringValueObject;

readonly class JourneyLogLinkTypeName extends StringValueObject
{
    private const int MIN_LENGTH = 1;

    /**
     * @throws InvalidDomainException
     */
    public function __construct(string $value)
    {
        $length = mb_strlen($value);

        if ($length < self::MIN_LENGTH) {
            throw new InvalidDomainException('journey log link type name must be at least ' . self::MIN_LENGTH . ' characters');
        }

        parent::__construct($value);
    }
}
