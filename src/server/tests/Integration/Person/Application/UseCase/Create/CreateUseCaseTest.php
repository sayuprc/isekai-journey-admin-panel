<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\UseCase\Create;

use App\Models\Person\Person as ModelsPerson;
use Person\Application\UseCase\Create\CreateInputData;
use Person\Application\UseCase\Create\CreateUseCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;

class CreateUseCaseTest extends DatabaseTestCase
{
    #[Test]
    public function create(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData('ヰ世界情緒'));

        $this->assertTrue($result->isOk());

        $persons = ModelsPerson::query()->get();
        $this->assertCount(1, $persons);
        $this->assertSame('ヰ世界情緒', $persons->first()->name);
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
