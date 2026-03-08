<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Infrastructures;

use Performer\Domain\Criteria\PerformerSearchCriteria;
use Performer\Domain\Criteria\Sort;
use Performer\Domain\Models\PerformerId;
use Performer\Infrastructures\PerformerRepository;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\None;
use Support\Optional\Some;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class PerformerRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function all(): void
    {
        $repository = $this->getInstance();

        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 2);

        $repository->save($performer1);
        $repository->save($performer2);

        $performers = $repository->all();

        $this->assertCount(2, $performers);
        $this->assertEquals([$performer1, $performer2], $performers);
    }

    #[Test]
    public function find(): void
    {
        $repository = $this->getInstance();

        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($performer);

        $found = $repository->find($performer->performerId);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function findNotFound(): void
    {
        $found = $this->getInstance()->find(PerformerId::reconstruct($this->generateUuid()));

        $this->assertNull($found);
    }

    #[Test]
    public function findByName(): void
    {
        $repository = $this->getInstance();

        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($performer);

        $found = $repository->findByName($performer->name);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function save(): void
    {
        $repository = $this->getInstance();

        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($performer);

        $found = $repository->find($performer->performerId);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $repository = $this->getInstance();

        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($performer);

        $repository->delete($performer->performerId);

        $found = $repository->find($performer->performerId);

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        $performer1 = $this->createPerformer($this->generateUuid(), 'performer1', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), 'performer2', 30);
        $performer3 = $this->createPerformer($this->generateUuid(), 'performer3', 20);

        $repository->save($performer1);
        $repository->save($performer2);
        $repository->save($performer3);

        $this->assertSame(30, $repository->getMaxOrderNo());
    }

    #[Test]
    public function getMaxOrderNoWhenEmpty(): void
    {
        $this->assertSame(0, $this->getInstance()->getMaxOrderNo());
    }

    #[Test]
    public function searchWithoutName(): void
    {
        $repository = $this->getInstance();

        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 20);

        $repository->save($performer1);
        $repository->save($performer2);

        $criteria = new PerformerSearchCriteria(new None());

        $performers = $repository->search($criteria);

        $this->assertCount(2, $performers);
    }

    #[Test]
    public function searchWithName(): void
    {
        $repository = $this->getInstance();

        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 20);

        $repository->save($performer1);
        $repository->save($performer2);

        $criteria = new PerformerSearchCriteria(new Some('ヰ世界情緒'));

        $performers = $repository->search($criteria);

        $this->assertCount(1, $performers);
        $this->assertEquals($performer1, $performers[0]);
    }

    #[Test]
    public function searchWithNameNotFound(): void
    {
        $repository = $this->getInstance();

        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 10);

        $repository->save($performer);

        $criteria = new PerformerSearchCriteria(new Some('存在しない名前'));

        $performers = $repository->search($criteria);

        $this->assertCount(0, $performers);
    }

    #[Test]
    public function searchSortByNameAsc(): void
    {
        $repository = $this->getInstance();

        $performer1 = $this->createPerformer($this->generateUuid(), 'あ', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), 'い', 20);

        $repository->save($performer2);
        $repository->save($performer1);

        $criteria = new PerformerSearchCriteria(new None(), Sort::Name, Order::Asc);

        $performers = $repository->search($criteria);

        $this->assertCount(2, $performers);
        $this->assertEquals($performer1, $performers[0]);
        $this->assertEquals($performer2, $performers[1]);
    }

    #[Test]
    public function searchSortByNameDesc(): void
    {
        $repository = $this->getInstance();

        $performer1 = $this->createPerformer($this->generateUuid(), 'あ', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), 'い', 20);

        $repository->save($performer1);
        $repository->save($performer2);

        $criteria = new PerformerSearchCriteria(new None(), Sort::Name, Order::Desc);

        $performers = $repository->search($criteria);

        $this->assertCount(2, $performers);
        $this->assertEquals($performer2, $performers[0]);
        $this->assertEquals($performer1, $performers[1]);
    }

    #[Test]
    public function searchWithPagination(): void
    {
        $repository = $this->getInstance();

        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 20);

        $repository->save($performer1);
        $repository->save($performer2);

        $criteria = new PerformerSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $page1 = $repository->search($criteria);

        $this->assertCount(2, $page1);

        $criteria2 = new PerformerSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 2, PerPage::TwentyFive);

        $page2 = $repository->search($criteria2);

        $this->assertCount(0, $page2);
    }

    #[Test]
    public function maxPageWithoutFilter(): void
    {
        $repository = $this->getInstance();

        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 20);

        $repository->save($performer1);
        $repository->save($performer2);

        $criteria = new PerformerSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $this->assertSame(1, $repository->maxPage($criteria));
    }

    #[Test]
    public function maxPageWithNameFilter(): void
    {
        $repository = $this->getInstance();

        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 20);

        $repository->save($performer1);
        $repository->save($performer2);

        $criteria = new PerformerSearchCriteria(new Some('ヰ世界情緒'));

        $this->assertSame(1, $repository->maxPage($criteria));
    }

    #[Test]
    public function maxPageWhenEmpty(): void
    {
        $criteria = new PerformerSearchCriteria(new None());

        $this->assertSame(0, $this->getInstance()->maxPage($criteria));
    }

    private function getInstance(): PerformerRepository
    {
        return $this->app->make(PerformerRepository::class);
    }
}
