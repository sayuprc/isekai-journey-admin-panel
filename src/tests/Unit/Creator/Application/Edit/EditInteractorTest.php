<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Edit;

use Creator\Application\Edit\EditInteractor;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
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

    private CreatorFactoryInterface&MockInterface $factory;

    private EditInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creatorRepository = Mockery::mock(CreatorRepositoryInterface::class);
        $this->factory = Mockery::mock(CreatorFactoryInterface::class);
        $this->interactor = new EditInteractor($this->creatorRepository, $this->factory);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(EditUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function editCreator(): void
    {
        $this->factory->shouldReceive('reconstitute')
            ->with('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'クリエイター')
            ->andReturnUsing(fn (): Creator => new Creator(
                new CreatorId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new CreatorName('クリエイター')
            ))
            ->once();

        $this->creatorRepository->shouldReceive('update')
            ->with(Mockery::on(
                fn (Creator $arg): bool => $arg->creatorId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->creatorName->value === 'クリエイター'
            ))
            ->once();

        $this->interactor->handle(new EditRequest(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'クリエイター',
        ));
    }
}
