<?php

declare(strict_types=1);

namespace Tests\Unit\Features\JourneyLog\Domain\Entities;

use App\Features\JourneyLog\Domain\Entities\Story;
use App\Features\JourneyLog\Domain\Exceptions\InvalidDomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StoryTest extends TestCase
{
    #[Test]
    public function successful(): void
    {
        $value1 = '1';
        $value2 = str_repeat('a', 255);

        $story1 = new Story($value1);
        $story2 = new Story($value2);

        $this->assertSame($value1, $story1->value);
        $this->assertSame($value2, $story2->value);
    }

    #[Test]
    public function emptyValue(): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('story must be between 1 and 255 characters');

        new Story('');
    }

    #[Test]
    public function overLength(): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('story must be between 1 and 255 characters');

        new Story(str_repeat('a', 256));
    }
}
