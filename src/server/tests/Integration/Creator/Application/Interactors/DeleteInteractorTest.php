<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\Interactors;

use Creator\Application\Interactors\DeleteInteractor;
use Creator\Application\UseCase\Delete\DeleteInputData;
use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use PHPUnit\Framework\Attributes\Test;
use Song\DebugInfrastructures\FileSongRepository;
use SongType\Domain\Models\SongType;
use Support\UseCase\Error\BusinessLogicError;
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

        $this->factory(FileCreatorRepository::class, $this->createCreator($uuid, 'クリエイター', 1)->toArray());

        $result = $this->getInstance()->handle(new DeleteInputData($uuid));

        $this->assertTrue($result->isOk());

        $creators = $this->getAll(Creator::class, FileCreatorRepository::class);
        $this->assertCount(0, $creators);
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $creatorId = $this->generateUuid();
        $songId = $this->generateUuid();

        $this->factory(FileCreatorRepository::class, $this->createCreator($creatorId, 'クリエイター', 1)->toArray());
        $this->factory(FileSongRepository::class, $this->createSong(
            $songId,
            '曲名',
            '説明',
            SongType::Original,
            null,
            1,
            [['creatorId' => $creatorId, 'orderNo' => 1]],
            [],
            [],
        )->toArray());

        $result = $this->getInstance()->handle(new DeleteInputData($creatorId));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessLogicError::class, $error);
        $this->assertSame('このクリエイターは楽曲に使用されているため削除できません', $error->message);

        $creators = $this->getAll(Creator::class, FileCreatorRepository::class);
        $this->assertCount(1, $creators);
    }

    private function getInstance(): DeleteInteractor
    {
        $this->privilegedContext();

        return $this->app->make(DeleteInteractor::class);
    }
}
