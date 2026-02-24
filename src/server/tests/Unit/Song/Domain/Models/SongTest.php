<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Song;
use SongType\Domain\Models\SongType;
use Tests\TestCase;

class SongTest extends TestCase
{
    #[Test]
    #[DataProvider('equalsDataProvider')]
    public function equals(Song $object, Song $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsDataProvider(): array
    {
        return [
            [
                Song::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'Song Title',
                    'Song Description',
                    SongType::Original->value,
                    1,
                    self::creators('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'),
                    self::creators('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'),
                    self::creators('cccccccc-cccc-cccc-cccc-cccccccccccc'),
                ),
                Song::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'Song Title',
                    'Song Description',
                    SongType::Original->value,
                    1,
                    self::creators('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'),
                    self::creators('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'),
                    self::creators('cccccccc-cccc-cccc-cccc-cccccccccccc'),
                ),
                true,
            ],
            [
                Song::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'Song Title',
                    'Song Description',
                    SongType::Original->value,
                    1,
                    self::creators('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'),
                    self::creators('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'),
                    self::creators('cccccccc-cccc-cccc-cccc-cccccccccccc'),
                ),
                Song::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'Other Song',
                    'Other Description',
                    SongType::Cover->value,
                    2,
                    self::creators('dddddddd-dddd-dddd-dddd-dddddddddddd'),
                    self::creators('eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee'),
                    self::creators('ffffffff-ffff-ffff-ffff-ffffffffffff'),
                ),
                true,
            ],
            [
                Song::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'Song Title',
                    'Song Description',
                    SongType::Original->value,
                    1,
                    self::creators('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'),
                    self::creators('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'),
                    self::creators('cccccccc-cccc-cccc-cccc-cccccccccccc'),
                ),
                Song::reconstruct(
                    '22222222-2222-2222-2222-222222222222',
                    'Song Title',
                    'Song Description',
                    SongType::Original->value,
                    1,
                    self::creators('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'),
                    self::creators('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'),
                    self::creators('cccccccc-cccc-cccc-cccc-cccccccccccc'),
                ),
                false,
            ],
            [
                Song::reconstruct(
                    '11111111-1111-1111-1111-111111111111',
                    'Song Title',
                    'Song Description',
                    SongType::Original->value,
                    1,
                    self::creators('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'),
                    self::creators('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'),
                    self::creators('cccccccc-cccc-cccc-cccc-cccccccccccc'),
                ),
                Song::reconstruct(
                    '22222222-2222-2222-2222-222222222222',
                    'Other Song',
                    'Other Description',
                    SongType::Cover->value,
                    2,
                    self::creators('dddddddd-dddd-dddd-dddd-dddddddddddd'),
                    self::creators('eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee'),
                    self::creators('ffffffff-ffff-ffff-ffff-ffffffffffff'),
                ),
                false,
            ],
        ];
    }

    /**
     * @return list<array{creatorId: string, orderNo: int}>
     */
    private static function creators(string $creatorId): array
    {
        return [['creatorId' => $creatorId, 'orderNo' => 1]];
    }

    #[Test]
    public function toArray(): void
    {
        $songId = '11111111-1111-1111-1111-111111111111';
        $title = 'Song Title';
        $description = 'Song Description';
        $songType = SongType::Original->value;
        $orderNo = 1;
        $arrangers = self::creators('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa');
        $composers = self::creators('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb');
        $lyricists = self::creators('cccccccc-cccc-cccc-cccc-cccccccccccc');

        $song = Song::reconstruct(
            $songId,
            $title,
            $description,
            $songType,
            $orderNo,
            $arrangers,
            $composers,
            $lyricists,
        );

        $expected = [
            'song_id' => $songId,
            'title' => $title,
            'description' => $description,
            'song_type' => $songType,
            'order_no' => $orderNo,
            'arrangers' => [
                ['creator_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'order_no' => 1],
            ],
            'composers' => [
                ['creator_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'order_no' => 1],
            ],
            'lyricists' => [
                ['creator_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc', 'order_no' => 1],
            ],
        ];

        $this->assertSame($expected, $song->toArray());
    }
}
