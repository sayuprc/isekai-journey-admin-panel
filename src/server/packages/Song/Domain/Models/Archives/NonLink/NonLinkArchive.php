<?php

declare(strict_types=1);

namespace Song\Domain\Models\Archives\NonLink;

use Song\Domain\Models\Archives\Archive;
use Song\Domain\Models\Archives\ArchivedOn;
use Song\Domain\Models\Archives\ArchiveId;
use Support\Domain\ValueObjects\OrderNo;

class NonLinkArchive implements Archive
{
    public function __construct(
        public readonly ArchiveId $archiveId,
        public readonly ArchivedOn $archivedOn,
        public readonly OrderNo $orderNo,
    ) {
    }
}
