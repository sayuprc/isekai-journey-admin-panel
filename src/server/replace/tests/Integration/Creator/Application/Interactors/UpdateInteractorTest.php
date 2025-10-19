<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\Interactors;

use Creator\Application\Interactors\UpdateInteractor;
use Creator\Application\UseCase\Update\UpdateInputData;
use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileCreatorRepository::class,
            $uuid,
            new Creator(new CreatorId($uuid), new CreatorName('クリエイター'))
        );

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

        $this->factory(
            FileCreatorRepository::class,
            $uuid,
            new Creator(new CreatorId($uuid), new CreatorName('ヰ世界情緒'))
        );

        $result = $this->getInstance()->handle(new UpdateInputData($uuid, 'ヰ世界情緒'));

        $this->assertTrue($result->isErr());

        /** @var array<Creator> $creators */
        $creators = $this->getAll(FileCreatorRepository::class);
        $this->assertCount(1, $creators);
        $this->assertSame('ヰ世界情緒', $creators[$uuid]->creatorName->value);
    }

    private function getInstance(): UpdateInteractor
    {
        return $this->container->get(UpdateInteractor::class);
    }
}
