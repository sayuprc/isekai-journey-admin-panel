<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects\Boolean;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\Boolean\BooleanValueObject;
use Tests\TestCase;

class BooleanValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideProperlyStoresValue')]
    public function properlyStoresValue(bool $value): void
    {
        $this->assertSame($value, new BooleanObject($value)->value);
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            [true],
            [false],
        ];
    }
}

readonly class BooleanObject extends BooleanValueObject
{
}
