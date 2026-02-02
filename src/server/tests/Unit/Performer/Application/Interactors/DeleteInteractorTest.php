<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\Interactors;

use Mockery;
use Mockery\MockInterface;
use Performer\Application\Interactors\DeleteInteractor;
use Performer\Application\UseCase\Delete\DeleteInputData;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteInteractorTest extends TestCase
{
    private MockInterface&PerformerRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);
    }

    #[Test]
    public function deletePerformer(): void
    {
        $this->repository->shouldReceive('delete')
            ->withArgs(fn (PerformerId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $this->getInstance()->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));
    }

    private function getInstance(): DeleteInteractor
    {
        return new DeleteInteractor($this->repository);
    }
}
