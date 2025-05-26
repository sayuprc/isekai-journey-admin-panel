<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Archives\YouTube;

use DateTime;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Archives\Archive;
use Song\Domain\Models\Archives\ArchivedOn;
use Song\Domain\Models\Archives\ArchiveId;
use Song\Domain\Models\Archives\ArchiveName;
use Song\Domain\Models\Archives\YouTube\ThumbnailUrl;
use Song\Domain\Models\Archives\YouTube\VideoUrl;
use Song\Domain\Models\Archives\YouTube\YouTubeArchive;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class YouTubeArchiveTest extends TestCase
{
    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $instance = new YouTubeArchive(
            new ArchiveId($this->generateUuid()),
            new ArchiveName('アーカイブ'),
            new VideoUrl('https://example.com'),
            new ThumbnailUrl('https://example.com'),
            new ArchivedOn(new DateTime()),
            new OrderNo(1)
        );

        $this->assertInstanceOf(Archive::class, $instance);
    }
}
