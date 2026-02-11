<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Domain\Services;

use Mockery;
use Mockery\MockInterface;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerIntegrityService;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class PerformerIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private MockInterface&UuidGeneratorInterface $generator;

    private MockInterface&PerformerFactoryInterface $factory;

    private MockInterface&PerformerRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->factory = Mockery::mock(PerformerFactoryInterface::class);
        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $performerName = '共演者';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $currentMaxOrderNo = 100;
        $expectedOrderNo = 110;

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->repository->shouldReceive('getMaxOrderNo')
            ->with()
            ->andReturn($currentMaxOrderNo)
            ->once();

        $expectedPerformer = $this->createPerformer($uuid, $performerName, $expectedOrderNo);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    PerformerId $performerIdArg,
                    PerformerName $performerNameArg,
                    OrderNo $orderNoArg,
                ): bool => $performerIdArg->value === $uuid
                    && $performerNameArg->value === $performerName
                    && $orderNoArg->value === $expectedOrderNo,
            )
            ->andReturn($expectedPerformer)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (PerformerName $arg): bool => $arg->value === $performerName)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForCreate($performerName);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedPerformer, $result->unwrap());
    }

    #[Test]
    public function prepareForCreateDuplicateName(): void
    {
        $performerName = '共演者';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $currentMaxOrderNo = 100;
        $expectedOrderNo = 110;

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->repository->shouldReceive('getMaxOrderNo')
            ->with()
            ->andReturn($currentMaxOrderNo)
            ->once();

        $expectedPerformer = $this->createPerformer($uuid, $performerName, $expectedOrderNo);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    PerformerId $performerIdArg,
                    PerformerName $performerNameArg,
                    OrderNo $orderNoArg,
                ): bool => $performerIdArg->value === $uuid
                    && $performerNameArg->value === $performerName
                    && $orderNoArg->value === $expectedOrderNo,
            )
            ->andReturn($expectedPerformer)
            ->once();

        $existingPerformer = $this->createPerformer('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $performerName, $expectedOrderNo);

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (PerformerName $arg): bool => $arg->value === $performerName)
            ->andReturn($existingPerformer)
            ->once();

        $result = $this->getInstance()->prepareForCreate($performerName);

        $this->assertTrue($result->isErr());
        $this->assertSame('すでに使われている名前です "共演者"', $result->unwrapErr());
    }

    #[Test]
    public function prepareForUpdate(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $performerName = '共演者';
        $orderNo = 1;

        $expectedPerformer = $this->createPerformer($uuid, $performerName, $orderNo);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    PerformerId $performerIdArg,
                    PerformerName $performerNameArg,
                    OrderNo $orderNoArg,
                ): bool => $performerIdArg->value === $uuid
                    && $performerNameArg->value === $performerName
                    && $orderNoArg->value === $orderNo,
            )
            ->andReturn($expectedPerformer)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (PerformerName $arg): bool => $arg->value === $performerName)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $performerName, $orderNo);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedPerformer, $result->unwrap());
    }

    #[Test]
    public function prepareForUpdateSameNameSelf(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $performerName = '共演者';
        $orderNo = 1;

        $expectedPerformer = $this->createPerformer($uuid, $performerName, $orderNo);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    PerformerId $performerIdArg,
                    PerformerName $performerNameArg,
                    OrderNo $orderNoArg,
                ): bool => $performerIdArg->value === $uuid
                    && $performerNameArg->value === $performerName
                    && $orderNoArg->value === $orderNo,
            )
            ->andReturn($expectedPerformer)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (PerformerName $arg): bool => $arg->value === $performerName)
            ->andReturn($expectedPerformer)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $performerName, $orderNo);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedPerformer, $result->unwrap());
    }

    #[Test]
    public function prepareForUpdateDuplicateNameOther(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $otherUuid = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $performerName = '共演者';
        $orderNo = 1;

        $expectedPerformer = $this->createPerformer($uuid, $performerName, $orderNo);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    PerformerId $performerIdArg,
                    PerformerName $performerNameArg,
                    OrderNo $orderNoArg,
                ): bool => $performerIdArg->value === $uuid
                    && $performerNameArg->value === $performerName
                    && $orderNoArg->value === $orderNo,
            )
            ->andReturn($expectedPerformer)
            ->once();

        $otherPerformer = $this->createPerformer($otherUuid, $performerName, $orderNo);

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (PerformerName $arg): bool => $arg->value === $performerName)
            ->andReturn($otherPerformer)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $performerName, $orderNo);

        $this->assertTrue($result->isErr());
        $this->assertSame('すでに使われている名前です "共演者"', $result->unwrapErr());
    }

    private function getInstance(): PerformerIntegrityService
    {
        return new PerformerIntegrityService(
            $this->generator,
            $this->factory,
            $this->repository,
        );
    }
}
