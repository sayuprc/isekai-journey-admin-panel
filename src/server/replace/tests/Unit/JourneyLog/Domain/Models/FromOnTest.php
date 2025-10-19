<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Domain\Models;

use DateType\ImmutableDate;
use JourneyLog\Domain\Models\FromOn;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\Date\ImmutableDateValueObject;
use Tests\TestCase;

class FromOnTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(ImmutableDateValueObject::class, new FromOn(new ImmutableDate()));
    }
}
