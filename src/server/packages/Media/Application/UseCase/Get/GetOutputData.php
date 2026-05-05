<?php

declare(strict_types=1);

namespace Media\Application\UseCase\Get;

use Media\Domain\Models\Media;
use Media\Domain\Models\MediaReferencedSong;

readonly class GetOutputData
{
    /**
     * @param list<MediaReferencedSong> $songs
     */
    public function __construct(
        public Media $media,
        public array $songs,
    )
    {
    }
}
