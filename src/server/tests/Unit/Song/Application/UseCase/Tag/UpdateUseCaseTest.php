<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\UseCase\Tag;

use Closure;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Song\Application\UseCase\Tag\Update\UpdateInputData;
use Song\Application\UseCase\Tag\Update\UpdateUseCase;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Domain\Services\SongTagIntegrityService;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainValidationError;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\Error\NotFoundError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class UpdateUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&SongTagRepositoryInterface $repository;

    private MockInterface&SongTagIntegrityService $service;

    private AuditLogRecorderInterface&MockInterface $recorder;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(SongTagRepositoryInterface::class);
        $this->service = Mockery::mock(SongTagIntegrityService::class);
        $this->recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $this->recorder->shouldReceive('record')->byDefault();
    }

    #[Test]
    public function editSongTag(): void
    {
        $songTagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = '派生曲';
        $orderNo = 1;

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->repository->shouldReceive('find')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === $songTagId)
            ->andReturn($this->createSongTag($songTagId, '旧タグ', 10))
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($songTagId, $name, $orderNo)
            ->andReturn(new Ok($tag = $this->createSongTag($songTagId, $name, $orderNo)))
            ->once();

        $this->repository->shouldReceive('save')
            ->withArgs(
                fn (SongTag $arg): bool => $arg->songTagId->value === $songTagId
                    && $arg->name->value === $name
                    && $arg->orderNo->value === $orderNo,
            )
            ->andReturn($tag)
            ->once();

        $result = $this->getInstance()->handle(new UpdateInputData($songTagId, $name, $orderNo));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function editFailsIfValidationErrorOccurs(): void
    {
        $songTagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = '派生曲';
        $orderNo = 1;

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->repository->shouldReceive('find')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === $songTagId)
            ->andReturn($this->createSongTag($songTagId, '旧タグ', 10))
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($songTagId, $name, $orderNo)
            ->andReturn(new Err(new DomainValidationError([])))
            ->once();

        $result = $this->getInstance()->handle(new UpdateInputData($songTagId, $name, $orderNo));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function editFailsWhenSongTagDoesNotExist(): void
    {
        $songTagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->repository->shouldReceive('find')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === $songTagId)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->handle(new UpdateInputData($songTagId, '派生曲', 1));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(NotFoundError::class, $error);
        $this->assertSame('SongTag', $error->resourceName);
        $this->assertSame($songTagId, $error->identifier);
    }

    private function getInstance(): UpdateUseCase
    {
        $context = $this->privilegedContext();

        return new UpdateUseCase(
            $this->authorizer($context),
            $this->transaction,
            $this->repository,
            $this->service,
            $this->recorder,
        );
    }
}
