<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\Interactors;

use Creator\Application\Interactors\UpdateInteractor;
use Creator\Application\UseCase\Update\UpdateInputData;
use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FileCreatorRepository::class, $uuid, $this->createCreator($uuid, 'クリエイター'));

        $result = $this->getInstance()->handle(new UpdateInputData($uuid, 'ヰ世界情緒'));

        $this->assertTrue($result->isOk());

        /** @var array<Creator> $creators */
        $creators = $this->getAll(FileCreatorRepository::class);
        $this->assertCount(1, $creators);
        $this->assertSame('ヰ世界情緒', $creators[$uuid]->creatorName->value);
    }

    #[Test]
    public function updateFailsIfNameAlreadyExists(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FileCreatorRepository::class, $uuid, $this->createCreator($uuid, 'ヰ世界情緒'));

        $result = $this->getInstance()->handle(new UpdateInputData($uuid, 'ヰ世界情緒'));

        $this->assertTrue($result->isErr());

        /** @var array<Creator> $creators */
        $creators = $this->getAll(FileCreatorRepository::class);
        $this->assertCount(1, $creators);
        $this->assertSame('ヰ世界情緒', $creators[$uuid]->creatorName->value);
    }

    private function getInstance(): UpdateInteractor
    {
        return $this->app->make(UpdateInteractor::class);
    }
}
