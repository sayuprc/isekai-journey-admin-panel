<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Domain\Models;

use JourneyLog\Domain\Models\JourneyLogLinkId;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\String\UuidValueObject;
use Tests\TestCase;

class JourneyLogLinkIdTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(UuidValueObject::class, new JourneyLogLinkId($this->generateUuid()));
    }
}
