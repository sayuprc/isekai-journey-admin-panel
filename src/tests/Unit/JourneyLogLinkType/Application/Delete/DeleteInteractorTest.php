<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLogLinkType\Application\Delete;

use JourneyLogLinkType\Application\Delete\DeleteInteractor;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Delete\DeleteRequest;
use JourneyLogLinkType\UseCases\Delete\DeleteUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteInteractorTest extends TestCase
{
    private JourneyLogLinkTypeRepositoryInterface&MockInterface $journeyLogLinkTypeRepository;

    private DeleteInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journeyLogLinkTypeRepository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);
        $this->interactor = new DeleteInteractor($this->journeyLogLinkTypeRepository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(DeleteUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function deleteJourneyLogLinkType(): void
    {
        $this->journeyLogLinkTypeRepository->shouldReceive('delete')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof JourneyLogLinkTypeId
                    && $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
            ))
            ->once();

        $this->interactor->handle(new DeleteRequest('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));
    }
}
