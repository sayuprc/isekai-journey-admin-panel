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

        $this->factory(FileCreatorRepository::class, $this->createCreator($creatorId, $beforeName, 10)->toArray());

        $result = $this->getInstance()->handle(new UpdateInputData($creatorId, $afterName, 20));

        $this->assertTrue($result->isOk());

        $creators = $this->getAll(Creator::class, FileCreatorRepository::class);
        $this->assertCount(1, $creators);
        $this->assertSame($afterName, array_first($creators)->name->value);
    }

    private function getInstance(): UpdateInteractor
    {
        $this->privilegedContext();

        return $this->app->make(UpdateInteractor::class);
    }
}
