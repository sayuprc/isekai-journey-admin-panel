<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects\Date;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\Date\ImmutableDateValueObject;
use Tests\TestCase;

class ImmutableDateValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideTimeIsZero')]
    public function timeIsZero(ImmutableDate $value): void
    {
        $result = Date::create($value);

        $this->assertTrue($result->isOk());
        $this->assertSame($value->format('Y-m-d 00:00:00.000000'), $result->unwrap()->value->format('Y-m-d H:i:s.u'));
    }

    public static function provideTimeIsZero(): array
    {
        return [
            [new ImmutableDate()],
            [new ImmutableDate('2019-12-09 10:28:31')],
            [new ImmutableDate('2019-12-09 10:28:31.000001')],
            [new ImmutableDate('2019-12-09 10:28:31.282930')],
        ];
    }
}

readonly class Date extends ImmutableDateValueObject
{
}
