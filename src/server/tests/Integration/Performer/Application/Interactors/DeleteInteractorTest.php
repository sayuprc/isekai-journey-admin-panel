<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\Interactors;

use Performer\Application\Interactors\DeleteInteractor;
use Performer\Application\UseCase\Delete\DeleteInputData;
use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Domain\Models\Performer;
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

        $this->factory(FilePerformerRepository::class, $this->createPerformer($uuid, '共演者', 1)->toArray());

        $this->getInstance()->handle(new DeleteInputData($uuid));

        $performers = $this->getAll(Performer::class, FilePerformerRepository::class);
        $this->assertCount(0, $performers);
    }

    private function getInstance(): DeleteInteractor
    {
        return $this->app->make(DeleteInteractor::class);
    }
}
