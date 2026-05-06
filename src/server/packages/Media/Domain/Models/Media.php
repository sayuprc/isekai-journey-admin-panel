<?php

declare(strict_types=1);

namespace Media\Domain\Models;

use DateType\ImmutableDate;

readonly class Media
{
    public function __construct(
        public MediaId $mediaId,
        public MediaTitle $title,
        public MediaUrl $url,
        public MediaPublishedAt $publishedAt,
        public MediaType $type,
        public MediaFormat $format,
        public bool $isDisplay,
    ) {
    }

    public static function reconstruct(
        string $mediaId,
        string $title,
        string $url,
        ImmutableDate $publishedAt,
        int $type,
        int $format,
        bool $isDisplay,
    ): self {
        return new self(
            MediaId::reconstruct($mediaId),
            MediaTitle::reconstruct($title),
            MediaUrl::reconstruct($url),
            MediaPublishedAt::reconstruct($publishedAt),
            MediaType::from($type),
            MediaFormat::from($format),
            $isDisplay,
        );
    }

    /**
     * @return array{media_id: string, title: string, url: string, published_at: string, type: value-of<MediaType>, format: value-of<MediaFormat>, is_display: bool}
     */
    public function toArray(): array
    {
        return [
            'media_id' => $this->mediaId->value,
            'title' => $this->title->value,
            'url' => $this->url->value,
            'published_at' => $this->publishedAt->value->format('Y-m-d'),
            'type' => $this->type->value,
            'format' => $this->format->value,
            'is_display' => $this->isDisplay,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->mediaId->equals($other->mediaId);
    }
}
