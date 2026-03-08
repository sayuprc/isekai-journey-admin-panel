<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\Interactors;

use Performer\Application\Interactors\CreateInteractor;
use Performer\Application\UseCase\Create\CreateInputData;
use Performer\Domain\Models\PerformerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class CreateInteractorTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function create(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData('ヰ世界情緒'));

        $this->assertTrue($result->isOk());

        $performers = $this->app->make(PerformerRepositoryInterface::class)->all();
        $this->assertCount(1, $performers);
        $this->assertSame('ヰ世界情緒', array_first($performers)->name->value);
        $this->assertSame(10, array_first($performers)->orderNo->value);
    }

    private function getInstance(): CreateInteractor
    {
        $this->privilegedContext();

        return $this->app->make(CreateInteractor::class);
    }
}
