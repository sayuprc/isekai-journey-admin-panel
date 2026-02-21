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
        $result = StringObject::create($value);

        $this->assertTrue($result->isOk());
        $this->assertSame($value, $result->unwrap()->value);
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            [''],
            ['value'],
        ];
    }

    #[Test]
    #[DataProvider('equalsEvaluatesEquivalenceProvider')]
    public function equalsEvaluatesEquivalence(StringObject $object, StringValueObject $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsEvaluatesEquivalenceProvider(): array
    {
        return [
            [
                StringObject::reconstruct('1'),
                StringObject::reconstruct('1'),
                true,
            ],
            [
                StringObject::reconstruct('1'),
                StringObject::reconstruct('2'),
                false,
            ],
            [
                StringObject::reconstruct('1'),
                OtherStringObject::reconstruct('1'),
                false,
            ],
            [
                StringObject::reconstruct('1'),
                OtherStringObject::reconstruct('2'),
                false,
            ],
        ];
    }
}

readonly class StringObject extends StringValueObject
{
}

readonly class OtherStringObject extends StringValueObject
{
}
