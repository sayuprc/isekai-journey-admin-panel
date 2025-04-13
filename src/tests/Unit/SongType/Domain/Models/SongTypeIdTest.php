<?php

declare(strict_types=1);

namespace Tests\Unit\SongType\Domain\Models;

use PHPUnit\Framework\Attributes\Test;
use SongType\Domain\Models\SongTypeId;
use Support\Domain\ValueObjects\String\UuidValueObject;
use Tests\TestCase;

class SongTypeIdTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(UuidValueObject::class, new SongTypeId($this->generateUuid()));
    }
}
