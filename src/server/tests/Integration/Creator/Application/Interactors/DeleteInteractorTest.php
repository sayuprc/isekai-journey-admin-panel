<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\Interactors;

use App\Models\Creator\Creator as ModelsCreator;
use Creator\Application\Interactors\DeleteInteractor;
use Creator\Application\UseCase\Delete\DeleteInputData;
use PHPUnit\Framework\Attributes\Test;
use SongType\Domain\Models\SongType;
use Support\UseCase\Error\BusinessLogicError;
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

        $this->storeCreators($this->createCreator($uuid, 'クリエイター', 1));

        $result = $this->getInstance()->handle(new DeleteInputData($uuid));

        $this->assertTrue($result->isOk());

        $creators = ModelsCreator::query()->get()->all();
        $this->assertCount(0, $creators);
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $creatorId = $this->generateUuid();
        $songId = $this->generateUuid();

        $this->storeCreators($this->createCreator($creatorId, 'クリエイター', 1));
        $this->storeSongs($this->createSong(
            $songId,
            '曲名',
            '説明',
            SongType::Original,
            null,
            1,
            [['creatorId' => $creatorId, 'orderNo' => 1]],
            [],
            [],
        ));

        $result = $this->getInstance()->handle(new DeleteInputData($creatorId));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessLogicError::class, $error);
        $this->assertSame('このクリエイターは楽曲に使用されているため削除できません', $error->message);

        $creators = ModelsCreator::query()->get()->all();
        $this->assertCount(1, $creators);
    }

    private function getInstance(): DeleteInteractor
    {
        $this->privilegedContext();

        return $this->app->make(DeleteInteractor::class);
    }
}
