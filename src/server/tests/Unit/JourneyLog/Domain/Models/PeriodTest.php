<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Domain\Models;

use DateTime;
use JourneyLog\Domain\Models\FromOn;
use JourneyLog\Domain\Models\Period;
use JourneyLog\Domain\Models\ToOn;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Support\Domain\Exceptions\InvalidDomainException;

class PeriodTest extends TestCase
{
    #[Test]
    #[DataProvider('provideProperlyStoresValue')]
    public function properlyStoresValue(FromOn $fromOn, ToOn $toOn): void
    {
        $instance = new Period($fromOn, $toOn);

        $this->assertSame($fromOn, $instance->fromOn);
        $this->assertSame($toOn, $instance->toOn);
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            [new FromOn(new DateTime('2019-12-09')), new ToOn(new DateTime('2019-12-09'))],
            [new FromOn(new DateTime('2019-12-09')), new ToOn(new DateTime('2019-12-10'))],
            [new FromOn(new DateTime('2019-12-09')), new ToOn(new DateTime('2019-12-11'))],
        ];
    }

    #[Test]
    public function isSingleDay(): void
    {
        $instance = new Period(
            new FromOn(new DateTime('2019-12-09')),
            new ToOn(new DateTime('2019-12-09'))
        );

        $this->assertTrue($instance->isSingleDay());
    }

    #[Test]
    public function isNotSingleDay(): void
    {
        $instance = new Period(
            new FromOn(new DateTime('2019-12-09')),
            new ToOn(new DateTime('2019-12-10'))
        );

        $this->assertFalse($instance->isSingleDay());
    }

    #[Test]
    public function throwExceptionWhenInvalidPeriodRange(): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('fromOn needs to be before toOn');

        new Period(
            new FromOn(new DateTime('2019-12-09')),
            new ToOn(new DateTime('2019-12-08')),
        );
    }
}
