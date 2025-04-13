<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLogLinkType\Application\Get;

use JourneyLogLinkType\Application\Get\GetInteractor;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeName;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Get\GetRequest;
use JourneyLogLinkType\UseCases\Get\GetResponse;
use JourneyLogLinkType\UseCases\Get\GetUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    private JourneyLogLinkTypeRepositoryInterface&MockInterface $journeyLogLinkTypeRepository;

    private GetInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journeyLogLinkTypeRepository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);
        $this->interactor = new GetInteractor($this->journeyLogLinkTypeRepository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(GetUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function getJourneyLogLinkType(): void
    {
        $this->journeyLogLinkTypeRepository->shouldReceive('getJourneyLogLinkType')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof JourneyLogLinkTypeId
                    && $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
            ))
            ->andReturnUsing(
                fn () => new JourneyLogLinkType(
                    new JourneyLogLinkTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                    new JourneyLogLinkTypeName('リンク'),
                    new OrderNo(1)
                )
            )
            ->once();

        $response = $this->interactor->handle(new GetRequest('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertInstanceOf(GetResponse::class, $response);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $response->journeyLogLinkType->journeyLogLinkTypeId->value);
        $this->assertSame('リンク', $response->journeyLogLinkType->journeyLogLinkTypeName->value);
        $this->assertSame(1, $response->journeyLogLinkType->orderNo->value);
    }
}
