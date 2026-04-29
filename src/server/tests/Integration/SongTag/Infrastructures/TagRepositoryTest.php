<?php

declare(strict_types=1);

namespace Tests\Integration\SongTag\Infrastructures;

use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\TagRepositoryInterface;
use SongTag\Domain\Criteria\Sort;
use SongTag\Domain\Criteria\TagSearchCriteria;
use SongTag\Infrastructures\TagRepository;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\None;
use Support\Optional\Some;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class TagRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function all(): void
    {
        $repository = $this->getInstance();

        $tag1 = $this->createSongTag($this->generateUuid(), 'ライブ定番', 10);
        $tag2 = $this->createSongTag($this->generateUuid(), '周年', 20);

        $repository->save($tag1);
        $repository->save($tag2);

        $tags = $repository->all();

        $this->assertCount(2, $tags);
        $this->assertEquals([$tag1, $tag2], $tags);
    }

    #[Test]
    public function findByName(): void
    {
        $repository = $this->getInstance();
        $tag = $this->createSongTag($this->generateUuid(), 'ライブ定番', 10);

        $repository->save($tag);

        $found = $repository->findByName($tag->name);

        $this->assertNotNull($found);
        $this->assertEquals($tag, $found);
    }

    #[Test]
    public function searchWithName(): void
    {
        $repository = $this->getInstance();

        $tag1 = $this->createSongTag($this->generateUuid(), 'ライブ定番', 10);
        $tag2 = $this->createSongTag($this->generateUuid(), '周年', 20);

        $repository->save($tag1);
        $repository->save($tag2);

        $criteria = new TagSearchCriteria(new Some('ライブ'));
        $tags = $repository->search($criteria);

        $this->assertCount(1, $tags);
        $this->assertEquals($tag1, $tags[0]);
    }

    #[Test]
    public function searchSortByNameDesc(): void
    {
        $repository = $this->getInstance();

        $tag1 = $this->createSongTag($this->generateUuid(), 'あ', 10);
        $tag2 = $this->createSongTag($this->generateUuid(), 'い', 20);

        $repository->save($tag1);
        $repository->save($tag2);

        $criteria = new TagSearchCriteria(new None(), Sort::Name, Order::Desc);
        $tags = $repository->search($criteria);

        $this->assertCount(2, $tags);
        $this->assertEquals($tag2, $tags[0]);
        $this->assertEquals($tag1, $tags[1]);
    }

    #[Test]
    public function maxPage(): void
    {
        $repository = $this->getInstance();

        for ($i = 1; $i <= 26; $i++) {
            $repository->save($this->createSongTag($this->generateUuid(), "タグ{$i}", $i));
        }

        $criteria = new TagSearchCriteria(new None(), Sort::OrderNo, Order::Asc, 1, PerPage::TwentyFive);

        $this->assertSame(2, $repository->maxPage($criteria));
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        $repository->save($this->createSongTag($this->generateUuid(), 'ライブ定番', 10));
        $repository->save($this->createSongTag($this->generateUuid(), '周年', 20));

        $this->assertSame(20, $repository->getMaxOrderNo());
    }

    #[Test]
    public function saveDuplicateNameViolatesUniqueConstraint(): void
    {
        $repository = $this->getInstance();

        $repository->save($this->createSongTag($this->generateUuid(), 'ライブ定番', 10));

        $this->expectException(QueryException::class);

        $repository->save($this->createSongTag($this->generateUuid(), 'ライブ定番', 20));
    }

    private function getInstance(): TagRepository
    {
        /** @var TagRepository */
        return $this->app->make(TagRepositoryInterface::class);
    }
}
