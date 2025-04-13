<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Domain\Models;

use JourneyLog\Domain\Models\JourneyLogLinkName;
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
