<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Domain\Models;

use JourneyLog\Domain\Models\Url;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\String\StringValueObject;
use Tests\TestCase;

class UrlTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(StringValueObject::class, new Url('https://example.com'));
    }
}
