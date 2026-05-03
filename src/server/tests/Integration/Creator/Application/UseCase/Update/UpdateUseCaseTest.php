<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\UseCase\Update;

use Creator\Application\UseCase\Update\UpdateInputData;
use Creator\Application\UseCase\Update\UpdateUseCase;
use Creator\Domain\Models\CreatorRepositoryInterface;
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
        $creatorId = $this->generateUuid();

        $beforeName = 'クリエイター';
        $afterName = 'ヰ世界情緒';

        $this->storeCreators($this->createCreator($creatorId, $beforeName, 10));

        $result = $this->getInstance()->handle(new UpdateInputData($creatorId, $afterName, 20));

        $this->assertTrue($result->isOk());

        $creators = $this->app->make(CreatorRepositoryInterface::class)->all();
        $this->assertCount(1, $creators);
        $this->assertSame($afterName, array_first($creators)->name->value);
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
