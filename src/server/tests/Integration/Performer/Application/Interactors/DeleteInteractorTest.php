<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\Interactors;

use Performer\Application\Interactors\DeleteInteractor;
use Performer\Application\UseCase\Delete\DeleteInputData;
use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class DeleteInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FilePerformerRepository::class,
            $uuid,
            new Performer(new PerformerId($uuid), new PerformerName('共演者'), new OrderNo(1))
        );

        $this->getInstance()->handle(new DeleteInputData($uuid));

        /** @var array<Performer> $performers */
        $performers = $this->getAll(FilePerformerRepository::class);
        $this->assertCount(0, $performers);
    }

    private function getInstance(): DeleteInteractor
    {
        return $this->app->make(DeleteInteractor::class);
    }
}
