<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Archives\Twitter;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Archives\Twitter\PostUrl;
use Support\Domain\ValueObjects\StringValueObject;
use Tests\TestCase;

class PostUrlTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(StringValueObject::class, new PostUrl('https://example.com'));
    }
}
