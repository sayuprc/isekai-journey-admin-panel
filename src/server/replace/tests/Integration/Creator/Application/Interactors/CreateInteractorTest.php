<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\Interactors;

use Creator\Application\Interactors\CreateInteractor;
use Creator\Application\UseCase\Create\CreateInputData;
use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function create(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData('ヰ世界情緒'));

        $this->assertTrue($result->isOk());

        /** @var array<Creator> $creators */
        $creators = $this->getAll(FileCreatorRepository::class);
        $this->assertCount(1, $creators);
        $this->assertSame('ヰ世界情緒', $creators[array_key_first($creators)]->creatorName->value);
    }

    #[Test]
    public function createFailsIfNameAlreadyExists(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileCreatorRepository::class,
            $uuid,
            new Creator(new CreatorId($uuid), new CreatorName('ヰ世界情緒'))
        );

        $result = $this->getInstance()->handle(new CreateInputData('ヰ世界情緒'));

        $this->assertTrue($result->isErr());

        /** @var array<Creator> $creators */
        $creators = $this->getAll(FileCreatorRepository::class);
        $this->assertCount(1, $creators);
    }

    private function getInstance(): CreateInteractor
    {
        return $this->container->get(CreateInteractor::class);
    }
}
