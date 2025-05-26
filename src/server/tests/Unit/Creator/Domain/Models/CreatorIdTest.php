<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Domain\Models;

use Creator\Domain\Models\CreatorId;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\String\UuidValueObject;
use Tests\TestCase;

class CreatorIdTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(UuidValueObject::class, new CreatorId($this->generateUuid()));
    }
}
