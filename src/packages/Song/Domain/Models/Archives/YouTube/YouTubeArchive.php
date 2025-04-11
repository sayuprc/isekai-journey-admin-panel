<?php

declare(strict_types=1);

namespace Song\Domain\Models\Archives\YouTube;

use Song\Domain\Models\Archives\Archive;
use Song\Domain\Models\Archives\ArchivedOn;
use Song\Domain\Models\Archives\ArchiveId;
use Song\Domain\Models\Archives\ArchiveName;
use Support\Domain\ValueObjects\OrderNo;

class YouTubeArchive implements Archive
{
    public function __construct(
        public readonly ArchiveId $archiveId,
        public readonly ArchiveName $archiveName,
        public readonly VideoUrl $videoUrl,
        public readonly ThumbnailUrl $thumbnailUrl,
        public readonly ArchivedOn $archivedOn,
        public readonly OrderNo $orderNo,
    ) {
    }
}
