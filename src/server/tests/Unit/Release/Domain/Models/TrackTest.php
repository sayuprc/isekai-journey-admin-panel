<?php

declare(strict_types=1);

namespace Tests\Unit\Release\Domain\Models;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\Track;
use Release\Domain\Models\TrackTitle;
use Song\Domain\Models\SongId;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class TrackTest extends TestCase
{
    private const string SONG_ID = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    #[Test]
    public function canCreateReferenceTrack(): void
    {
        $result = Track::create(
            SongId::reconstruct(self::SONG_ID),
            null,
            OrderNo::reconstruct(1),
        );

        $this->assertTrue($result->isOk());

        $track = $result->unwrap();

        $this->assertSame(self::SONG_ID, $track->songId?->value);
        $this->assertNull($track->title);
        $this->assertSame(['song_id' => self::SONG_ID, 'title' => null, 'track_no' => 1], $track->toArray());
    }

    #[Test]
    public function canCreateTitleOnlyTrack(): void
    {
        $result = Track::create(
            null,
            TrackTitle::reconstruct('管理対象外の楽曲'),
            OrderNo::reconstruct(1),
        );

        $this->assertTrue($result->isOk());

        $track = $result->unwrap();

        $this->assertNull($track->songId);
        $this->assertSame('管理対象外の楽曲', $track->title?->value);
        $this->assertSame(['song_id' => null, 'title' => '管理対象外の楽曲', 'track_no' => 1], $track->toArray());
    }

    #[Test]
    public function cannotCreateWithBothSongIdAndTitle(): void
    {
        $result = Track::create(
            SongId::reconstruct(self::SONG_ID),
            TrackTitle::reconstruct('管理対象外の楽曲'),
            OrderNo::reconstruct(1),
        );

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(EntityRuleViolationError::class, $result->unwrapErr());
    }

    #[Test]
    public function cannotCreateWithNeitherSongIdNorTitle(): void
    {
        $result = Track::create(null, null, OrderNo::reconstruct(1));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(EntityRuleViolationError::class, $result->unwrapErr());
    }

    #[Test]
    public function canReconstructReferenceTrack(): void
    {
        $track = Track::reconstruct(self::SONG_ID, null, 1);

        $this->assertSame(self::SONG_ID, $track->songId?->value);
        $this->assertNull($track->title);
        $this->assertSame(1, $track->trackNo->value);
    }

    #[Test]
    public function canReconstructTitleOnlyTrack(): void
    {
        $track = Track::reconstruct(null, '管理対象外の楽曲', 1);

        $this->assertNull($track->songId);
        $this->assertSame('管理対象外の楽曲', $track->title?->value);
        $this->assertSame(1, $track->trackNo->value);
    }
}
