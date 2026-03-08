<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\DebugInfrastructures;

use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Criteria\CreatorSearchCriteria;
use Creator\Domain\Criteria\Sort;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\None;
use Support\Optional\Some;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FileCreatorRepositoryTest extends TestCase
{
    use EntityFactory;
    use EntityStore;
    use FileRepositoryTransaction;

    #[Test]
    public function all(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $this->storeCreators($creator1, $creator2);

        $creators = $this->getInstance()->all();

        $this->assertCount(2, $creators);
        $this->assertEquals([$creator1, $creator2], $creators);
    }

    #[Test]
    public function find(): void
    {
        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->storeCreators($creator);

        $found = $this->getInstance()->find($creator->creatorId);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function findByName(): void
    {
        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->storeCreators($creator);

        $found = $this->getInstance()->findByName($creator->name);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function findByIds(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $this->storeCreators($creator1, $creator2);

        $creators = $this->getInstance()->findByIds($creator1->creatorId, $creator2->creatorId);

        $this->assertCount(2, $creators);
        $this->assertEquals([$creator1, $creator2], $creators);
    }

    #[Test]
    public function save(): void
    {
        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->getInstance()->save($creator);

        $found = $this->getInstance()->find($creator->creatorId);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->storeCreators($creator);

        $this->getInstance()->delete($creator->creatorId);

        $found = $this->getInstance()->find($creator->creatorId);

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $this->storeCreators($creator1, $creator2);

        $this->assertSame(20, $this->getInstance()->getMaxOrderNo());
    }

    #[Test]
    public function getMaxOrderNoWhenEmpty(): void
    {
        $this->assertSame(0, $this->getInstance()->getMaxOrderNo());
    }

    #[Test]
    public function searchWithoutName(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $this->storeCreators($creator1, $creator2);

        $criteria = new CreatorSearchCriteria(new None());

        $creators = $this->getInstance()->search($criteria);

        $this->assertCount(2, $creators);
    }

    #[Test]
    public function searchWithName(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $this->storeCreators($creator1, $creator2);

        $criteria = new CreatorSearchCriteria(new Some('ヰ世界情緒'));

        $creators = $this->getInstance()->search($criteria);

        $this->assertCount(1, $creators);
        $this->assertEquals($creator1, $creators[0]);
    }

    #[Test]
    public function searchWithNameNotFound(): void
    {
        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);

        $this->storeCreators($creator);

        $criteria = new CreatorSearchCriteria(new Some('存在しない名前'));

        $creators = $this->getInstance()->search($criteria);

        $this->assertCount(0, $creators);
    }

    #[Test]
    public function searchSortByNameAsc(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'あ', 10);
        $creator2 = $this->createCreator($this->generateUuid(), 'い', 20);

        $this->storeCreators($creator2, $creator1);

        $criteria = new CreatorSearchCriteria(new None(), Sort::Name, Order::Asc);

        $creators = $this->getInstance()->search($criteria);

        $this->assertCount(2, $creators);
        $this->assertEquals($creator1, $creators[0]);
        $this->assertEquals($creator2, $creators[1]);
    }

    #[Test]
    public function searchSortByNameDesc(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'あ', 10);
        $creator2 = $this->createCreator($this->generateUuid(), 'い', 20);

        $this->storeCreators($creator1, $creator2);

        $criteria = new CreatorSearchCriteria(new None(), Sort::Name, Order::Desc);

        $creators = $this->getInstance()->search($criteria);

        $this->assertCount(2, $creators);
        $this->assertEquals($creator2, $creators[0]);
        $this->assertEquals($creator1, $creators[1]);
    }

    #[Test]
    public function searchWithPagination(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $this->storeCreators($creator1, $creator2);

        $criteria = new CreatorSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $page1 = $this->getInstance()->search($criteria);

        $this->assertCount(2, $page1);

        $criteria2 = new CreatorSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 2, PerPage::TwentyFive);

        $page2 = $this->getInstance()->search($criteria2);

        $this->assertCount(0, $page2);
    }

    #[Test]
    public function maxPageWithoutFilter(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $this->storeCreators($creator1, $creator2);

        $criteria = new CreatorSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $this->assertSame(1, $this->getInstance()->maxPage($criteria));
    }

    #[Test]
    public function maxPageWithNameFilter(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $this->storeCreators($creator1, $creator2);

        $criteria = new CreatorSearchCriteria(new Some('ヰ世界情緒'));

        $this->assertSame(1, $this->getInstance()->maxPage($criteria));
    }

    #[Test]
    public function maxPageWhenEmpty(): void
    {
        $criteria = new CreatorSearchCriteria(new None());

        $this->assertSame(0, $this->getInstance()->maxPage($criteria));
    }

    private function getInstance(): FileCreatorRepository
    {
        return $this->app->make(FileCreatorRepository::class);
    }
}
