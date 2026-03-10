<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\Interactors;

use Override;
use Mockery;
use Mockery\MockInterface;
use Performer\Application\Interactors\GetInteractor;
use Performer\Application\UseCase\Get\GetInputData;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\Error\NotFoundError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&PerformerRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);
    }

    #[Test]
    public function getPerformer(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(fn (PerformerId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB')
            ->andReturn($this->createPerformer('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', '共演者名', 1))
            ->once();

        $result = $this->getInstance()->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->performer->performerId->value);
        $this->assertSame('共演者名', $response->performer->name->value);
        $this->assertSame(1, $response->performer->orderNo->value);
    }

    #[Test]
    public function failureGetPerformer(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(fn (PerformerId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB')
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertFalse($result->isOk());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(NotFoundError::class, $error);
        $this->assertSame('Performer', $error->resourceName);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $error->identifier);
    }

    private function getInstance(): GetInteractor
    {
        return new GetInteractor(
            $this->privilegedContext(),
            $this->repository,
        );
    }
}
