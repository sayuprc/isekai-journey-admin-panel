<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Delete;

use Creator\Application\Delete\DeleteInteractor;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\Delete\DeleteRequest;
use Creator\UseCases\Delete\DeleteUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteInteractorTest extends TestCase
{
    private CreatorRepositoryInterface&MockInterface $repository;

    private DeleteInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);

        $this->interactor = new DeleteInteractor($this->repository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(DeleteUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function deleteJourneyLog(): void
    {
        $this->repository->shouldReceive('delete')
            ->with(Mockery::on(fn (CreatorId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->once();

        $this->interactor->handle(new DeleteRequest('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));
    }
}
