<?php

declare(strict_types=1);

namespace Song\Domain\Entities;

class SongLink
{
    public function __construct(
        public readonly SongLinkId $songLinkId,
        public readonly SongLinkName $songLinkName,
        public readonly Url $url,
        public readonly OrderNo $orderNo,
    ) {
    }
}
