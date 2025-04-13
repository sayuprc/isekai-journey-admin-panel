<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Archives;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Archives\ArchiveName;
use Support\Domain\ValueObjects\StringValueObject;
use Tests\TestCase;

class ArchiveNameTest extends TestCase
{
    #[Test]
    public function isExtendsSpecificClass(): void
    {
        $this->assertInstanceOf(StringValueObject::class, new ArchiveName('アーカイブ'));
    }
}
