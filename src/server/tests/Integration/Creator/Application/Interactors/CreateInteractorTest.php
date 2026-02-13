<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\Interactors;

use Creator\Application\Interactors\CreateInteractor;
use Creator\Application\UseCase\Create\CreateInputData;
use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function create(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData('ヰ世界情緒'));

        $this->assertTrue($result->isOk());

        $creators = $this->getAll(Creator::class, FileCreatorRepository::class);
        $this->assertCount(1, $creators);
        $this->assertSame('ヰ世界情緒', array_first($creators)->creatorName->value);
    }

    private function getInstance(): CreateInteractor
    {
        return $this->app->make(CreateInteractor::class);
    }
}
