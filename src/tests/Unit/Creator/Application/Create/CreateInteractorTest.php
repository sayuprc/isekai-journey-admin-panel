<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Create;

use Creator\Application\Create\CreateInteractor;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\Create\CreateRequest;
use Creator\UseCases\Create\CreateUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    private CreatorRepositoryInterface&MockInterface $creatorRepository;

    private CreatorFactoryInterface&MockInterface $factory;

    private CreateInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creatorRepository = Mockery::mock(CreatorRepositoryInterface::class);
        $this->factory = Mockery::mock(CreatorFactoryInterface::class);
        $this->interactor = new CreateInteractor($this->creatorRepository, $this->factory);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(CreateUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function create(): void
    {
        $this->factory->shouldReceive('create')
            ->with('クリエイター')
            ->andReturnUsing(fn (): Creator => new Creator(
                new CreatorId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new CreatorName('クリエイター')
            ))
            ->once();

        $this->creatorRepository->shouldReceive('insert')
            ->with(Mockery::on(
                fn (Creator $arg): bool => $arg->creatorId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->creatorName->value === 'クリエイター'
            ))
            ->once();

        $this->interactor->handle(new CreateRequest('クリエイター'));
    }
}
