<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Domain\Models;

use DateTime;
use JourneyLog\Domain\Models\FromOn;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\DateTime\DateValueObject;
use Tests\TestCase;

class FromOnTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(DateValueObject::class, new FromOn(new DateTime()));
    }
}
