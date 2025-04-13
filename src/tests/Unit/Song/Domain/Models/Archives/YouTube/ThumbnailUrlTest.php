<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Archives\YouTube;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Archives\YouTube\ThumbnailUrl;
use Support\Domain\ValueObjects\StringValueObject;
use Tests\TestCase;

class ThumbnailUrlTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(StringValueObject::class, new ThumbnailUrl('https://example.com'));
    }
}
