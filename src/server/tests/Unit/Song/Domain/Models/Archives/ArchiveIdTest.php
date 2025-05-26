<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Archives;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Archives\ArchiveId;
use Support\Domain\ValueObjects\String\UuidValueObject;
use Tests\TestCase;

class ArchiveIdTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(UuidValueObject::class, new ArchiveId($this->generateUuid()));
    }
}
