<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\Interactors;

use Performer\Application\Interactors\CreateInteractor;
use Performer\Application\UseCase\Create\CreateInputData;
use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Domain\Models\Performer;
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

        $performers = $this->getAll(Performer::class, FilePerformerRepository::class);
        $this->assertCount(1, $performers);
        $this->assertSame('ヰ世界情緒', array_first($performers)->performerName->value);
        $this->assertSame(10, array_first($performers)->orderNo->value);
    }

    private function getInstance(): CreateInteractor
    {
        return $this->app->make(CreateInteractor::class);
    }
}
