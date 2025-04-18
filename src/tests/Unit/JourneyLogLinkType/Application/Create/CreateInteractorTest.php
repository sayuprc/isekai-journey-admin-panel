<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLogLinkType\Application\Create;

use JourneyLogLinkType\Application\Create\CreateInteractor;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Create\CreateRequest;
use JourneyLogLinkType\UseCases\Create\CreateUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Application\Uuid\DummyUuidGenerator;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    private JourneyLogLinkTypeRepositoryInterface&MockInterface $journeyLogLinkTypeRepository;

    private CreateInteractor $interactor;

    protected function setup(): void
    {
        parent::setup();

        $this->journeyLogLinkTypeRepository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);
        $this->interactor = new CreateInteractor($this->journeyLogLinkTypeRepository, $this->app->make(DummyUuidGenerator::class));
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(CreateUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function createJourneyLogLinkType(): void
    {
        $this->journeyLogLinkTypeRepository->shouldReceive('insert')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof JourneyLogLinkType
                    && $arg->journeyLogLinkTypeId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->journeyLogLinkTypeName->value === 'リンク'
                    && $arg->orderNo->value === 1
            ))
            ->once();

        $this->interactor->handle(new CreateRequest(
            'リンク',
            1
        ));
    }
}
