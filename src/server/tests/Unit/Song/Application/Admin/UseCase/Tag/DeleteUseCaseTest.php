<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Admin\UseCase\Tag;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use Closure;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Tag\Delete\DeleteInputData;
use Song\Application\Admin\UseCase\Tag\Delete\DeleteUseCase;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class DeleteUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&SongTagRepositoryInterface $repository;

    private AuditLogRecorderInterface&MockInterface $recorder;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->byDefault();
        $this->repository = Mockery::mock(SongTagRepositoryInterface::class);
        $this->recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $this->recorder->shouldReceive('record')->byDefault();
    }

    #[Test]
    public function deleteSongTag(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn($this->createSongTag('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'タグ', 1))
            ->once();

        $this->repository->shouldReceive('isUsed')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn(false)
            ->once();

        $this->repository->shouldReceive('delete')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $result = $this->getInstance()->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function cannotDeleteWhenUsed(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn($this->createSongTag('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'タグ', 1))
            ->once();

        $this->repository->shouldReceive('isUsed')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn(true)
            ->once();

        $this->repository->shouldNotReceive('delete');

        $result = $this->getInstance()->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessLogicError::class, $error);
        $this->assertSame('この楽曲タグは楽曲に使用されているため削除できません', $error->message);
    }

    #[Test]
    public function unauthenticated(): void
    {
        $this->repository->shouldNotReceive('isUsed');
        $this->repository->shouldNotReceive('delete');

        $context = $this->app->make(AuthContext::class);

        $result = $this->getInstance($context)->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    #[Test]
    public function unauthorized(): void
    {
        $this->repository->shouldNotReceive('isUsed');
        $this->repository->shouldNotReceive('delete');

        $context = $this->app->make(AuthContext::class);
        $context->set(AdminUser::reconstruct(
            $this->generateUuid(),
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            [],
        ));

        $result = $this->getInstance($context)->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    #[Test]
    public function invalidSongTagId(): void
    {
        $this->repository->shouldNotReceive('isUsed');
        $this->repository->shouldNotReceive('delete');

        $result = $this->getInstance()->handle(new DeleteInputData('invalid-id'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(InvalidInputError::class, $result->unwrapErr());
    }

    private function getInstance(?AuthContext $context = null): DeleteUseCase
    {
        $context ??= $this->privilegedContext();

        return new DeleteUseCase(
            $this->authorizer($context),
            $this->transaction,
            $this->repository,
            $this->recorder,
        );
    }
}
