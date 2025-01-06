<?php

declare(strict_types=1);

namespace Shared\Domain;

use Shared\Domain\Exceptions\InvalidDomainException;

abstract class UuidValueObject extends StringValueObject
{
    /**
     * @throws InvalidDomainException
     */
    public function __construct(string $value)
    {
        if (! preg_match('/\A[\da-fA-F]{8}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{12}\z/', $value)) {
            throw new InvalidDomainException('Value format is invalid');
        }

        parent::__construct($value);
    }
}
