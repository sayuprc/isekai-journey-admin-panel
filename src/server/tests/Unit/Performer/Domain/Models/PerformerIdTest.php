<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Domain\Models;

use Performer\Domain\Models\PerformerId;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\String\UuidValueObject;
use Tests\TestCase;

class PerformerIdTest extends TestCase
{
    #[Test]
    public function isInstanceOfUuidValueObject(): void
    {
        $this->assertInstanceOf(UuidValueObject::class, new PerformerId($this->generateUuid()));
    }
}
