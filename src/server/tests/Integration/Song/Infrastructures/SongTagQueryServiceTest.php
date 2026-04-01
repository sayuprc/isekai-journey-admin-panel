<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Infrastructures;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Criteria\SongTagSearchCriteria;
use Song\Domain\Criteria\SongTagSort;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Infrastructures\SongTagQueryService;
use Song\Infrastructures\SongTagRepository;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Domain\ValueObjects\OrderNo;
use Support\Optional\None;
use Support\Optional\Some;
use Tests\Support\DatabaseTestCase;

class SongTagQueryServiceTest extends DatabaseTestCase
{
    #[Test]
    public function search(): void
    {
        $repository = $this->getRepository();
        $repository->save($this->createTag($this->generateUuid(), 'ポップ', 30));
        $repository->save($this->createTag($this->generateUuid(), 'ロック', 10));
        $repository->save($this->createTag($this->generateUuid(), 'バラード', 20));

        $criteria = new SongTagSearchCriteria(new None());

        $result = $this->getInstance()->search($criteria);

        $this->assertCount(3, $result);
        $this->assertSame('ロック', $result[0]->name->value);
        $this->assertSame('バラード', $result[1]->name->value);
        $this->assertSame('ポップ', $result[2]->name->value);
    }

    #[Test]
    public function searchByName(): void
    {
        $repository = $this->getRepository();
        $repository->save($this->createTag($this->generateUuid(), 'ロック', 10));
        $repository->save($this->createTag($this->generateUuid(), 'ロール', 20));
        $repository->save($this->createTag($this->generateUuid(), 'ポップ', 30));

        $criteria = new SongTagSearchCriteria(new Some('ロ'));

        $result = $this->getInstance()->search($criteria);

        $this->assertCount(2, $result);
        $this->assertSame('ロック', $result[0]->name->value);
        $this->assertSame('ロール', $result[1]->name->value);
    }

    #[Test]
    public function searchSortByName(): void
    {
        $repository = $this->getRepository();
        $repository->save($this->createTag($this->generateUuid(), 'ポップ', 10));
        $repository->save($this->createTag($this->generateUuid(), 'アニソン', 20));
        $repository->save($this->createTag($this->generateUuid(), 'ロック', 30));

        $criteria = new SongTagSearchCriteria(new None(), SongTagSort::Name, Order::Asc);

        $result = $this->getInstance()->search($criteria);

        $this->assertCount(3, $result);
        $this->assertSame('アニソン', $result[0]->name->value);
        $this->assertSame('ポップ', $result[1]->name->value);
        $this->assertSame('ロック', $result[2]->name->value);
    }

    #[Test]
    public function searchWithPagination(): void
    {
        $repository = $this->getRepository();
        $repository->save($this->createTag($this->generateUuid(), 'ロック', 10));
        $repository->save($this->createTag($this->generateUuid(), 'ポップ', 20));
        $repository->save($this->createTag($this->generateUuid(), 'バラード', 30));

        // PerPage::TwentyFive で page=2 → 25件目以降は存在しないので空
        $criteria = new SongTagSearchCriteria(new None(), page: 2, perPage: PerPage::TwentyFive);

        $result = $this->getInstance()->search($criteria);

        $this->assertCount(0, $result);
    }

    #[Test]
    public function maxPage(): void
    {
        $repository = $this->getRepository();
        $repository->save($this->createTag($this->generateUuid(), 'ロック', 10));
        $repository->save($this->createTag($this->generateUuid(), 'ポップ', 20));
        $repository->save($this->createTag($this->generateUuid(), 'バラード', 30));

        // 3件 / 25件 = 1ページ
        $criteria = new SongTagSearchCriteria(new None(), perPage: PerPage::TwentyFive);

        $this->assertSame(1, $this->getInstance()->maxPage($criteria));
    }

    #[Test]
    public function maxPageWhenEmpty(): void
    {
        $criteria = new SongTagSearchCriteria(new None());

        $this->assertSame(0, $this->getInstance()->maxPage($criteria));
    }

    private function createTag(string $id, string $name, int $orderNo): SongTag
    {
        return new SongTag(
            SongTagId::reconstruct($id),
            SongTagName::reconstruct($name),
            OrderNo::reconstruct($orderNo),
        );
    }

    private function getRepository(): SongTagRepository
    {
        return $this->app->make(SongTagRepository::class);
    }

    private function getInstance(): SongTagQueryService
    {
        return $this->app->make(SongTagQueryService::class);
    }
}
