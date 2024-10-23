<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Domain\Entities;

readonly class OrderNo
{
    public function __construct(public int $value)
    {
    }
}
