<?php

declare(strict_types=1);

namespace Media\Domain\Models;

readonly class Media
{
    public function __construct(
        public MediaId $mediaId,
        public MediaTitle $title,
        public MediaUrl $url,
        public MediaType $type,
        public bool $isDisplay,
    ) {
    }

    public static function reconstruct(
        string $mediaId,
        string $title,
        string $url,
        int $type,
        bool $isDisplay,
    ): self {
        return new self(
            MediaId::reconstruct($mediaId),
            MediaTitle::reconstruct($title),
            MediaUrl::reconstruct($url),
            MediaType::from($type),
            $isDisplay,
        );
    }

    /**
     * @return array{media_id: string, title: string, url: string, type: value-of<MediaType>, is_display: bool}
     */
    public function toArray(): array
    {
        return [
            'media_id' => $this->mediaId->value,
            'title' => $this->title->value,
            'url' => $this->url->value,
            'type' => $this->type->value,
            'is_display' => $this->isDisplay,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->mediaId->equals($other->mediaId);
    }
}
