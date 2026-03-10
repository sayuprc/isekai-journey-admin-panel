<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Interactors;

use Creator\Application\Interactors\GetInteractor;
use Creator\Application\UseCase\Get\GetInputData;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\Error\NotFoundError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    use EntityFactory;

    private CreatorRepositoryInterface&MockInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);
    }

    #[Test]
    public function getCreator(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(fn (CreatorId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB')
            ->andReturn($this->createCreator('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'クリエイター名', 1))
            ->once();

        $result = $this->getInstance()->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->creator->creatorId->value);
        $this->assertSame('クリエイター名', $response->creator->name->value);
    }

    #[Test]
    public function failureGetCreator(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(fn (CreatorId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB')
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertFalse($result->isOk());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(NotFoundError::class, $error);
        $this->assertSame('Creator', $error->resourceName);
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
