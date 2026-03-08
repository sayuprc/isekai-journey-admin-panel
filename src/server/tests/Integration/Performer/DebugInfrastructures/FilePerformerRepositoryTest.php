<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\DebugInfrastructures;

use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Domain\Criteria\PerformerSearchCriteria;
use Performer\Domain\Criteria\Sort;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\None;
use Support\Optional\Some;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FilePerformerRepositoryTest extends TestCase
{
    use EntityFactory;
    use EntityStore;
    use FileRepositoryTransaction;

    #[Test]
    public function all(): void
    {
        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 2);

        $this->storePerformers($performer1, $performer2);

        $performers = $this->getInstance()->all();

        $this->assertCount(2, $performers);
        $this->assertEquals([$performer1, $performer2], $performers);
    }

    #[Test]
    public function find(): void
    {
        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->storePerformers($performer);

        $found = $this->getInstance()->find($performer->performerId);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function findByName(): void
    {
        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->storePerformers($performer);

        $found = $this->getInstance()->findByName($performer->name);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function save(): void
    {
        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->getInstance()->save($performer);

        $found = $this->getInstance()->find($performer->performerId);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->storePerformers($performer);

        $this->getInstance()->delete($performer->performerId);

        $found = $this->getInstance()->find($performer->performerId);

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        // 空の場合は 0
        $this->assertSame(0, $repository->getMaxOrderNo());

        // データを追加
        $performer1 = $this->createPerformer($this->generateUuid(), 'performer1', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), 'performer2', 30);
        $performer3 = $this->createPerformer($this->generateUuid(), 'performer3', 20);

        $this->storePerformers($performer1, $performer2, $performer3);

        // 最大値が返ることを確認
        $this->assertSame(30, $repository->getMaxOrderNo());
    }

    #[Test]
    public function searchWithoutName(): void
    {
        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 20);

        $this->storePerformers($performer1, $performer2);

        $criteria = new PerformerSearchCriteria(new None());

        $performers = $this->getInstance()->search($criteria);

        $this->assertCount(2, $performers);
    }

    #[Test]
    public function searchWithName(): void
    {
        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 20);

        $this->storePerformers($performer1, $performer2);

        $criteria = new PerformerSearchCriteria(new Some('ヰ世界情緒'));

        $performers = $this->getInstance()->search($criteria);

        $this->assertCount(1, $performers);
        $this->assertEquals($performer1, $performers[0]);
    }

    #[Test]
    public function searchWithNameNotFound(): void
    {
        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 10);

        $this->storePerformers($performer);

        $criteria = new PerformerSearchCriteria(new Some('存在しない名前'));

        $performers = $this->getInstance()->search($criteria);

        $this->assertCount(0, $performers);
    }

    #[Test]
    public function searchSortByNameAsc(): void
    {
        $performer1 = $this->createPerformer($this->generateUuid(), 'あ', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), 'い', 20);

        $this->storePerformers($performer2, $performer1);

        $criteria = new PerformerSearchCriteria(new None(), Sort::Name, Order::Asc);

        $performers = $this->getInstance()->search($criteria);

        $this->assertCount(2, $performers);
        $this->assertEquals($performer1, $performers[0]);
        $this->assertEquals($performer2, $performers[1]);
    }

    #[Test]
    public function searchSortByNameDesc(): void
    {
        $performer1 = $this->createPerformer($this->generateUuid(), 'あ', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), 'い', 20);

        $this->storePerformers($performer1, $performer2);

        $criteria = new PerformerSearchCriteria(new None(), Sort::Name, Order::Desc);

        $performers = $this->getInstance()->search($criteria);

        $this->assertCount(2, $performers);
        $this->assertEquals($performer2, $performers[0]);
        $this->assertEquals($performer1, $performers[1]);
    }

    #[Test]
    public function searchWithPagination(): void
    {
        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 20);

        $this->storePerformers($performer1, $performer2);

        $criteria = new PerformerSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $page1 = $this->getInstance()->search($criteria);

        $this->assertCount(2, $page1);

        $criteria2 = new PerformerSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 2, PerPage::TwentyFive);

        $page2 = $this->getInstance()->search($criteria2);

        $this->assertCount(0, $page2);
    }

    #[Test]
    public function maxPageWithoutFilter(): void
    {
        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 20);

        $this->storePerformers($performer1, $performer2);

        $criteria = new PerformerSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $this->assertSame(1, $this->getInstance()->maxPage($criteria));
    }

    #[Test]
    public function maxPageWithNameFilter(): void
    {
        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 20);

        $this->storePerformers($performer1, $performer2);

        $criteria = new PerformerSearchCriteria(new Some('ヰ世界情緒'));

        $this->assertSame(1, $this->getInstance()->maxPage($criteria));
    }

    #[Test]
    public function maxPageWhenEmpty(): void
    {
        $criteria = new PerformerSearchCriteria(new None());

        $this->assertSame(0, $this->getInstance()->maxPage($criteria));
    }

    private function getInstance(): FilePerformerRepository
    {
        return $this->app->make(FilePerformerRepository::class);
    }
}
