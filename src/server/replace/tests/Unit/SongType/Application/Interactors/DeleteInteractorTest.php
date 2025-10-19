<?php

declare(strict_types=1);

namespace Tests\Unit\SongType\Application\Interactors;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SongType\Application\Interactors\DeleteInteractor;
use SongType\Application\UseCase\Delete\DeleteInputData;
use SongType\Application\UseCase\Delete\DeleteUseCaseInterface;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use Tests\TestCase;

class DeleteInteractorTest extends TestCase
{
    private MockInterface&SongTypeRepositoryInterface $repository;

    private DeleteInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongTypeRepositoryInterface::class);

        $this->interactor = new DeleteInteractor($this->repository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(DeleteUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function deleteSongType(): void
    {
        $this->repository->shouldReceive('delete')
            ->with(Mockery::on(fn (SongTypeId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->once();

        $this->interactor->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));
    }
}
