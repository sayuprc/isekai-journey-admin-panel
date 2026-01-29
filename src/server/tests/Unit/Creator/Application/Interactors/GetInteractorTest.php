<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Interactors;

use Creator\Application\Interactors\GetInteractor;
use Creator\Application\UseCase\Get\GetInputData;
use Creator\Application\UseCase\Get\GetOutputData;
use Creator\Application\UseCase\Get\GetUseCaseInterface;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    use EntityFactory;

    private CreatorRepositoryInterface&MockInterface $repository;

    private GetInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);

        $this->interactor = new GetInteractor($this->repository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(GetUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function getCreator(): void
    {
        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (CreatorId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'))
            ->andReturn($this->createCreator('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'クリエイター名'))
            ->once();

        $result = $this->interactor->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertInstanceOf(GetOutputData::class, $response);

        $this->assertInstanceOf(Creator::class, $response->creator);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->creator->creatorId->value);
        $this->assertSame('クリエイター名', $response->creator->creatorName->value);
    }

    #[Test]
    public function failureGetCreator(): void
    {
        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (CreatorId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'))
            ->andReturnNull()
            ->once();

        $result = $this->interactor->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertFalse($result->isOk());

        $this->assertSame('Creator not found: BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $result->unwrapErr());
    }
}
