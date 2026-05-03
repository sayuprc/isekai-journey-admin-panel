<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\UseCase\Delete;

use Performer\Application\UseCase\Delete\DeleteInputData;
use Performer\Application\UseCase\Delete\DeleteUseCase;
use Performer\Domain\Models\PerformerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->storePerformers($this->createPerformer($uuid, '共演者', 1));

        $result = $this->getInstance()->handle(new DeleteInputData($uuid));

        $this->assertTrue($result->isOk());

        $performers = $this->app->make(PerformerRepositoryInterface::class)->all();
        $this->assertCount(0, $performers);
    }

    private function getInstance(): DeleteUseCase
    {
        $this->privilegedContext();

        return $this->app->make(DeleteUseCase::class);
    }
}
