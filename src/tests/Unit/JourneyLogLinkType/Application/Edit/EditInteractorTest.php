<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLogLinkType\Application\Edit;

use JourneyLogLinkType\Application\Edit\EditInteractor;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Edit\EditRequest;
use JourneyLogLinkType\UseCases\Edit\EditUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditInteractorTest extends TestCase
{
    private JourneyLogLinkTypeRepositoryInterface&MockInterface $journeyLogLinkTypeRepository;

    private EditInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journeyLogLinkTypeRepository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);
        $this->interactor = new EditInteractor($this->journeyLogLinkTypeRepository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(EditUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function editJourneyLogLinkType(): void
    {
        $this->journeyLogLinkTypeRepository->shouldReceive('update')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof JourneyLogLinkType
                    && $arg->journeyLogLinkTypeId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->journeyLogLinkTypeName->value === 'リンク'
                    && $arg->orderNo->value === 1
            ))
            ->once();

        $this->interactor->handle(new EditRequest(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'リンク',
            1
        ));
    }
}
