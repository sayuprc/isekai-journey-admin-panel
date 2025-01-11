<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLogLinkType\Domain\Entities;

use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Shared\Domain\Exceptions\InvalidDomainException;
use Tests\TestCase;

class JourneyLogLinkTypeNameTest extends TestCase
{
    #[Test]
    #[DataProvider('validData')]
    public function canBeInstanced(string $value): void
    {
        $story = new JourneyLogLinkTypeName($value);

        $this->assertSame($value, $story->value);
    }

    public static function validData(): array
    {
        return [
            ['a'],
            ['あ'],
            ['a b c あ'],
        ];
    }

    #[Test]
    #[DataProvider('invalidData')]
    public function invalidValue(string $value): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('journey log link type name must be at least 1 characters');

        new JourneyLogLinkTypeName($value);
    }

    public static function invalidData(): array
    {
        return [
            [''],
        ];
    }
}
