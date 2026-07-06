<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\SongId;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;

/**
 * 収録曲。次の 2 形態のどちらか一方のみを取る（XOR）。
 *
 * - 参照トラック: songId あり / title なし（Song 集約を参照する）
 * - タイトルのみトラック: songId なし / title あり（管理対象外楽曲。表示専用）
 */
readonly class Track
{
    private function __construct(
        public ?SongId $songId,
        public ?TrackTitle $title,
        public OrderNo $trackNo,
    ) {
    }

    /**
     * @return Result<self, EntityRuleViolationError>
     */
    public static function create(?SongId $songId, ?TrackTitle $title, OrderNo $trackNo): Result
    {
        if (is_null($songId) === is_null($title)) {
            return new Err(new EntityRuleViolationError('media', '収録曲には楽曲かタイトルのどちらか一方のみを指定してください。'));
        }

        return new Ok(new self($songId, $title, $trackNo));
    }

    public static function reconstruct(?string $songId, ?string $title, int $trackNo): self
    {
        return new self(
            is_null($songId) ? null : SongId::reconstruct($songId),
            is_null($title) ? null : TrackTitle::reconstruct($title),
            OrderNo::reconstruct($trackNo),
        );
    }

    /**
     * @return array{song_id: ?string, title: ?string, track_no: int}
     */
    public function toArray(): array
    {
        return [
            'song_id' => $this->songId?->value,
            'title' => $this->title?->value,
            'track_no' => $this->trackNo->value,
        ];
    }
}
