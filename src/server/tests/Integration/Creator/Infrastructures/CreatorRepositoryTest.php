<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Infrastructures;

use Creator\Domain\Criteria\CreatorSearchCriteria;
use Creator\Domain\Criteria\Sort;
use Creator\Domain\Models\CreatorId;
use Creator\Infrastructures\CreatorRepository;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\None;
use Support\Optional\Some;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class CreatorRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function all(): void
    {
        $repository = $this->getInstance();

        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $repository->save($creator1);
        $repository->save($creator2);

        $creators = $repository->all();

        $this->assertCount(2, $creators);
        $this->assertEquals([$creator1, $creator2], $creators);
    }

    #[Test]
    public function find(): void
    {
        $repository = $this->getInstance();

        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($creator);

        $found = $repository->find($creator->creatorId);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function findNotFound(): void
    {
        $found = $this->getInstance()->find(CreatorId::reconstruct($this->generateUuid()));

        $this->assertNull($found);
    }

    #[Test]
    public function findByName(): void
    {
        $repository = $this->getInstance();

        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($creator);

        $found = $repository->findByName($creator->name);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function findByIds(): void
    {
        $repository = $this->getInstance();

        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $repository->save($creator1);
        $repository->save($creator2);

        $creators = $repository->findByIds($creator1->creatorId, $creator2->creatorId);

        $this->assertCount(2, $creators);
        $this->assertEqualsCanonicalizing([$creator1, $creator2], $creators);
    }

    #[Test]
    public function save(): void
    {
        $repository = $this->getInstance();

        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($creator);

        $found = $repository->find($creator->creatorId);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $repository = $this->getInstance();

        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($creator);

        $repository->delete($creator->creatorId);

        $found = $repository->find($creator->creatorId);

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $repository->save($creator1);
        $repository->save($creator2);

        $this->assertSame(20, $repository->getMaxOrderNo());
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

        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $repository->save($creator1);
        $repository->save($creator2);

        $criteria = new CreatorSearchCriteria(new None());

        $creators = $repository->search($criteria);

        $this->assertCount(2, $creators);
    }

    #[Test]
    public function searchWithName(): void
    {
        $repository = $this->getInstance();

        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $repository->save($creator1);
        $repository->save($creator2);

        $criteria = new CreatorSearchCriteria(new Some('ヰ世界情緒'));

        $creators = $repository->search($criteria);

        $this->assertCount(1, $creators);
        $this->assertEquals($creator1, $creators[0]);
    }

    #[Test]
    public function searchWithNameNotFound(): void
    {
        $repository = $this->getInstance();

        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);

        $repository->save($creator);

        $criteria = new CreatorSearchCriteria(new Some('存在しない名前'));

        $creators = $repository->search($criteria);

        $this->assertCount(0, $creators);
    }

    #[Test]
    public function searchSortByNameAsc(): void
    {
        $repository = $this->getInstance();

        $creator1 = $this->createCreator($this->generateUuid(), 'あ', 10);
        $creator2 = $this->createCreator($this->generateUuid(), 'い', 20);

        $repository->save($creator2);
        $repository->save($creator1);

        $criteria = new CreatorSearchCriteria(new None(), Sort::Name, Order::Asc);

        $creators = $repository->search($criteria);

        $this->assertCount(2, $creators);
        $this->assertEquals($creator1, $creators[0]);
        $this->assertEquals($creator2, $creators[1]);
    }

    #[Test]
    public function searchSortByNameDesc(): void
    {
        $repository = $this->getInstance();

        $creator1 = $this->createCreator($this->generateUuid(), 'あ', 10);
        $creator2 = $this->createCreator($this->generateUuid(), 'い', 20);

        $repository->save($creator1);
        $repository->save($creator2);

        $criteria = new CreatorSearchCriteria(new None(), Sort::Name, Order::Desc);

        $creators = $repository->search($criteria);

        $this->assertCount(2, $creators);
        $this->assertEquals($creator2, $creators[0]);
        $this->assertEquals($creator1, $creators[1]);
    }

    #[Test]
    public function searchWithPagination(): void
    {
        $repository = $this->getInstance();

        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $repository->save($creator1);
        $repository->save($creator2);

        $criteria = new CreatorSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $page1 = $repository->search($criteria);

        $this->assertCount(2, $page1);

        $criteria2 = new CreatorSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 2, PerPage::TwentyFive);

        $page2 = $repository->search($criteria2);

        $this->assertCount(0, $page2);
    }

    #[Test]
    public function maxPageWithoutFilter(): void
    {
        $repository = $this->getInstance();

        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $repository->save($creator1);
        $repository->save($creator2);

        $criteria = new CreatorSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $this->assertSame(1, $repository->maxPage($criteria));
    }

    #[Test]
    public function maxPageWithNameFilter(): void
    {
        $repository = $this->getInstance();

        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $repository->save($creator1);
        $repository->save($creator2);

        $criteria = new CreatorSearchCriteria(new Some('ヰ世界情緒'));

        $this->assertSame(1, $repository->maxPage($criteria));
    }

    #[Test]
    public function maxPageWhenEmpty(): void
    {
        $criteria = new CreatorSearchCriteria(new None());

        $this->assertSame(0, $this->getInstance()->maxPage($criteria));
    }

    private function getInstance(): CreatorRepository
    {
        return $this->app->make(CreatorRepository::class);
    }
}
