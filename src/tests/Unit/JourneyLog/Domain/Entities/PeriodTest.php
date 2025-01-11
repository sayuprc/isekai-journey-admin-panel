<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Domain\Entities;

use DateTime;
use JourneyLog\Domain\Entities\FromOn;
use JourneyLog\Domain\Entities\Period;
use JourneyLog\Domain\Entities\ToOn;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shared\Domain\Exceptions\InvalidDomainException;

class PeriodTest extends TestCase
{
    #[Test]
    #[DataProvider('validData')]
    public function can(FromOn $fromOn, ToOn $toOn): void
    {
        $period = new Period($fromOn, $toOn);

        $this->assertInstanceOf(Period::class, $period);
    }

    public static function validData(): array
    {
        return [
            [new FromOn(new DateTime('2019-12-09')), new ToOn(new DateTime('2019-12-09'))],
            [new FromOn(new DateTime('2019-12-09')), new ToOn(new DateTime('2019-12-10'))],
            [new FromOn(new DateTime('2019-12-09')), new ToOn(new DateTime('2019-12-11'))],
        ];
    }

    #[Test]
    #[DataProvider('singleDayData')]
    public function singleDay(FromOn $fromOn, ToOn $toOn, bool $expected): void
    {
        $period = new Period($fromOn, $toOn);

        $this->assertSame($expected, $period->isSingleDay());
    }

    public static function singleDayData(): array
    {
        return [
            [new FromOn(new DateTime('2019-12-09')), new ToOn(new DateTime('2019-12-09')), true],
            [new FromOn(new DateTime('2019-12-09')), new ToOn(new DateTime('2019-12-10')), false],
        ];
    }

    #[Test]
    #[DataProvider('invalidData')]
    public function invalidPeriodRange(FromOn $fromOn, ToOn $toOn): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('fromOn needs to be before toOn');

        new Period($fromOn, $toOn);
    }

    public static function invalidData(): array
    {
        return [
            [new FromOn(new DateTime('2019-12-09')), new ToOn(new DateTime('2019-12-08'))],
        ];
    }
}
