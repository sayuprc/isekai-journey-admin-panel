<?php

declare(strict_types=1);

namespace Media\Application\UseCase\Get;

use Media\Application\Query\MediaReferencedSong;
use Media\Domain\Models\Media;

readonly class GetOutputData
{
    /**
     * @param list<MediaReferencedSong> $songs
     */
    public function __construct(
        public Media $media,
        public array $songs,
    ) {
    }
}
