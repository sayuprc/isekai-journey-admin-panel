<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\DeleteInteractor;
use Song\Application\UseCase\Delete\DeleteInputData;
use Song\DebugInfrastructures\FileSongRepository;
use Song\Domain\Models\Song;
use SongType\Domain\Models\SongType;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class DeleteInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileSongRepository::class,
            $this->createSong($uuid, '', '', SongType::Original, 1, [], [], [])->toArray(),
        );

        $result = $this->getInstance()->handle(new DeleteInputData($uuid));

        $this->assertTrue($result->isOk());

        $songs = $this->getAll(Song::class, FileSongRepository::class);
        $this->assertCount(0, $songs);
    }

    private function getInstance(): DeleteInteractor
    {
        $this->privilegedContext();

        return $this->app->make(DeleteInteractor::class);
    }
}
