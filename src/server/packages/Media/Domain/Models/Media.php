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
        public MediaPlatform $platform,
        public ?MediaThumbnail $thumbnail,
    ) {
    }

    public static function youtube(
        MediaId $mediaId,
        MediaTitle $title,
        MediaUrl $url,
        MediaPublishedAt $publishedAt,
        MediaType $type,
        MediaFormat $format,
        bool $isDisplay,
        MediaThumbnail $thumbnail,
    ): self {
        return new self(
            $mediaId,
            $title,
            $url,
            $publishedAt,
            $type,
            $format,
            $isDisplay,
            MediaPlatform::YouTube,
            $thumbnail,
        );
    }

    public static function x(
        MediaId $mediaId,
        MediaTitle $title,
        MediaUrl $url,
        MediaPublishedAt $publishedAt,
        MediaType $type,
        MediaFormat $format,
        bool $isDisplay,
    ): self {
        return new self(
            $mediaId,
            $title,
            $url,
            $publishedAt,
            $type,
            $format,
            $isDisplay,
            MediaPlatform::X,
            null,
        );
    }

    public static function other(
        MediaId $mediaId,
        MediaTitle $title,
        MediaUrl $url,
        MediaPublishedAt $publishedAt,
        MediaType $type,
        MediaFormat $format,
        bool $isDisplay,
    ): self {
        return new self(
            $mediaId,
            $title,
            $url,
            $publishedAt,
            $type,
            $format,
            $isDisplay,
            MediaPlatform::Other,
            null,
        );
    }

    public static function reconstruct(
        string $mediaId,
        string $title,
        string $url,
        ImmutableDate $publishedAt,
        int $type,
        int $format,
        bool $isDisplay,
        int $platform,
        ?string $thumbnailUrl,
    ): self {
        return new self(
            MediaId::reconstruct($mediaId),
            MediaTitle::reconstruct($title),
            MediaUrl::reconstruct($url),
            MediaPublishedAt::reconstruct($publishedAt),
            MediaType::from($type),
            MediaFormat::from($format),
            $isDisplay,
            MediaPlatform::from($platform),
            is_null($thumbnailUrl) ? null : MediaThumbnail::reconstruct($thumbnailUrl),
        );
    }

    /**
     * @return array{media_id: string, title: string, url: string, published_at: string, type: value-of<MediaType>, format: value-of<MediaFormat>, is_display: bool, platform: value-of<MediaPlatform>, thumbnail_url: string|null}
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
            'platform' => $this->platform->value,
            'thumbnail_url' => $this->thumbnail?->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->mediaId->equals($other->mediaId);
    }
}
