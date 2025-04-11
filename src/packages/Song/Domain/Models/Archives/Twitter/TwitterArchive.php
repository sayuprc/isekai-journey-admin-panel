<?php

declare(strict_types=1);

namespace Song\Domain\Models\Archives\Twitter;

use Song\Domain\Models\Archives\Archive;
use Song\Domain\Models\Archives\ArchivedOn;
use Song\Domain\Models\Archives\ArchiveId;
use Song\Domain\Models\Archives\ArchiveName;
use Support\Domain\ValueObjects\OrderNo;

class TwitterArchive implements Archive
{
    public function __construct(
        public readonly ArchiveId $archiveId,
        public readonly ArchiveName $archiveName,
        public readonly PostUrl $postUrl,
        public readonly ArchivedOn $archivedOn,
        public readonly OrderNo $orderNo,
    ) {
    }
}
