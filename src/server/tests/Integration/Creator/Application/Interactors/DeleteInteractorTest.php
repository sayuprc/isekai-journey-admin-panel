<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\Interactors;

use Creator\Application\Interactors\DeleteInteractor;
use Creator\Application\UseCase\Delete\DeleteInputData;
use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use PHPUnit\Framework\Attributes\Test;
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

        $this->factory(FileCreatorRepository::class, $uuid, $this->createCreator($uuid, 'クリエイター'));

        $this->getInstance()->handle(new DeleteInputData($uuid));

        /** @var array<Creator> $creators */
        $creators = $this->getAll(FileCreatorRepository::class);
        $this->assertCount(0, $creators);
    }

    private function getInstance(): DeleteInteractor
    {
        return $this->app->make(DeleteInteractor::class);
    }
}
