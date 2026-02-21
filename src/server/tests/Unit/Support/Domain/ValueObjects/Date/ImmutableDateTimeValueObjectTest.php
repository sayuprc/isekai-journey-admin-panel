<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects\Date;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\Date\ImmutableDateTimeValueObject;
use Tests\TestCase;

class ImmutableDateTimeValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideProperlyStoresValue')]
    public function properlyStoresValue(DateTimeImmutable $value): void
    {
        $result = ImmutableDateTime::create($value);

        $this->assertTrue($result->isOk());
        $this->assertSame($value->format('Y-m-d H:i:s'), $result->unwrap()->value->format('Y-m-d H:i:s'));
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            [new DateTimeImmutable()],
        ];
    }

    #[Test]
    #[DataProvider('equalsEvaluatesEquivalenceProvider')]
    public function equalsEvaluatesEquivalence(ImmutableDateTime $object, ImmutableDateTimeValueObject $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsEvaluatesEquivalenceProvider(): array
    {
        $now = new DateTimeImmutable();

        return [
            [
                ImmutableDateTime::reconstruct($now),
                ImmutableDateTime::reconstruct($now),
                true,
            ],
            [
                ImmutableDateTime::reconstruct($now),
                ImmutableDateTime::reconstruct($now->modify('+1 seconds')),
                false,
            ],
            [
                ImmutableDateTime::reconstruct($now),
                OtherImmutableDateTime::reconstruct($now),
                false,
            ],
            [
                ImmutableDateTime::reconstruct($now),
                OtherImmutableDateTime::reconstruct($now->modify('+1 seconds')),
                false,
            ],
        ];
    }
}

readonly class ImmutableDateTime extends ImmutableDateTimeValueObject
{
}

readonly class OtherImmutableDateTime extends ImmutableDateTimeValueObject
{
}
