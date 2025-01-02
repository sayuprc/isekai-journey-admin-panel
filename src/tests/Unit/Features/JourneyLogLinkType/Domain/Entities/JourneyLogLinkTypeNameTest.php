<?php

declare(strict_types=1);

namespace Tests\Unit\Features\JourneyLogLinkType\Domain\Entities;

use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use PHPUnit\Framework\Attributes\Test;
use Shared\Domain\Exceptions\InvalidDomainException;
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
