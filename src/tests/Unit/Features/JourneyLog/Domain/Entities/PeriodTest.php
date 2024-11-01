<?php

declare(strict_types=1);

namespace Tests\Unit\Features\JourneyLog\Domain\Entities;

use App\Features\JourneyLog\Domain\Entities\Period;
use App\Shared\Domain\Exceptions\InvalidDomainException;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PeriodTest extends TestCase
{
    #[Test]
    public function singleDay(): void
    {
        $period = new Period(new DateTimeImmutable('2019-12-09'), new DateTimeImmutable('2019-12-09'));

        $this->assertTrue($period->isSingleDay());
    }

    #[Test]
    public function notSingleDay(): void
    {
        $period = new Period(new DateTimeImmutable('2019-12-09'), new DateTimeImmutable('2019-12-10'));

        $this->assertFalse($period->isSingleDay());
    }

    #[Test]
    public function invalidPeriodRange(): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('fromOn needs to be before toOn');

        new Period(new DateTimeImmutable('2019-12-09'), new DateTimeImmutable('2019-12-08'));
    }
}
