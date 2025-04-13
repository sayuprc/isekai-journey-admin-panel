<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Title;
use Support\Domain\ValueObjects\StringValueObject;
use Tests\TestCase;

class TitleTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(StringValueObject::class, new Title('楽曲'));
    }
}
