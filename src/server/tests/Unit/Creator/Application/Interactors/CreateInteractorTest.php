<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Interactors;

use Creator\Application\Interactors\CreateInteractor;
use Creator\Application\UseCase\Create\CreateInputData;
use Creator\Application\UseCase\Create\CreateUseCaseInterface;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorNameDuplicateCheckService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    private CreatorRepositoryInterface&MockInterface $repository;

    private CreatorFactoryInterface&MockInterface $factory;

    private CreatorNameDuplicateCheckService&MockInterface $service;

    private CreateInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);
        $this->factory = Mockery::mock(CreatorFactoryInterface::class);
        $this->service = Mockery::mock(CreatorNameDuplicateCheckService::class);

        $this->interactor = new CreateInteractor($this->repository, $this->factory, $this->service);
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
            ->andReturn(new Creator(
                new CreatorId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new CreatorName('クリエイター')
            ))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター'))
            ->andreturn(false)
            ->once();

        $this->repository->shouldReceive('insert')
            ->with(Mockery::on(
                fn (Creator $arg): bool => $arg->creatorId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->creatorName->value === 'クリエイター'
            ))
            ->once();

        $result = $this->interactor->handle(new CreateInputData('クリエイター'));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function createFailsIfNameAlreadyExists(): void
    {
        $this->factory->shouldReceive('create')
            ->with('クリエイター')
            ->andReturn(new Creator(
                new CreatorId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new CreatorName('クリエイター')
            ))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター'))
            ->andreturn(true)
            ->once();

        $result = $this->interactor->handle(new CreateInputData('クリエイター'));

        $this->assertTrue($result->isErr());
    }
}
