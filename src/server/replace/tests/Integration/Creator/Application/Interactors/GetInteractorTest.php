<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\Interactors;

use Creator\Application\Interactors\GetInteractor;
use Creator\Application\UseCase\Get\GetInputData;
use Creator\Application\UseCase\Get\GetOutputData;
use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Result;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function getCreator(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileCreatorRepository::class,
            $uuid,
            new Creator(new CreatorId($uuid), new CreatorName('ヰ世界情緒'))
        );

        $result = $this->getInstance()->handle(new GetInputData($uuid));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertInstanceOf(GetOutputData::class, $response);

        $this->assertInstanceOf(Creator::class, $response->creator);
        $this->assertSame($uuid, $response->creator->creatorId->value);
        $this->assertSame('ヰ世界情緒', $response->creator->creatorName->value);
    }

    #[Test]
    public function failureGetCreator(): void
    {
        $result = $this->getInstance()->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isErr());

        $this->assertSame('Creator not found: BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $result->unwrapErr());
    }

    private function getInstance(): GetInteractor
    {
        return $this->container->get(GetInteractor::class);
    }
}
