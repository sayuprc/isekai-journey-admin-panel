<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\UseCase\Update;

use Performer\Application\UseCase\Update\UpdateInputData;
use Performer\Application\UseCase\Update\UpdateUseCase;
use Performer\Domain\Models\PerformerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->storePerformers($this->createPerformer($uuid, '共演者', 1));

        $result = $this->getInstance()->handle(new UpdateInputData($uuid, 'ヰ世界情緒', 2));

        $this->assertTrue($result->isOk());

        $performers = $this->app->make(PerformerRepositoryInterface::class)->all();
        $this->assertCount(1, $performers);
        $this->assertSame('ヰ世界情緒', array_first($performers)->name->value);
        $this->assertSame(2, array_first($performers)->orderNo->value);
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
