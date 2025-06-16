<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Domain\Models;

use DateType\ImmutableDate;
use JourneyLog\Domain\Models\ToOn;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\Date\ImmutableDateValueObject;
use Tests\TestCase;

class ToOnTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(ImmutableDateValueObject::class, new ToOn(new ImmutableDate()));
    }
}
