<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLogLinkType\Application\Edit;

use JourneyLogLinkType\Application\Edit\EditInteractor;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeName;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Edit\EditInputData;
use JourneyLogLinkType\UseCases\Edit\EditUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class EditInteractorTest extends TestCase
{
    private JourneyLogLinkTypeRepositoryInterface&MockInterface $repository;

    private JourneyLogLinkTypeFactoryInterface&MockInterface $factory;

    private EditInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);
        $this->factory = Mockery::mock(JourneyLogLinkTypeFactoryInterface::class);

        $this->interactor = new EditInteractor($this->repository, $this->factory);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(EditUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function editJourneyLogLinkType(): void
    {
        $this->factory->shouldReceive('reconstitute')
            ->with('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'リンク', 1)
            ->andReturn(new JourneyLogLinkType(
                new JourneyLogLinkTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new JourneyLogLinkTypeName('リンク'),
                new OrderNo(1),
            ))
            ->once();

        $this->repository->shouldReceive('update')
            ->with(Mockery::on(
                fn (JourneyLogLinkType $arg): bool => $arg->journeyLogLinkTypeId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->journeyLogLinkTypeName->value === 'リンク'
                    && $arg->orderNo->value === 1
            ))
            ->once();

        $this->interactor->handle(new EditInputData(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'リンク',
            1
        ));
    }
}
