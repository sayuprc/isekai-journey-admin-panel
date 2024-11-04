<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Domain\Entities;

use App\Shared\Domain\Exceptions\InvalidDomainException;
use App\Shared\Domain\StringValueObject;

class JourneyLogLinkTypeName extends StringValueObject
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
