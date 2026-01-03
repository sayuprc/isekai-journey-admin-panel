<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\Interactors;

use Performer\Application\Interactors\CreateInteractor;
use Performer\Application\UseCase\Create\CreateInputData;
use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function create(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData('ヰ世界情緒', 1));

        $this->assertTrue($result->isOk());

        /** @var array<Performer> $performers */
        $performers = $this->getAll(FilePerformerRepository::class);
        $this->assertCount(1, $performers);
        $this->assertSame('ヰ世界情緒', $performers[array_key_first($performers)]->performerName->value);
        $this->assertSame(1, $performers[array_key_first($performers)]->orderNo->value);
    }

    #[Test]
    public function createFailsIfNameAlreadyExists(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FilePerformerRepository::class,
            $uuid,
            new Performer(new PerformerId($uuid), new PerformerName('ヰ世界情緒'), new OrderNo(1)),
        );

        $result = $this->getInstance()->handle(new CreateInputData('ヰ世界情緒', 1));

        $this->assertTrue($result->isErr());

        /** @var array<Performer> $performers */
        $performers = $this->getAll(FilePerformerRepository::class);
        $this->assertCount(1, $performers);
    }

    private function getInstance(): CreateInteractor
    {
        return $this->app->make(CreateInteractor::class);
    }
}
