<?php

declare(strict_types=1);

namespace Tests\Unit\Features\JourneyLogLinkType\Domain\Entities;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use App\Shared\Domain\Exceptions\InvalidDomainException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JourneyLogLinkTypeNameTest extends TestCase
{
    #[Test]
    public function successful(): void
    {
        $value = 'a';

        $story = new JourneyLogLinkTypeName($value);

        $this->assertSame($value, $story->value);
    }

    #[Test]
    public function emptyValue(): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->expectExceptionMessage('journey log link type name must be at least 1 characters');

        new JourneyLogLinkTypeName('');
    }
}
