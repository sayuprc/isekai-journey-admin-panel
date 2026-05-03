<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\UseCase\Search;

use Creator\Application\UseCase\Search\SearchInputData;
use Creator\Application\UseCase\Search\SearchUseCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function searchWithoutName(): void
    {
        $uuid = $this->generateUuid();

        $this->storeCreators($this->createCreator($uuid, 'ヰ世界情緒', 1));

        $result = $this->getInstance()->handle(new SearchInputData());

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();

        $this->assertCount(1, $output->creators);
        $this->assertSame($uuid, $output->creators[0]->creatorId->value);
        $this->assertSame('ヰ世界情緒', $output->creators[0]->name->value);
    }

    #[Test]
    public function searchWithName(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storeCreators(
            $this->createCreator($uuid1, 'ヰ世界情緒', 1),
            $this->createCreator($uuid2, '香椎モイミ', 2),
        );

        $result = $this->getInstance()->handle(new SearchInputData(name: 'ヰ世界情緒'));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();

        $this->assertCount(1, $output->creators);
        $this->assertSame($uuid1, $output->creators[0]->creatorId->value);
    }

    #[Test]
    public function searchWithNameNotFound(): void
    {
        $uuid = $this->generateUuid();

        $this->storeCreators($this->createCreator($uuid, 'ヰ世界情緒', 1));

        $result = $this->getInstance()->handle(new SearchInputData(name: '存在しない名前'));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();

        $this->assertCount(0, $output->creators);
        $this->assertSame(0, $output->maxPage);
    }

    private function getInstance(): SearchUseCase
    {
        $this->privilegedContext();

        return $this->app->make(SearchUseCase::class);
    }
}
