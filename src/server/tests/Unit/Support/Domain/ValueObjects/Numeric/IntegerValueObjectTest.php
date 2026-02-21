<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects\Numeric;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\Numeric\IntegerValueObject;
use Tests\TestCase;

class IntegerValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideProperlyStoresValue')]
    public function properlyStoresValue(int $value): void
    {
        $result = IntegerObject::create($value);

        $this->assertTrue($result->isOk());
        $this->assertSame($value, $result->unwrap()->value);
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }

    #[Test]
    #[DataProvider('equalsEvaluatesEquivalenceProvider')]
    public function equalsEvaluatesEquivalence(IntegerObject $object, IntegerValueObject $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsEvaluatesEquivalenceProvider(): array
    {
        return [
            [
                IntegerObject::reconstruct(1),
                IntegerObject::reconstruct(1),
                true,
            ],
            [
                IntegerObject::reconstruct(1),
                IntegerObject::reconstruct(2),
                false,
            ],
            [
                IntegerObject::reconstruct(1),
                OtherIntegerObject::reconstruct(1),
                false,
            ],
            [
                IntegerObject::reconstruct(1),
                OtherIntegerObject::reconstruct(2),
                false,
            ],
        ];
    }
}

readonly class IntegerObject extends IntegerValueObject
{
}

readonly class OtherIntegerObject extends IntegerValueObject
{
}
