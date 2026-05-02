<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\UseCase;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\UseCase\Delete\DeleteInputData;
use Song\Application\UseCase\Delete\DeleteUseCase;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Tests\TestCase;

class DeleteUseCaseTest extends TestCase
{
    private MockInterface&SongRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongRepositoryInterface::class);
    }

    #[Test]
    public function deleteSong(): void
    {
        $this->repository->shouldReceive('delete')
            ->withArgs(fn (SongId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
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
