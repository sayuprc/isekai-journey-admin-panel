<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Get;

use Creator\Application\Get\GetInteractor;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\Get\GetRequest;
use Creator\UseCases\Get\GetResponse;
use Creator\UseCases\Get\GetUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\ResultType\Result;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
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
            ->andReturn(new Creator(
                new CreatorId('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
                new CreatorName('クリエイター名'),
            ))
            ->once();

        $result = $this->interactor->handle(new GetRequest('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertInstanceOf(GetResponse::class, $response);

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

        $result = $this->interactor->handle(new GetRequest('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOk());

        $this->assertSame('Creator not found: BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $result->unwrapErr());
    }
}
