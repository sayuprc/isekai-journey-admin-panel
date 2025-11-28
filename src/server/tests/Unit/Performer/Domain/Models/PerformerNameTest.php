<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Domain\Models;

use Performer\Domain\Models\PerformerName;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\String\StringValueObject;
use Tests\TestCase;

class PerformerNameTest extends TestCase
{
    #[Test]
    public function isInstanceOfStringValueObject(): void
    {
        $this->assertInstanceOf(StringValueObject::class, new PerformerName('パフォーマー'));
    }
}
