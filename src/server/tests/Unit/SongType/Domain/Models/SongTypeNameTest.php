<?php

declare(strict_types=1);

namespace Tests\Unit\SongType\Domain\Models;

use PHPUnit\Framework\Attributes\Test;
use SongType\Domain\Models\SongTypeName;
use Support\Domain\ValueObjects\String\StringValueObject;
use Tests\TestCase;

class SongTypeNameTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(StringValueObject::class, new SongTypeName('楽曲種別'));
    }
}
