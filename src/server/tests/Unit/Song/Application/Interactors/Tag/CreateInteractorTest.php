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
use Song\Application\Interactors\Tag\CreateInteractor;
use Song\Application\UseCase\Tag\Create\CreateInputData;
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
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
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
    public function create(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'ロック';

        $tag = new SongTag(
            SongTagId::reconstruct($uuid),
            SongTagName::reconstruct($name),
            OrderNo::reconstruct(10),
        );

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForCreate')
            ->with($name)
            ->andReturn(new Ok($tag))
            ->once();

        $this->repository->shouldReceive('save')
            ->withArgs(fn (SongTag $arg): bool => $arg->songTagId->value === $uuid)
            ->andReturn($tag)
            ->once();

        $result = $this->getInstance($this->privilegedContext())->handle(new CreateInputData($name));

        $this->assertTrue($result->isOk());
        $this->assertSame($tag, $result->unwrap()->tag);
    }

    #[Test]
    public function createFailsIfUnauthenticated(): void
    {
        $context = $this->app->make(AuthContext::class);

        $result = $this->getInstance($context)->handle(new CreateInputData('ロック'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    #[Test]
    public function createFailsIfNoPermission(): void
    {
        $context = $this->app->make(AuthContext::class);
        $context->set($this->createAdminUser($this->generateUuid(), 'general@example.com', Role::General));

        $result = $this->getInstance($context)->handle(new CreateInputData('ロック'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    #[Test]
    public function createFailsIfDomainServiceReturnsError(): void
    {
        $name = 'ロック';

        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->once();

        $this->service->shouldReceive('prepareForCreate')
            ->with($name)
            ->andReturn(new Err(new BusinessRuleViolationError('すでに使われているタグ名です "ロック"')))
            ->once();

        $result = $this->getInstance($this->privilegedContext())->handle(new CreateInputData($name));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(BusinessLogicError::class, $result->unwrapErr());
    }

    private function getInstance(AuthContext $context): CreateInteractor
    {
        return new CreateInteractor(
            $context,
            $this->transaction,
            $this->repository,
            $this->service,
        );
    }
}
