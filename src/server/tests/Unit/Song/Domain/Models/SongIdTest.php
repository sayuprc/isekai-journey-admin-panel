<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongId;
use Support\Domain\ValueObjects\String\UuidValueObject;
use Tests\TestCase;

class SongIdTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(UuidValueObject::class, new SongId($this->generateUuid()));
    }
}
