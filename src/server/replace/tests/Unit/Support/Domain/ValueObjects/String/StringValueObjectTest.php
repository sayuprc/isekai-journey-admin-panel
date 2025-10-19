<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects\String;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\String\StringValueObject;
use Tests\TestCase;

class StringValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideProperlyStoresValue')]
    public function properlyStoresValue(string $value): void
    {
        $this->assertSame($value, new StringObject($value)->value);
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            [''],
            ['value'],
        ];
    }
}

readonly class StringObject extends StringValueObject
{
}
