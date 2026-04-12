<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors\Tag;

use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use Closure;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Song\Application\Interactors\Tag\UpdateInteractor;
use Song\Application\UseCase\Tag\Update\UpdateInputData;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Domain\Services\SongTagIntegrityService;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\NotFoundError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&SongTagRepositoryInterface $repository;

    private MockInterface&SongTagIntegrityService $service;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(SongTagRepositoryInterface::class);
        $this->service = Mockery::mock(SongTagIntegrityService::class);
    }

    #[Test]
    public function updateSongTag(): void
    {
        $songTagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'ロック';
        $orderNo = 20;
        $beforeTag = new SongTag(
            SongTagId::reconstruct($songTagId),
            SongTagName::reconstruct('ポップ'),
            OrderNo::reconstruct(10),
        );
        $afterTag = new SongTag(
            SongTagId::reconstruct($songTagId),
            SongTagName::reconstruct($name),
            OrderNo::reconstruct($orderNo),
        );

        $this->repository->shouldReceive('find')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === $songTagId)
            ->andReturn($beforeTag)
            ->once();

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($songTagId, $name, $orderNo)
            ->andReturn(new Ok($afterTag))
            ->once();

        $this->repository->shouldReceive('save')
            ->withArgs(
                fn (SongTag $arg): bool => $arg->songTagId->value === $songTagId
                    && $arg->name->value === $name
                    && $arg->orderNo->value === $orderNo,
            )
            ->andReturn($afterTag)
            ->once();

        $result = $this->getInstance($this->privilegedContext())->handle(new UpdateInputData($songTagId, $name, $orderNo));

        $this->assertTrue($result->isOk());
        $this->assertSame($afterTag, $result->unwrap()->tag);
    }

    #[Test]
    public function updateSongTagFailsIfUnauthenticated(): void
    {
        $context = $this->app->make(AuthContext::class);

        $result = $this->getInstance($context)->handle(new UpdateInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'ロック', 20));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    #[Test]
    public function updateSongTagFailsIfNoPermission(): void
    {
        $context = $this->app->make(AuthContext::class);
        $context->set($this->createAdminUser($this->generateUuid(), 'general@example.com', Role::General));

        $result = $this->getInstance($context)->handle(new UpdateInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'ロック', 20));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    #[Test]
    public function updateSongTagFailsIfNotFound(): void
    {
        $songTagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->repository->shouldReceive('find')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === $songTagId)
            ->andReturnNull()
            ->once();

        $this->transaction->shouldNotReceive('scope');
        $this->service->shouldNotReceive('prepareForUpdate');
        $this->repository->shouldNotReceive('save');

        $result = $this->getInstance($this->privilegedContext())->handle(new UpdateInputData($songTagId, 'ロック', 20));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(NotFoundError::class, $error);
        $this->assertSame('楽曲タグ', $error->resourceName);
        $this->assertSame($songTagId, $error->identifier);
    }

    #[Test]
    public function updateSongTagFailsIfDomainServiceReturnsError(): void
    {
        $songTagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'ロック';
        $orderNo = 20;

        $this->repository->shouldReceive('find')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === $songTagId)
            ->andReturn(new SongTag(
                SongTagId::reconstruct($songTagId),
                SongTagName::reconstruct('ポップ'),
                OrderNo::reconstruct(10),
            ))
            ->once();

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($songTagId, $name, $orderNo)
            ->andReturn(new Err(new BusinessRuleViolationError('すでに使われているタグ名です "ロック"')))
            ->once();

        $this->repository->shouldNotReceive('save');

        $result = $this->getInstance($this->privilegedContext())->handle(new UpdateInputData($songTagId, $name, $orderNo));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(BusinessLogicError::class, $result->unwrapErr());
    }

    private function getInstance(AuthContext $context): UpdateInteractor
    {
        return new UpdateInteractor(
            $context,
            $this->transaction,
            $this->repository,
            $this->service,
        );
    }
}
