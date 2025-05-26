<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Edit;

use Creator\Application\Edit\EditInteractor;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorNameDuplicateCheckService;
use Creator\UseCases\Edit\EditInputData;
use Creator\UseCases\Edit\EditUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditInteractorTest extends TestCase
{
    private CreatorRepositoryInterface&MockInterface $repository;

    private CreatorFactoryInterface&MockInterface $factory;

    private CreatorNameDuplicateCheckService&MockInterface $service;

    private EditInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);
        $this->factory = Mockery::mock(CreatorFactoryInterface::class);
        $this->service = Mockery::mock(CreatorNameDuplicateCheckService::class);

        $this->interactor = new EditInteractor($this->repository, $this->factory, $this->service);
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
            ->andReturn(new Creator(
                new CreatorId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new CreatorName('クリエイター')
            ))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター'))
            ->andReturn(false)
            ->once();

        $this->repository->shouldReceive('update')
            ->with(Mockery::on(
                fn (Creator $arg): bool => $arg->creatorId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->creatorName->value === 'クリエイター'
            ))
            ->once();

        $result = $this->interactor->handle(new EditInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'クリエイター'));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function editFailsIfNameAlreadyExists(): void
    {
        $this->factory->shouldReceive('reconstitute')
            ->with('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'クリエイター')
            ->andReturn(new Creator(
                new CreatorId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new CreatorName('クリエイター')
            ))
            ->once();

        $this->service->shouldReceive('exists')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター'))
            ->andReturn(true)
            ->once();

        $result = $this->interactor->handle(new EditInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'クリエイター'));

        $this->assertTrue($result->isErr());
    }
}
