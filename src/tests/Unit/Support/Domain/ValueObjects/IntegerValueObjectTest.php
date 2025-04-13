<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\IntegerValueObject;
use Tests\TestCase;

class IntegerValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideProperlyStoresValue')]
    public function properlyStoresValue(int $value): void
    {
        $this->assertSame($value, new IntegerObject($value)->value);
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }
}

class IntegerObject extends IntegerValueObject
{
}
