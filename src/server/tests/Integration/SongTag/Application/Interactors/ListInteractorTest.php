<?php

declare(strict_types=1);

namespace Tests\Integration\SongTag\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use SongTag\Application\Interactors\ListInteractor;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListInteractorTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function nonEmptyTags(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongTags($this->createSongTag($uuid, 'ライブ定番', 1));

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertCount(1, $response->tags);
        $this->assertSame($uuid, $response->tags[0]->tagId->value);
        $this->assertSame('ライブ定番', $response->tags[0]->name->value);
    }

    private function getInstance(): ListInteractor
    {
        $this->privilegedContext();

        return $this->app->make(ListInteractor::class);
    }
}
