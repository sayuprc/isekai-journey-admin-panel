<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects\Numeric;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\ValueObjects\Numeric\PositiveIntegerValueObject;
use Tests\TestCase;

class PositiveIntegerValueObjectTest extends TestCase
{
    #[Test]
    public function properlyStoresValue(): void
    {
        $this->assertSame(1, new PositiveIntegerObject(1)->value);
    }

    #[DataProvider('provideThrowExceptionWhenInvalidValue')]
    public function throwExceptionWhenInvalidValue(int $value): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('Value must be a positive integer');

        new PositiveIntegerObject($value);
    }

    public static function provideThrowExceptionWhenInvalidValue(): array
    {
        return [
            [-1],
            [0],
        ];
    }
}

class PositiveIntegerObject extends PositiveIntegerValueObject
{
}
