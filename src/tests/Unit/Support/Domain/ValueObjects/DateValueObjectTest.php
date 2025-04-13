<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\DateValueObject;
use Tests\TestCase;

class DateValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideTimeIsZero')]
    public function timeIsZero(DateTimeInterface $value): void
    {
        $this->assertSame($value->format('Y-m-d 00:00:00.000000'), new Date($value)->value->format('Y-m-d H:i:s.u'));
    }

    public static function provideTimeIsZero(): array
    {
        return [
            [new DateTime()],
            [new DateTimeImmutable()],
            [new DateTime('2019-12-09 10:28:31')],
            [new DateTimeImmutable('2019-12-09 10:28:31')],
            [new DateTime('2019-12-09 10:28:31.000001')],
            [new DateTimeImmutable('2019-12-09 10:28:31.282930')],
        ];
    }
}

class Date extends DateValueObject
{
}
