<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Domain\Models;

use Creator\Domain\Models\CreatorName;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\String\StringValueObject;
use Tests\TestCase;

class CreatorNameTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(StringValueObject::class, new CreatorName('クリエイター'));
    }
}
