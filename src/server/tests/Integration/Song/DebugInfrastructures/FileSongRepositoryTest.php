<?php

declare(strict_types=1);

namespace Tests\Integration\Song\DebugInfrastructures;

use Creator\Domain\Models\CreatorId;
use PHPUnit\Framework\Attributes\Test;
use Song\DebugInfrastructures\FileSongRepository;
use Song\Domain\Criteria\SongSearchCriteria;
use Song\Domain\Criteria\Sort;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongAttribute;
use Song\Domain\Models\SongType;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\None;
use Support\Optional\Some;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FileSongRepositoryTest extends TestCase
{
    use EntityFactory;
    use EntityStore;
    use FileRepositoryTransaction;

    #[Test]
    public function all(): void
    {
        $this->storeSongs(
            $song1 = $this->createSong(
                $this->generateUuid(),
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original,
                null,
                1,
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            ),
            $song2 = $this->createSong(
                $this->generateUuid(),
                '全部夢だった！',
                'カバー楽曲',
                SongType::Cover,
                null,
                2,
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            ),
        );

        $songs = $this->getInstance()->all();

        $this->assertCount(2, $songs);
        $this->assertEquals([$song1, $song2], $songs);
    }

    #[Test]
    public function find(): void
    {
        $this->storeSongs(
            $song = $this->createSong(
                $this->generateUuid(),
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original,
                null,
                1,
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            ),
        );

        $found = $this->getInstance()->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($song, $found);
    }

    #[Test]
    public function save(): void
    {
        $song = $this->createSong(
            $this->generateUuid(),
            '描き続けた君へ',
            'オリジナル楽曲',
            SongType::Original,
            null,
            1,
            [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
        );

        $this->getInstance()->save($song);

        $found = $this->getAll(Song::class, FileSongRepository::class);

        $this->assertCount(1, $found);
        $this->assertEquals($song, array_first($found));
    }

    #[Test]
    public function deleting(): void
    {
        $this->storeSongs(
            $song = $this->createSong(
                $this->generateUuid(),
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original,
                null,
                1,
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            ),
        );

        $this->getInstance()->delete($song->songId);

        $found = $this->getInstance()->find($song->songId);

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        // 空の場合は 0
        $this->assertSame(0, $repository->getMaxOrderNo());

        // データを追加
        $this->storeSongs(
            $this->createSong($this->generateUuid(), '曲1', '説明', SongType::Original, null, 10, [], [], []),
            $this->createSong($this->generateUuid(), '曲2', '説明', SongType::Original, null, 30, [], [], []),
            $this->createSong($this->generateUuid(), '曲3', '説明', SongType::Original, null, 20, [], [], []),
        );

        // 最大値が返ることを確認
        $this->assertSame(30, $repository->getMaxOrderNo());
    }

    #[Test]
    public function isCreatorUsedInArranger(): void
    {
        $creatorId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $this->generateUuid(),
                '曲1',
                '説明',
                SongType::Original,
                null,
                1,
                [],
                [],
                [['creatorId' => $creatorId, 'orderNo' => 1]],
            ),
        );

        $this->assertTrue($this->getInstance()->isCreatorUsed(CreatorId::reconstruct($creatorId)));
    }

    #[Test]
    public function isCreatorUsedInComposer(): void
    {
        $creatorId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $this->generateUuid(),
                '曲2',
                '説明',
                SongType::Original,
                null,
                1,
                [],
                [['creatorId' => $creatorId, 'orderNo' => 1]],
                [],
            ),
        );

        $this->assertTrue($this->getInstance()->isCreatorUsed(CreatorId::reconstruct($creatorId)));
    }

    #[Test]
    public function isCreatorUsedInLyricist(): void
    {
        $creatorId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $this->generateUuid(),
                '曲3',
                '説明',
                SongType::Original,
                null,
                1,
                [['creatorId' => $creatorId, 'orderNo' => 1]],
                [],
                [],
            ),
        );

        $this->assertTrue($this->getInstance()->isCreatorUsed(CreatorId::reconstruct($creatorId)));
    }

    #[Test]
    public function isCreatorUsedNotFound(): void
    {
        $creatorId = $this->generateUuid();

        $this->assertFalse($this->getInstance()->isCreatorUsed(CreatorId::reconstruct($creatorId)));
    }

    #[Test]
    public function searchWithoutFilters(): void
    {
        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $this->storeSongs($song1, $song2);

        $criteria = new SongSearchCriteria(new None(), new None(), new None());

        $songs = $this->getInstance()->search($criteria);

        $this->assertCount(2, $songs);
    }

    #[Test]
    public function searchWithTitle(): void
    {
        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $this->storeSongs($song1, $song2);

        $criteria = new SongSearchCriteria(new Some('描き続けた君へ'), new None(), new None());

        $songs = $this->getInstance()->search($criteria);

        $this->assertCount(1, $songs);
        $this->assertEquals($song1, $songs[0]);
    }

    #[Test]
    public function searchWithType(): void
    {
        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $this->storeSongs($song1, $song2);

        $criteria = new SongSearchCriteria(new None(), new Some(SongType::Original), new None());

        $songs = $this->getInstance()->search($criteria);

        $this->assertCount(1, $songs);
        $this->assertEquals($song1, $songs[0]);
    }

    #[Test]
    public function searchWithAttribute(): void
    {
        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'コラボ楽曲', SongType::Original, SongAttribute::Collaboration, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'オリジナル楽曲', SongType::Original, null, 20, [], [], []);

        $this->storeSongs($song1, $song2);

        $criteria = new SongSearchCriteria(new None(), new None(), new Some(SongAttribute::Collaboration));

        $songs = $this->getInstance()->search($criteria);

        $this->assertCount(1, $songs);
        $this->assertEquals($song1, $songs[0]);
    }

    #[Test]
    public function searchWithTitleNotFound(): void
    {
        $song = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);

        $this->storeSongs($song);

        $criteria = new SongSearchCriteria(new Some('存在しないタイトル'), new None(), new None());

        $songs = $this->getInstance()->search($criteria);

        $this->assertCount(0, $songs);
    }

    #[Test]
    public function searchSortByTitleAsc(): void
    {
        $song1 = $this->createSong($this->generateUuid(), 'あ', 'オリジナル楽曲', SongType::Original, null, 20, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), 'い', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);

        $this->storeSongs($song2, $song1);

        $criteria = new SongSearchCriteria(new None(), new None(), new None(), Sort::Title, Order::Asc);

        $songs = $this->getInstance()->search($criteria);

        $this->assertCount(2, $songs);
        $this->assertEquals($song1, $songs[0]);
        $this->assertEquals($song2, $songs[1]);
    }

    #[Test]
    public function searchSortByTitleDesc(): void
    {
        $song1 = $this->createSong($this->generateUuid(), 'あ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), 'い', 'オリジナル楽曲', SongType::Original, null, 20, [], [], []);

        $this->storeSongs($song1, $song2);

        $criteria = new SongSearchCriteria(new None(), new None(), new None(), Sort::Title, Order::Desc);

        $songs = $this->getInstance()->search($criteria);

        $this->assertCount(2, $songs);
        $this->assertEquals($song2, $songs[0]);
        $this->assertEquals($song1, $songs[1]);
    }

    #[Test]
    public function searchSortByOrderNoAsc(): void
    {
        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $this->storeSongs($song2, $song1);

        $criteria = new SongSearchCriteria(new None(), new None(), new None(), Sort::OrderNo, Order::Asc);

        $songs = $this->getInstance()->search($criteria);

        $this->assertCount(2, $songs);
        $this->assertEquals($song1, $songs[0]);
        $this->assertEquals($song2, $songs[1]);
    }

    #[Test]
    public function searchSortByOrderNoDesc(): void
    {
        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $this->storeSongs($song1, $song2);

        $criteria = new SongSearchCriteria(new None(), new None(), new None(), Sort::OrderNo, Order::Desc);

        $songs = $this->getInstance()->search($criteria);

        $this->assertCount(2, $songs);
        $this->assertEquals($song2, $songs[0]);
        $this->assertEquals($song1, $songs[1]);
    }

    #[Test]
    public function searchWithPagination(): void
    {
        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $this->storeSongs($song1, $song2);

        $criteria = new SongSearchCriteria(new None(), new None(), new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $page1 = $this->getInstance()->search($criteria);

        $this->assertCount(2, $page1);

        $criteria2 = new SongSearchCriteria(new None(), new None(), new None(), Sort::OrderNo, Order::Asc, 2, PerPage::TwentyFive);

        $page2 = $this->getInstance()->search($criteria2);

        $this->assertCount(0, $page2);
    }

    #[Test]
    public function maxPageWithoutFilter(): void
    {
        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $this->storeSongs($song1, $song2);

        $criteria = new SongSearchCriteria(new None(), new None(), new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $this->assertSame(1, $this->getInstance()->maxPage($criteria));
    }

    #[Test]
    public function maxPageWithTitleFilter(): void
    {
        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $this->storeSongs($song1, $song2);

        $criteria = new SongSearchCriteria(new Some('描き続けた君へ'), new None(), new None());

        $this->assertSame(1, $this->getInstance()->maxPage($criteria));
    }

    #[Test]
    public function maxPageWhenEmpty(): void
    {
        $criteria = new SongSearchCriteria(new None(), new None(), new None());

        $this->assertSame(0, $this->getInstance()->maxPage($criteria));
    }

    private function getInstance(): FileSongRepository
    {
        return $this->app->make(FileSongRepository::class);
    }
}
