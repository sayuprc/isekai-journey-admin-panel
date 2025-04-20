<?php

declare(strict_types=1);

namespace Tests\Unit\JourneyLogLinkType\Application\Get;

use JourneyLogLinkType\Application\Get\GetInteractor;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeName;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Get\GetRequest;
use JourneyLogLinkType\UseCases\Get\GetUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Support\ResultType\Result;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    private JourneyLogLinkTypeRepositoryInterface&MockInterface $repository;

    private GetInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);

        $this->interactor = new GetInteractor($this->repository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(GetUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function getJourneyLogLinkType(): void
    {
        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (JourneyLogLinkTypeId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->andReturn(new JourneyLogLinkType(
                new JourneyLogLinkTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                new JourneyLogLinkTypeName('リンク'),
                new OrderNo(1)
            ))
            ->once();

        $result = $this->interactor->handle(new GetRequest('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $response->journeyLogLinkType->journeyLogLinkTypeId->value);
        $this->assertSame('リンク', $response->journeyLogLinkType->journeyLogLinkTypeName->value);
        $this->assertSame(1, $response->journeyLogLinkType->orderNo->value);
    }

    #[Test]
    public function failureGetJourneyLogLinkType(): void
    {
        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (JourneyLogLinkTypeId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'))
            ->andReturnNull()
            ->once();

        $result = $this->interactor->handle(new GetRequest('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOk());

        $this->assertSame('JourneyLogLinkType not found: AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $result->unwrapErr());
    }
}
