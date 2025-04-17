<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Edit;

use Creator\Application\Edit\EditInteractor;
use Creator\Domain\Models\Creator;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\Edit\EditRequest;
use Creator\UseCases\Edit\EditUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditInteractorTest extends TestCase
{
    private CreatorRepositoryInterface&MockInterface $creatorRepository;

    private EditInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creatorRepository = Mockery::mock(CreatorRepositoryInterface::class);
        $this->interactor = new EditInteractor($this->creatorRepository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(EditUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function editCreator(): void
    {
        $this->creatorRepository->shouldReceive('editCreator')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof Creator
                    && $arg->creatorId->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                    && $arg->creatorName->value === 'クリエイター'
            ))
            ->once();

        $this->interactor->handle(new EditRequest(
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            'クリエイター',
        ));
    }
}
