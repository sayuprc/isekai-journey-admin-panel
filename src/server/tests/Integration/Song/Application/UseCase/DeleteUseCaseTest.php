<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\UseCase;

use App\Models\Song\Song;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\UseCase\Delete\DeleteInputData;
use Song\Application\UseCase\Delete\DeleteUseCase;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($uuid, '', '', SongType::Original, true, 1, [], [], [], []),
        );

        $result = $this->getInstance()->handle(new DeleteInputData($uuid));

        $this->assertTrue($result->isOk());

        $songs = Song::query()->get();
        $this->assertCount(0, $songs);
    }

    private function getInstance(): DeleteUseCase
    {
        $this->privilegedContext();

        return $this->app->make(DeleteUseCase::class);
    }
}
