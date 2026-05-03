<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\UseCase\Delete;

use Mockery;
use Mockery\MockInterface;
use Override;
use Performer\Application\UseCase\Delete\DeleteInputData;
use Performer\Application\UseCase\Delete\DeleteUseCase;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteUseCaseTest extends TestCase
{
    private MockInterface&PerformerRepositoryInterface $repository;

    #[Override]
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

        $result = $this->getInstance()->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertTrue($result->isOk());
    }

    private function getInstance(): DeleteUseCase
    {
        return new DeleteUseCase(
            $this->privilegedContext(),
            $this->repository,
        );
    }
}
