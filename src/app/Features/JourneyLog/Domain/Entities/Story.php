<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Domain\Entities;

readonly class Story
{
    public function __construct(public string $value)
    {
    }
}
