<?php

declare(strict_types=1);

namespace Tests\Unit\Features\JourneyLog\Domain\Entities;

use DateTimeImmutable;
use JourneyLog\Domain\Entities\FromOn;
use JourneyLog\Domain\Entities\Period;
use JourneyLog\Domain\Entities\ToOn;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shared\Domain\Exceptions\InvalidDomainException;

class PeriodTest extends TestCase
{
    #[Test]
    public function singleDay(): void
    {
        $period = new Period(
            new FromOn(new DateTimeImmutable('2019-12-09')),
            new ToOn(new DateTimeImmutable('2019-12-09'))
        );

        $this->assertTrue($period->isSingleDay());
    }

    #[Test]
    public function notSingleDay(): void
    {
        $period = new Period(
            new FromOn(new DateTimeImmutable('2019-12-09')),
            new ToOn(new DateTimeImmutable('2019-12-10'))
        );

        $this->assertFalse($period->isSingleDay());
    }

    #[Test]
    public function invalidPeriodRange(): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('fromOn needs to be before toOn');

        new Period(
            new FromOn(new DateTimeImmutable('2019-12-09')),
            new ToOn(new DateTimeImmutable('2019-12-08'))
        );
    }
}
