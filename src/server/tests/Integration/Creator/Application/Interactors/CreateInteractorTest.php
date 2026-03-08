<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\Interactors;

use App\Models\Creator\Creator as ModelsCreator;
use Creator\Application\Interactors\CreateInteractor;
use Creator\Application\UseCase\Create\CreateInputData;
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

        $creators = ModelsCreator::query()->get();
        $this->assertCount(1, $creators);
        $this->assertSame('ヰ世界情緒', $creators->first()->name);
    }

    private function getInstance(): CreateInteractor
    {
        $this->privilegedContext();

        return $this->app->make(CreateInteractor::class);
    }
}
