<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\UuidValueObject;
use Tests\TestCase;

class UuidValueObjectTest extends TestCase
{
    #[Test]
    public function successful(): void
    {
        $value = $this->generateUuid();

        $uuid = new Uuid($value);

        $this->assertSame($value, $uuid->value);
    }

    #[Test]
    #[DataProvider('invalidData')]
    public function formatIsInvalid(string $value): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('Value format is invalid');

        new Uuid($value);
    }

    public static function invalidData(): array
    {
        return [
            [''],
            ['invalid format'],
        ];
    }
}

class Uuid extends UuidValueObject
{
}
