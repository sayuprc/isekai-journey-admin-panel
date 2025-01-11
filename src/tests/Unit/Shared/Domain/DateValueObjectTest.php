<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Shared\Domain\DateValueObject;
use Tests\TestCase;

class DateValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('validData')]
    public function timeIsZero(DateTimeInterface $value): void
    {
        $date = new Date($value);

        $this->assertSame($value->format('Y-m-d 00:00:00.000000'), $date->value->format('Y-m-d H:i:s.u'));
    }

    public static function validData(): array
    {
        return [
            [new DateTime()],
            [new DateTimeImmutable()],
            [new DateTime('2019-12-09 10:28:31')],
            [new DateTimeImmutable('2019-12-09 10:28:31')],
        ];
    }
}

class Date extends DateValueObject
{
}
