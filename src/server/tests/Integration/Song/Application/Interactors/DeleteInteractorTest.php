<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\DeleteInteractor;
use Song\Application\UseCase\Delete\DeleteInputData;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteInteractorTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid, '', '', SongType::Original, null, 1, [], [], []),
        );

        $result = $this->getInstance()->handle(new DeleteInputData($uuid));

        $this->assertTrue($result->isOk());

        $songs = $this->app->make(SongRepositoryInterface::class)->all();
        $this->assertCount(0, $songs);
    }

    private function getInstance(): DeleteInteractor
    {
        $this->privilegedContext();

        return $this->app->make(DeleteInteractor::class);
    }
}
