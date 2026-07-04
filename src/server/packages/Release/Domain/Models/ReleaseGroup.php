<?php

declare(strict_types=1);

namespace Release\Domain\Models;

readonly class ReleaseGroup
{
    public function __construct(
        public ReleaseGroupId $releaseGroupId,
        public ReleaseGroupTitle $title,
        public ReleaseGroupType $type,
        public Description $description,
        public bool $isDisplay,
    ) {
    }

    public static function reconstruct(
        string $releaseGroupId,
        string $title,
        int $type,
        string $description,
        bool $isDisplay,
    ): self {
        return new self(
            ReleaseGroupId::reconstruct($releaseGroupId),
            ReleaseGroupTitle::reconstruct($title),
            ReleaseGroupType::from($type),
            Description::reconstruct($description),
            $isDisplay,
        );
    }

    /**
     * @return array{release_group_id: string, title: string, type: value-of<ReleaseGroupType>, description: string, is_display: bool}
     */
    public function toArray(): array
    {
        return [
            'release_group_id' => $this->releaseGroupId->value,
            'title' => $this->title->value,
            'type' => $this->type->value,
            'description' => $this->description->value,
            'is_display' => $this->isDisplay,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->releaseGroupId->equals($other->releaseGroupId);
    }
}
