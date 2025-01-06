<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Domain;

use PHPUnit\Framework\Attributes\Test;
use Shared\Domain\Exceptions\InvalidDomainException;
use Shared\Domain\UuidValueObject;
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
