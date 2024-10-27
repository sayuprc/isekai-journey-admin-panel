<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Domain\Entities;

use App\Features\JourneyLog\Domain\Exceptions\InvalidDomainException;

readonly class Story
{
    private const int MIN_LENGTH = 1;

    private const int MAX_LENGTH = 255;

    /**
     * @throws InvalidDomainException
     */
    public function __construct(public string $value)
    {
        $length = mb_strlen($this->value);

        if ($length < self::MIN_LENGTH || self::MAX_LENGTH < $length) {
            throw new InvalidDomainException('story must be between ' . self::MIN_LENGTH . ' and ' . self::MAX_LENGTH . ' characters');
        }
    }
}
