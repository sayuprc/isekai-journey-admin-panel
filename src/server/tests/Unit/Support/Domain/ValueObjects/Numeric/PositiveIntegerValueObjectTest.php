<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects\Numeric;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\Numeric\PositiveIntegerValueObject;
use Tests\TestCase;

class PositiveIntegerValueObjectTest extends TestCase
{
    #[Test]
    public function properlyStoresValue(): void
    {
        $result = PositiveIntegerObject::create(1);

        $this->assertTrue($result->isOk());
        $this->assertSame(1, $result->unwrap()->value);
    }

    #[Test]
    #[DataProvider('provideThrowExceptionWhenInvalidValue')]
    public function throwExceptionWhenInvalidValue(int $value): void
    {
        $result = PositiveIntegerObject::create($value);

        $this->assertTrue($result->isErr());
        $this->assertSame('正の整数ではありません: ' . $value, $result->unwrapErr()->message);
    }

    public static function provideThrowExceptionWhenInvalidValue(): array
    {
        return [
            [-1],
            [0],
        ];
    }

    #[Test]
    #[DataProvider('equalsEvaluatesEquivalenceProvider')]
    public function equalsEvaluatesEquivalence(PositiveIntegerObject $object, PositiveIntegerValueObject $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsEvaluatesEquivalenceProvider(): array
    {
        return [
            [
                PositiveIntegerObject::reconstruct(1),
                PositiveIntegerObject::reconstruct(1),
                true,
            ],
            [
                PositiveIntegerObject::reconstruct(1),
                PositiveIntegerObject::reconstruct(2),
                false,
            ],
            [
                PositiveIntegerObject::reconstruct(1),
                OtherPositiveIntegerObject::reconstruct(1),
                false,
            ],
            [
                PositiveIntegerObject::reconstruct(1),
                OtherPositiveIntegerObject::reconstruct(2),
                false,
            ],
        ];
    }
}

readonly class PositiveIntegerObject extends PositiveIntegerValueObject
{
}

readonly class OtherPositiveIntegerObject extends PositiveIntegerValueObject
{
}
