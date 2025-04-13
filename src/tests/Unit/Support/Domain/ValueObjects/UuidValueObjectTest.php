<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain\ValueObjects;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\ValueObjects\UuidValueObject;
use Tests\TestCase;

class UuidValueObjectTest extends TestCase
{
    #[Test]
    #[DataProvider('provideProperlyStoresValue')]
    public function properlyStoresValue(string $value): void
    {
        $this->assertSame($value, new Uuid($value)->value);
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            ['dd23940f-6c8c-4316-a3dd-4ab030fcfcac'],
            ['B47477D8-B090-4163-9A2B-C179CC8E692F'],
        ];
    }

    #[Test]
    #[DataProvider('provideThrowExceptionWhenInvalidFormat')]
    public function throwExceptionWhenInvalidFormat(string $value): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('Value format is invalid');

        new Uuid($value);
    }

    public static function provideThrowExceptionWhenInvalidFormat(): array
    {
        return [
            [''],
            ['invalid format'],
            ['GAAAAAAi-AAbA-AsAA-AAAA-AAAAAAcAAeAA'],
            ['gAAAAlAA-AAAn-AtAA-aAAh-AAAAAtAAAAAp'],
        ];
    }
}

class Uuid extends UuidValueObject
{
}
