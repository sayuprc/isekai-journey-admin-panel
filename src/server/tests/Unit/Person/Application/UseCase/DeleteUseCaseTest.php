<?php

declare(strict_types=1);

namespace Tests\Unit\Person\Application\UseCase;

use Mockery;
use Mockery\MockInterface;
use Override;
use Person\Application\UseCase\Delete\DeleteInputData;
use Person\Application\UseCase\Delete\DeleteUseCase;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonRepositoryInterface;
use Person\Domain\Services\PersonUsageCheckerInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Tests\TestCase;

class DeleteUseCaseTest extends TestCase
{
    private MockInterface&PersonRepositoryInterface $repository;

    private MockInterface&PersonUsageCheckerInterface $usageChecker;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(PersonRepositoryInterface::class);
        $this->usageChecker = Mockery::mock(PersonUsageCheckerInterface::class);
    }

    #[Test]
    public function deletePerson(): void
    {
        $this->usageChecker->shouldReceive('isUsed')
            ->withArgs(fn (PersonId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn(false)
            ->once();

        $this->repository->shouldReceive('delete')
            ->withArgs(fn (PersonId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $result = $this->getInstance()->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $this->usageChecker->shouldReceive('isUsed')
            ->withArgs(fn (PersonId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn(true)
            ->once();

        $this->repository->shouldNotReceive('delete');

        $result = $this->getInstance()->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessLogicError::class, $error);
        $this->assertSame('この人物は楽曲に使用されているため削除できません', $error->message);
    }

    #[Test]
    public function invalidPersonId(): void
    {
        $this->usageChecker->shouldNotReceive('isUsed');
        $this->repository->shouldNotReceive('delete');

        $result = $this->getInstance()->handle(new DeleteInputData('invalid-id'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(InvalidInputError::class, $result->unwrapErr());
    }

    private function getInstance(): DeleteUseCase
    {
        return new DeleteUseCase(
            $this->authorizer(),
            $this->repository,
            $this->usageChecker,
        );
    }
}
