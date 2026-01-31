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
        $creatorId = $this->generateUuid();

        $beforeName = 'クリエイター';
        $afterName = 'ヰ世界情緒';

        $this->factory(FileCreatorRepository::class, $creatorId, $this->createCreator($creatorId, $beforeName));

        $result = $this->getInstance()->handle(new UpdateInputData($creatorId, $afterName));

        $this->assertTrue($result->isOk());

        /** @var array<Creator> $creators */
        $creators = $this->getAll(FileCreatorRepository::class);
        $this->assertCount(1, $creators);
        $this->assertSame($afterName, $creators[$creatorId]->creatorName->value);
    }

    #[Test]
    public function updateFailsIfNameAlreadyExists(): void
    {
        $targetId = $this->generateUuid();
        $otherId = $this->generateUuid();

        $beforeName = 'クリエイター';
        $afterName = 'ヰ世界情緒';

        $this->factory(FileCreatorRepository::class, $targetId, $this->createCreator($targetId, $beforeName));
        $this->factory(FileCreatorRepository::class, $otherId, $this->createCreator($otherId, $afterName));

        $result = $this->getInstance()->handle(new UpdateInputData($targetId, $afterName));

        $this->assertTrue($result->isErr());

        /** @var array<Creator> $creators */
        $creators = $this->getAll(FileCreatorRepository::class);
        $this->assertCount(2, $creators);
        $this->assertSame($afterName, $creators[$otherId]->creatorName->value);
        $this->assertSame($beforeName, $creators[$targetId]->creatorName->value);
    }

    private function getInstance(): UpdateInteractor
    {
        return $this->app->make(UpdateInteractor::class);
    }
}
