<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Create;

use Creator\Application\Create\CreateInteractor;
use Creator\Domain\Models\Creator;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\Create\CreateRequest;
use Creator\UseCases\Create\CreateUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Application\Uuid\DummyUuidGenerator;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    private CreatorRepositoryInterface&MockInterface $creatorRepository;

    private CreateInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creatorRepository = $this->mock(CreatorRepositoryInterface::class);
        $this->interactor = new CreateInteractor($this->creatorRepository, $this->app->make(DummyUuidGenerator::class));
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(CreateUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function create(): void
    {
        $this->creatorRepository->shouldReceive('createCreator')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof Creator
                    && $arg->creatorId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->creatorName->value === 'クリエイター'
            ))
            ->once();

        $this->interactor->handle(new CreateRequest('クリエイター'));
    }
}
