<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Infrastructures;

use Creator\Domain\Models\CreatorId;
use Creator\Infrastructures\CreatorRepository;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Criteria\SongSearchCriteria;
use Song\Domain\Criteria\Sort;
use Song\Domain\Models\SongAttribute;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\None;
use Support\Optional\Some;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class SongRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function all(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), '曲1', '説明1', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '曲2', '説明2', SongType::Cover, null, 20, [], [], []);

        $repository->save($song1);
        $repository->save($song2);

        $songs = $repository->all();

        $this->assertCount(2, $songs);
        $this->assertEquals($song1, $songs[0]);
        $this->assertEquals($song2, $songs[1]);
    }

    #[Test]
    public function find(): void
    {
        $repository = $this->getInstance();

        $song = $this->createSong($this->generateUuid(), '曲名', '説明', SongType::Original, null, 1, [], [], []);

        $repository->save($song);

        $found = $repository->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($song, $found);
    }

    #[Test]
    public function findNotFound(): void
    {
        $found = $this->getInstance()->find(SongId::reconstruct($this->generateUuid()));

        $this->assertNull($found);
    }

    #[Test]
    public function findWithCreators(): void
    {
        $creatorRepository = $this->app->make(CreatorRepository::class);
        $creator = $this->createCreator($this->generateUuid(), 'クリエイター', 1);
        $creatorRepository->save($creator);

        $repository = $this->getInstance();

        $song = $this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            SongAttribute::Collaboration,
            1,
            [['creatorId' => $creator->creatorId->value, 'orderNo' => 1]],
            [['creatorId' => $creator->creatorId->value, 'orderNo' => 1]],
            [['creatorId' => $creator->creatorId->value, 'orderNo' => 1]],
        );

        $repository->save($song);

        $found = $repository->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($song, $found);
    }

    #[Test]
    public function isCreatorUsed(): void
    {
        $creatorRepository = $this->app->make(CreatorRepository::class);
        $creator = $this->createCreator($this->generateUuid(), 'クリエイター', 1);
        $creatorRepository->save($creator);

        $repository = $this->getInstance();

        $song = $this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            null,
            1,
            [['creatorId' => $creator->creatorId->value, 'orderNo' => 1]],
            [],
            [],
        );

        $repository->save($song);

        $this->assertTrue($repository->isCreatorUsed($creator->creatorId));
    }

    #[Test]
    public function isCreatorNotUsed(): void
    {
        $this->assertFalse($this->getInstance()->isCreatorUsed(CreatorId::reconstruct($this->generateUuid())));
    }

    #[Test]
    public function save(): void
    {
        $repository = $this->getInstance();

        $song = $this->createSong($this->generateUuid(), '曲名', '説明', SongType::Original, null, 1, [], [], []);

        $repository->save($song);

        $found = $repository->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($song, $found);
    }

    #[Test]
    public function saveUpdatesExisting(): void
    {
        $repository = $this->getInstance();

        $song = $this->createSong($this->generateUuid(), '旧タイトル', '旧説明', SongType::Original, null, 1, [], [], []);
        $repository->save($song);

        $updated = $this->createSong($song->songId->value, '新タイトル', '新説明', SongType::Cover, null, 2, [], [], []);
        $repository->save($updated);

        $found = $repository->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($updated, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $repository = $this->getInstance();

        $song = $this->createSong($this->generateUuid(), '曲名', '説明', SongType::Original, null, 1, [], [], []);

        $repository->save($song);
        $repository->delete($song->songId);

        $found = $repository->find($song->songId);

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), '曲1', '説明', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '曲2', '説明', SongType::Original, null, 30, [], [], []);
        $song3 = $this->createSong($this->generateUuid(), '曲3', '説明', SongType::Original, null, 20, [], [], []);

        $repository->save($song1);
        $repository->save($song2);
        $repository->save($song3);

        $this->assertSame(30, $repository->getMaxOrderNo());
    }

    #[Test]
    public function getMaxOrderNoWhenEmpty(): void
    {
        $this->assertSame(0, $this->getInstance()->getMaxOrderNo());
    }

    #[Test]
    public function searchWithoutFilters(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $repository->save($song1);
        $repository->save($song2);

        $criteria = new SongSearchCriteria(new None(), new None(), new None());

        $songs = $repository->search($criteria);

        $this->assertCount(2, $songs);
    }

    #[Test]
    public function searchWithTitle(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $repository->save($song1);
        $repository->save($song2);

        $criteria = new SongSearchCriteria(new Some('描き続けた君へ'), new None(), new None());

        $songs = $repository->search($criteria);

        $this->assertCount(1, $songs);
        $this->assertEquals($song1, $songs[0]);
    }

    #[Test]
    public function searchWithType(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $repository->save($song1);
        $repository->save($song2);

        $criteria = new SongSearchCriteria(new None(), new Some(SongType::Original), new None());

        $songs = $repository->search($criteria);

        $this->assertCount(1, $songs);
        $this->assertEquals($song1, $songs[0]);
    }

    #[Test]
    public function searchWithAttribute(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'コラボ楽曲', SongType::Original, SongAttribute::Collaboration, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'オリジナル楽曲', SongType::Original, null, 20, [], [], []);

        $repository->save($song1);
        $repository->save($song2);

        $criteria = new SongSearchCriteria(new None(), new None(), new Some(SongAttribute::Collaboration));

        $songs = $repository->search($criteria);

        $this->assertCount(1, $songs);
        $this->assertEquals($song1, $songs[0]);
    }

    #[Test]
    public function searchWithTitleNotFound(): void
    {
        $repository = $this->getInstance();

        $song = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);

        $repository->save($song);

        $criteria = new SongSearchCriteria(new Some('存在しないタイトル'), new None(), new None());

        $songs = $repository->search($criteria);

        $this->assertCount(0, $songs);
    }

    #[Test]
    public function searchSortByTitleAsc(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), 'あ', 'オリジナル楽曲', SongType::Original, null, 20, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), 'い', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);

        $repository->save($song2);
        $repository->save($song1);

        $criteria = new SongSearchCriteria(new None(), new None(), new None(), Sort::Title, Order::Asc);

        $songs = $repository->search($criteria);

        $this->assertCount(2, $songs);
        $this->assertEquals($song1, $songs[0]);
        $this->assertEquals($song2, $songs[1]);
    }

    #[Test]
    public function searchSortByTitleDesc(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), 'あ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), 'い', 'オリジナル楽曲', SongType::Original, null, 20, [], [], []);

        $repository->save($song1);
        $repository->save($song2);

        $criteria = new SongSearchCriteria(new None(), new None(), new None(), Sort::Title, Order::Desc);

        $songs = $repository->search($criteria);

        $this->assertCount(2, $songs);
        $this->assertEquals($song2, $songs[0]);
        $this->assertEquals($song1, $songs[1]);
    }

    #[Test]
    public function searchSortByOrderNoAsc(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $repository->save($song2);
        $repository->save($song1);

        $criteria = new SongSearchCriteria(new None(), new None(), new None(), Sort::OrderNo, Order::Asc);

        $songs = $repository->search($criteria);

        $this->assertCount(2, $songs);
        $this->assertEquals($song1, $songs[0]);
        $this->assertEquals($song2, $songs[1]);
    }

    #[Test]
    public function searchSortByOrderNoDesc(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $repository->save($song1);
        $repository->save($song2);

        $criteria = new SongSearchCriteria(new None(), new None(), new None(), Sort::OrderNo, Order::Desc);

        $songs = $repository->search($criteria);

        $this->assertCount(2, $songs);
        $this->assertEquals($song2, $songs[0]);
        $this->assertEquals($song1, $songs[1]);
    }

    #[Test]
    public function searchWithPagination(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $repository->save($song1);
        $repository->save($song2);

        $criteria = new SongSearchCriteria(new None(), new None(), new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $page1 = $repository->search($criteria);

        $this->assertCount(2, $page1);

        $criteria2 = new SongSearchCriteria(new None(), new None(), new None(), Sort::OrderNo, Order::Asc, 2, PerPage::TwentyFive);

        $page2 = $repository->search($criteria2);

        $this->assertCount(0, $page2);
    }

    #[Test]
    public function maxPageWithoutFilter(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $repository->save($song1);
        $repository->save($song2);

        $criteria = new SongSearchCriteria(new None(), new None(), new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $this->assertSame(1, $repository->maxPage($criteria));
    }

    #[Test]
    public function maxPageWithTitleFilter(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 10, [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 20, [], [], []);

        $repository->save($song1);
        $repository->save($song2);

        $criteria = new SongSearchCriteria(new Some('描き続けた君へ'), new None(), new None());

        $this->assertSame(1, $repository->maxPage($criteria));
    }

    #[Test]
    public function maxPageWhenEmpty(): void
    {
        $criteria = new SongSearchCriteria(new None(), new None(), new None());

        $this->assertSame(0, $this->getInstance()->maxPage($criteria));
    }

    private function getInstance(): SongRepository
    {
        return $this->app->make(SongRepository::class);
    }
}
