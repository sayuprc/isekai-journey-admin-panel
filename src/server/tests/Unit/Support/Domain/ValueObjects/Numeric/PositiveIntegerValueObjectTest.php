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
}

readonly class PositiveIntegerObject extends PositiveIntegerValueObject
{
}
