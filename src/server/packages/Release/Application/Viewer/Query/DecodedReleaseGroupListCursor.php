<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

readonly class DecodedReleaseGroupListCursor
{
    public function __construct(
        public string $firstReleasedOn,
        public string $releaseGroupId,
    ) {
    }
}
