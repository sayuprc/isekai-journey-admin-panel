<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLogLinkType\Domain\Models;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\String\UuidValueObject;
use Tests\TestCase;

class JourneyLogLinkTypeIdTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(UuidValueObject::class, new JourneyLogLinkTypeId($this->generateUuid()));
    }
}
