<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLog\Application\Delete;

use JourneyLog\Application\Delete\DeleteInteractor;
use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Delete\DeleteInputData;
use JourneyLog\UseCases\Delete\DeleteUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteInteractorTest extends TestCase
{
    private JourneyLogRepositoryInterface&MockInterface $repository;

    private DeleteInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(JourneyLogRepositoryInterface::class);

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
            ->with(Mockery::on(fn (JourneyLogId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->once();

        $this->interactor->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));
    }
}
