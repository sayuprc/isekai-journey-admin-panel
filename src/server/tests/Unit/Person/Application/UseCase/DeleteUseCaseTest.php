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
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongRepositoryInterface;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Tests\TestCase;

class DeleteUseCaseTest extends TestCase
{
    private MockInterface&PersonRepositoryInterface $repository;

    private MockInterface&SongRepositoryInterface $songRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(PersonRepositoryInterface::class);
        $this->songRepository = Mockery::mock(SongRepositoryInterface::class);
    }

    #[Test]
    public function deletePerson(): void
    {
        $this->songRepository->shouldReceive('isPersonUsed')
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
        $this->songRepository->shouldReceive('isPersonUsed')
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
        $this->songRepository->shouldNotReceive('isPersonUsed');
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
            $this->songRepository,
        );
    }
}
