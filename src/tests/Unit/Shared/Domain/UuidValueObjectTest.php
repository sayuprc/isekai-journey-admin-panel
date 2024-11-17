<?php

declare(strict_types=1);

namespace Shared\Domain;

use App\Shared\Domain\Exceptions\InvalidDomainException;
use App\Shared\Domain\UuidValueObject;
use PHPUnit\Framework\Attributes\Test;
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
    public function emptyValue(): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('Value format is invalid');

        new Uuid('');
    }

    #[Test]
    public function invalidFormat(): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('Value format is invalid');

        new Uuid('invalid format');
    }
}

class Uuid extends UuidValueObject
{
}
