<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\Interactors;

use Performer\Application\Interactors\SearchInteractor;
use Performer\Application\UseCase\Search\SearchInputData;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchInteractorTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function searchWithoutName(): void
    {
        $uuid = $this->generateUuid();

        $this->storePerformers($this->createPerformer($uuid, 'ヰ世界情緒', 1));

        $result = $this->getInstance()->handle(new SearchInputData());

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();

        $this->assertCount(1, $output->performers);
        $this->assertSame($uuid, $output->performers[0]->performerId->value);
        $this->assertSame('ヰ世界情緒', $output->performers[0]->name->value);
    }

    #[Test]
    public function searchWithName(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storePerformers(
            $this->createPerformer($uuid1, 'ヰ世界情緒', 1),
            $this->createPerformer($uuid2, '春猿火', 2),
        );

        $result = $this->getInstance()->handle(new SearchInputData(name: 'ヰ世界情緒'));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();

        $this->assertCount(1, $output->performers);
        $this->assertSame($uuid1, $output->performers[0]->performerId->value);
    }

    #[Test]
    public function searchWithNameNotFound(): void
    {
        $uuid = $this->generateUuid();

        $this->storePerformers($this->createPerformer($uuid, 'ヰ世界情緒', 1));

        $result = $this->getInstance()->handle(new SearchInputData(name: '存在しない名前'));

        $this->assertTrue($result->isOk());

        $output = $result->unwrap();

        $this->assertCount(0, $output->performers);
        $this->assertSame(0, $output->maxPage);
    }

    private function getInstance(): SearchInteractor
    {
        $this->privilegedContext();

        return $this->app->make(SearchInteractor::class);
    }
}
