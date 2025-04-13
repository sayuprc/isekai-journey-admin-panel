<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Domain\Models;

use JourneyLog\Domain\Models\Story;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\ValueObjects\StringValueObject;

class StoryTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(StringValueObject::class, new Story('ストーリー'));
    }

    #[Test]
    #[DataProvider('provideProperlyStoresValue')]
    public function properlyStoresValue(string $value): void
    {
        $this->assertSame($value, new Story($value)->value);
    }

    public static function provideProperlyStoresValue(): array
    {
        return [
            ['1'],
            [str_repeat('a', 255)],
            [str_repeat('あ', 255)],
        ];
    }

    #[Test]
    #[DataProvider('provideThrowExceptionWhenInvalidValue')]
    public function throwExceptionWhenInvalidValue(string $value): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('story must be between 1 and 255 characters');

        new Story($value);
    }

    public static function provideThrowExceptionWhenInvalidValue(): array
    {
        return [
            [''],
            [str_repeat('a', 256)],
            [str_repeat('あ', 256)],
        ];
    }
}
