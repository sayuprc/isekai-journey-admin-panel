<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\Interactors;

use Performer\Application\Interactors\GetInteractor;
use Performer\Application\UseCase\Get\GetInputData;
use Performer\Application\UseCase\Get\GetOutputData;
use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Domain\Models\Performer;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Result;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function getPerformer(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FilePerformerRepository::class, $uuid, $this->createPerformer($uuid, 'ヰ世界情緒', 1));

        $result = $this->getInstance()->handle(new GetInputData($uuid));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertInstanceOf(GetOutputData::class, $response);

        $this->assertInstanceOf(Performer::class, $response->performer);
        $this->assertSame($uuid, $response->performer->performerId->value);
        $this->assertSame('ヰ世界情緒', $response->performer->performerName->value);
        $this->assertSame(1, $response->performer->orderNo->value);
    }

    #[Test]
    public function failureGetPerformer(): void
    {
        $result = $this->getInstance()->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertTrue($result->isErr());

        $this->assertSame('Performer not found: BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $result->unwrapErr());
    }

    private function getInstance(): GetInteractor
    {
        return $this->app->make(GetInteractor::class);
    }
}
