<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Archives\NonLink;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Archives\Archive;
use Song\Domain\Models\Archives\ArchivedOn;
use Song\Domain\Models\Archives\ArchiveId;
use Song\Domain\Models\Archives\NonLink\NonLinkArchive;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class NonLinkArchiveTest extends TestCase
{
    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $instance = new NonLinkArchive(
            new ArchiveId($this->generateUuid()),
            new ArchivedOn(new ImmutableDate()),
            new OrderNo(1)
        );

        $this->assertInstanceOf(Archive::class, $instance);
    }
}
