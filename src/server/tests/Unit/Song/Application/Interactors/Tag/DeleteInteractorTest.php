<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Interactors\Tag;

use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\Tag\DeleteInteractor;
use Song\Application\UseCase\Tag\Delete\DeleteInputData;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\InvalidInputError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class DeleteInteractorTest extends TestCase
{
    use EntityFactory;

    private MockInterface&SongTagRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongTagRepositoryInterface::class);
    }

    #[Test]
    public function deleteSongTag(): void
    {
        $songTagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->repository->shouldReceive('delete')
            ->withArgs(fn (SongTagId $arg): bool => $arg->value === $songTagId)
            ->once();

        $result = $this->getInstance($this->privilegedContext())->handle(new DeleteInputData($songTagId));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function deleteSongTagFailsIfUnauthenticated(): void
    {
        $context = $this->app->make(AuthContext::class);

        $result = $this->getInstance($context)->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthenticationError::class, $result->unwrapErr());
    }

    #[Test]
    public function deleteSongTagFailsIfNoPermission(): void
    {
        $context = $this->app->make(AuthContext::class);
        $context->set($this->createAdminUser($this->generateUuid(), 'general@example.com', Role::General));

        $result = $this->getInstance($context)->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    #[Test]
    public function deleteSongTagFailsIfIdIsInvalid(): void
    {
        $this->repository->shouldNotReceive('delete');

        $result = $this->getInstance($this->privilegedContext())->handle(new DeleteInputData('invalid'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(InvalidInputError::class, $result->unwrapErr());
    }

    private function getInstance(AuthContext $context): DeleteInteractor
    {
        return new DeleteInteractor(
            $context,
            $this->repository,
        );
    }
}
