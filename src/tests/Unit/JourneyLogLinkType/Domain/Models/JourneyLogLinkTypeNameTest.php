<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLogLinkType\Domain\Models;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\ValueObjects\String\StringValueObject;
use Tests\TestCase;

class JourneyLogLinkTypeNameTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(StringValueObject::class, new JourneyLogLinkTypeName('value'));
    }

    #[Test]
    #[DataProvider('provideProperlyStoresValue')]
    public function properlyStoresValue(string $value): void
    {
        $this->assertSame($value, new JourneyLogLinkTypeName($value)->value);
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            ['a'],
            ['あ'],
            ['a b c あ'],
        ];
    }

    #[Test]
    public function throwExceptionWhenEmptyValue(): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('journey log link type name must be at least 1 characters');

        new JourneyLogLinkTypeName('');
    }
}
