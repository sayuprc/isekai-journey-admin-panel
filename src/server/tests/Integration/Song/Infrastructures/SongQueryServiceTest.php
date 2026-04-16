<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Infrastructures;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Criteria\SongSearchCriteria;
use Song\Domain\Criteria\Sort;
use Song\Domain\Models\SongAttribute;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongQueryService;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\None;
use Support\Optional\Some;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SongQueryServiceTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function searchWithoutFilters(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 1, [], [], [], true),
            $this->createSong($uuid2, '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 2, [], [], [], true),
        );

        $results = $this->getInstance()->search($this->criteria());

        $this->assertCount(2, $results);
        $this->assertSame($uuid1, $results[0]->songId);
        $this->assertSame('描き続けた君へ', $results[0]->title);
        $this->assertSame(SongType::Original, $results[0]->type);
        $this->assertNull($results[0]->attribute);
        $this->assertSame(1, $results[0]->orderNo);
        $this->assertSame($uuid2, $results[1]->songId);
    }

    #[Test]
    public function searchWithTitle(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 1, [], [], [], true),
            $this->createSong($uuid2, '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 2, [], [], [], true),
        );

        $results = $this->getInstance()->search($this->criteria(title: new Some('描き続けた君へ')));

        $this->assertCount(1, $results);
        $this->assertSame($uuid1, $results[0]->songId);
        $this->assertSame('描き続けた君へ', $results[0]->title);
    }

    #[Test]
    public function searchWithType(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 1, [], [], [], true),
            $this->createSong($uuid2, '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 2, [], [], [], true),
        );

        $results = $this->getInstance()->search($this->criteria(type: new Some(SongType::Original)));

        $this->assertCount(1, $results);
        $this->assertSame($uuid1, $results[0]->songId);
        $this->assertSame(SongType::Original, $results[0]->type);
    }

    #[Test]
    public function searchWithAttribute(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, '描き続けた君へ', 'コラボ楽曲', SongType::Original, SongAttribute::Collaboration, 1, [], [], [], true),
            $this->createSong($uuid2, '全部夢だった！', 'オリジナル楽曲', SongType::Original, null, 2, [], [], [], true),
        );

        $results = $this->getInstance()->search($this->criteria(attribute: new Some(SongAttribute::Collaboration)));

        $this->assertCount(1, $results);
        $this->assertSame($uuid1, $results[0]->songId);
        $this->assertSame(SongAttribute::Collaboration, $results[0]->attribute);
    }

    #[Test]
    public function searchWithIsDisplay(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid1, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 1, [], [], [], true),
            $this->createSong($uuid2, '全部夢だった！', 'カバー楽曲', SongType::Cover, null, 2, [], [], [], false),
        );

        $results = $this->getInstance()->search($this->criteria(isDisplay: new Some(false)));

        $this->assertCount(1, $results);
        $this->assertSame($uuid2, $results[0]->songId);
        $this->assertFalse($results[0]->isDisplay);
    }

    #[Test]
    public function searchNotFound(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid, '描き続けた君へ', 'オリジナル楽曲', SongType::Original, null, 1, [], [], [], true),
        );

        $results = $this->getInstance()->search($this->criteria(title: new Some('存在しないタイトル')));

        $this->assertCount(0, $results);
    }

    #[Test]
    public function maxPage(): void
    {
        $songs = [];

        for ($i = 1; $i <= 26; $i++) {
            $songs[] = $this->createSong($this->generateUuid(), "楽曲{$i}", '説明', SongType::Original, null, $i, [], [], [], true);
        }

        $this->storeSongs(...$songs);

        $this->assertSame(2, $this->getInstance()->maxPage($this->criteria(perPage: PerPage::TwentyFive)));
    }

    #[Test]
    public function maxPageWhenNotFound(): void
    {
        $criteria = $this->criteria(title: new Some('存在しないタイトル'));

        $this->assertSame(0, $this->getInstance()->maxPage($criteria));
    }

    private function criteria(
        mixed $title = null,
        mixed $type = null,
        mixed $attribute = null,
        mixed $isDisplay = null,
        PerPage $perPage = PerPage::Fifty,
    ): SongSearchCriteria {
        return new SongSearchCriteria(
            $title ?? new None(),
            $type ?? new None(),
            $attribute ?? new None(),
            $isDisplay ?? new None(),
            Sort::OrderNo,
            Order::Asc,
            1,
            $perPage,
        );
    }

    private function getInstance(): SongQueryService
    {
        return $this->app->make(SongQueryService::class);
    }
}
