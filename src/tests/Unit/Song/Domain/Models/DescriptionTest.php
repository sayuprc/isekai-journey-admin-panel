<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Description;
use Support\Domain\ValueObjects\StringValueObject;
use Tests\TestCase;

class DescriptionTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(StringValueObject::class, new Description('説明'));
    }
}
