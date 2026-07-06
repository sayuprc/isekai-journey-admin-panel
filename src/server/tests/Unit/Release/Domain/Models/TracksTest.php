<?php

declare(strict_types=1);

namespace Tests\Unit\Release\Domain\Models;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\Tracks;
use Tests\TestCase;

class TracksTest extends TestCase
{
    private const string SONG_ID = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    #[Test]
    public function canCreateFromMixedTracks(): void
    {
        $result = Tracks::fromArray([
            ['songId' => self::SONG_ID, 'title' => null, 'trackNo' => 1],
            ['songId' => null, 'title' => '管理対象外の楽曲', 'trackNo' => 2],
        ]);

        $this->assertTrue($result->isOk());
        $this->assertSame([
            ['song_id' => self::SONG_ID, 'title' => null, 'track_no' => 1],
            ['song_id' => null, 'title' => '管理対象外の楽曲', 'track_no' => 2],
        ], $result->unwrap()->toArray());
    }

    #[Test]
    public function cannotCreateWithBothSongIdAndTitle(): void
    {
        $result = Tracks::fromArray([
            ['songId' => self::SONG_ID, 'title' => '管理対象外の楽曲', 'trackNo' => 1],
        ]);

        $this->assertTrue($result->isErr());
        $this->assertArrayHasKey('media', $result->unwrapErr()->errors);
    }

    #[Test]
    public function cannotCreateWithNeitherSongIdNorTitle(): void
    {
        $result = Tracks::fromArray([
            ['songId' => null, 'title' => null, 'trackNo' => 1],
        ]);

        $this->assertTrue($result->isErr());
        $this->assertArrayHasKey('media', $result->unwrapErr()->errors);
    }

    #[Test]
    public function cannotCreateWithEmptyTitle(): void
    {
        $result = Tracks::fromArray([
            ['songId' => null, 'title' => '', 'trackNo' => 1],
        ]);

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function cannotCreateWithDuplicatedTrackNos(): void
    {
        $result = Tracks::fromArray([
            ['songId' => self::SONG_ID, 'title' => null, 'trackNo' => 1],
            ['songId' => null, 'title' => '管理対象外の楽曲', 'trackNo' => 1],
        ]);

        $this->assertTrue($result->isErr());
        $this->assertSame(['同じ曲順を複数指定することはできません。'], $result->unwrapErr()->errors['media']);
    }

    #[Test]
    public function canReconstruct(): void
    {
        $tracks = Tracks::reconstruct([
            ['songId' => self::SONG_ID, 'title' => null, 'trackNo' => 1],
            ['songId' => null, 'title' => '管理対象外の楽曲', 'trackNo' => 2],
        ]);

        $this->assertSame([
            ['song_id' => self::SONG_ID, 'title' => null, 'track_no' => 1],
            ['song_id' => null, 'title' => '管理対象外の楽曲', 'track_no' => 2],
        ], $tracks->toArray());
    }
}
