<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Create;

/**
 * @phpstan-type MediumInput array{position: int, formatValue: int, tracks: list<array{songId: string, trackNo: int}>}
 */
readonly class CreateInputData
{
    /**
     * @param list<MediumInput> $media
     */
    public function __construct(
        public string $releaseGroupId,
        public string $name,
        public string $releasedOn,
        public string $description,
        public ?string $jacketArtUrl,
        public bool $isDisplay,
        public int $orderNo,
        public array $media,
    ) {
    }
}
