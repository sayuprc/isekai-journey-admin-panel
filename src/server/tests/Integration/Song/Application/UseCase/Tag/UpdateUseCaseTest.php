<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\UseCase\Tag;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\UseCase\Tag\Update\UpdateInputData;
use Song\Application\UseCase\Tag\Update\UpdateUseCase;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongTags($this->createSongTag($uuid, '旧タグ', 1));

        $result = $this->getInstance()->handle(new UpdateInputData($uuid, '派生曲', 2));

        $this->assertTrue($result->isOk());

        $tags = $this->app->make(SongTagRepositoryInterface::class)->all();
        $this->assertCount(1, $tags);
        $this->assertSame('派生曲', array_first($tags)->name->value);
        $this->assertSame(2, array_first($tags)->orderNo->value);
    }

    #[Test]
    public function updateFailsWhenSongTagDoesNotExist(): void
    {
        $uuid = $this->generateUuid();

        $result = $this->getInstance()->handle(new UpdateInputData($uuid, '派生曲', 2));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
