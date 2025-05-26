<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Domain\Models\JourneyLogLink;

use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLinkName;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\String\StringValueObject;
use Tests\TestCase;

class JourneyLogLinkNameTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(StringValueObject::class, new JourneyLogLinkName('リンク'));
    }
}
