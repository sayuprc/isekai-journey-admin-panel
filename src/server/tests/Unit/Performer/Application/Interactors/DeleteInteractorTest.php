<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\Interactors;

use Mockery;
use Mockery\MockInterface;
use Performer\Application\Interactors\DeleteInteractor;
use Performer\Application\UseCase\Delete\DeleteInputData;
use Performer\Application\UseCase\Delete\DeleteUseCaseInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteInteractorTest extends TestCase
{
    private MockInterface&PerformerRepositoryInterface $repository;

    private DeleteInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);

        $this->interactor = new DeleteInteractor($this->repository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(DeleteUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function deletePerformer(): void
    {
        $this->repository->shouldReceive('delete')
            ->with(Mockery::on(fn (PerformerId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->once();

        $this->interactor->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));
    }
}
