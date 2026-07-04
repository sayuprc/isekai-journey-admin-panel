<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Update;

/**
 * @phpstan-type MediumInput array{position: int, formatValue: int, tracks: list<array{songId: string, trackNo: int}>}
 */
readonly class UpdateInputData
{
    /**
     * @param list<MediumInput> $media
     */
    public function __construct(
        public string $releaseId,
        public string $name,
        public string $releasedOn,
        public string $description,
        public ?string $jacketArtUrl,
        public bool $isDisplay,
        public array $media,
    ) {
    }
}
