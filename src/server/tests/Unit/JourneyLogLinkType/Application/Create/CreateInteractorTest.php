<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLogLinkType\Application\Create;

use JourneyLogLinkType\Application\Create\CreateInteractor;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeName;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Create\CreateInputData;
use JourneyLogLinkType\UseCases\Create\CreateUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    private JourneyLogLinkTypeRepositoryInterface&MockInterface $repository;

    private JourneyLogLinkTypeFactoryInterface&MockInterface $factory;

    private CreateInteractor $interactor;

    protected function setup(): void
    {
        parent::setup();

        $this->repository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);
        $this->factory = Mockery::mock(JourneyLogLinkTypeFactoryInterface::class);

        $this->interactor = new CreateInteractor($this->repository, $this->factory);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(CreateUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function createJourneyLogLinkType(): void
    {
        $this->factory->shouldReceive('create')
            ->with('リンク', 1)
            ->andReturn(new JourneyLogLinkType(
                new JourneyLogLinkTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new JourneyLogLinkTypeName('リンク'),
                new OrderNo(1),
            ))
            ->once();

        $this->repository->shouldReceive('insert')
            ->with(Mockery::on(
                fn (JourneyLogLinkType $arg): bool => $arg->journeyLogLinkTypeId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->journeyLogLinkTypeName->value === 'リンク'
                    && $arg->orderNo->value === 1
            ))
            ->once();

        $this->interactor->handle(new CreateInputData(
            'リンク',
            1
        ));
    }
}
