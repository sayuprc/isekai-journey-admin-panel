<?php

declare(strict_types=1);

namespace Tests\Integration\SongTag\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use SongTag\Application\Interactors\SearchInteractor;
use SongTag\Application\UseCase\Search\SearchInputData;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchInteractorTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function searchByName(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongTags($this->createSongTag($uuid, 'ライブ定番', 1));

        $result = $this->getInstance()->handle(new SearchInputData(name: 'ライブ'));
        $this->assertTrue($result->isOk());

        $output = $result->unwrap();
        $this->assertCount(1, $output->tags);
        $this->assertSame($uuid, $output->tags[0]->tagId->value);
        $this->assertSame('ライブ定番', $output->tags[0]->name->value);
    }

    private function getInstance(): SearchInteractor
    {
        $this->privilegedContext();

        return $this->app->make(SearchInteractor::class);
    }
}
