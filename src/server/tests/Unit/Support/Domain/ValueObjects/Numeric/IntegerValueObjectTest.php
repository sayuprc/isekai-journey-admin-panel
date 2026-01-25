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
}

readonly class IntegerObject extends IntegerValueObject
{
}
