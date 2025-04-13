<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Domain\Models;

use DateTime;
use JourneyLog\Domain\Models\ToOn;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\DateValueObject;
use Tests\TestCase;

class ToOnTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(DateValueObject::class, new ToOn(new DateTime()));
    }
}
