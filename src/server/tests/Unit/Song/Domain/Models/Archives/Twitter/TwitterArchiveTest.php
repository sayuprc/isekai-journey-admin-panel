<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Archives\Twitter;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Archives\Archive;
use Song\Domain\Models\Archives\ArchivedOn;
use Song\Domain\Models\Archives\ArchiveId;
use Song\Domain\Models\Archives\ArchiveName;
use Song\Domain\Models\Archives\Twitter\PostUrl;
use Song\Domain\Models\Archives\Twitter\TwitterArchive;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class TwitterArchiveTest extends TestCase
{
    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $instance = new TwitterArchive(
            new ArchiveId($this->generateUuid()),
            new ArchiveName('アーカイブ'),
            new PostUrl('https://example.com'),
            new ArchivedOn(new ImmutableDate()),
            new OrderNo(1)
        );

        $this->assertInstanceOf(Archive::class, $instance);
    }
}
