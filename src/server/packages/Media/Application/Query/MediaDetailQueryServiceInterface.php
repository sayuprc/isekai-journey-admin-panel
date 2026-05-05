<?php

declare(strict_types=1);

namespace Media\Application\Query;

use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaReferencedSong;

interface MediaDetailQueryServiceInterface
{
    /**
     * @return list<MediaReferencedSong>
     */
    public function findReferencedSongs(MediaId $mediaId): array;
}
