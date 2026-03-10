<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Domain\Services;

use Override;
use Mockery;
use Mockery\MockInterface;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerIntegrityService;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class PerformerIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private MockInterface&UuidGeneratorInterface $generator;

    private MockInterface&PerformerFactoryInterface $factory;

    private MockInterface&PerformerRepositoryInterface $repository;

    #[Override]
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
        $name = '共演者';
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

        $expectedPerformer = $this->createPerformer($uuid, $name, $expectedOrderNo);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    PerformerId $performerIdArg,
                    PerformerName $nameArg,
                    OrderNo $orderNoArg,
                ): bool => $performerIdArg->value === $uuid
                    && $nameArg->value === $name
                    && $orderNoArg->value === $expectedOrderNo,
            )
            ->andReturn($expectedPerformer)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (PerformerName $arg): bool => $arg->value === $name)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForCreate($name);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedPerformer, $result->unwrap());
    }

    #[Test]
    public function prepareForCreateDuplicateName(): void
    {
        $name = '共演者';
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

        $expectedPerformer = $this->createPerformer($uuid, $name, $expectedOrderNo);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    PerformerId $performerIdArg,
                    PerformerName $nameArg,
                    OrderNo $orderNoArg,
                ): bool => $performerIdArg->value === $uuid
                    && $nameArg->value === $name
                    && $orderNoArg->value === $expectedOrderNo,
            )
            ->andReturn($expectedPerformer)
            ->once();

        $existingPerformer = $this->createPerformer('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $name, $expectedOrderNo);

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (PerformerName $arg): bool => $arg->value === $name)
            ->andReturn($existingPerformer)
            ->once();

        $result = $this->getInstance()->prepareForCreate($name);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessRuleViolationError::class, $error);
        $this->assertSame('すでに使われている名前です "共演者"', $error->message);
    }

    #[Test]
    public function prepareForUpdate(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = '共演者';
        $orderNo = 1;

        $expectedPerformer = $this->createPerformer($uuid, $name, $orderNo);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    PerformerId $performerIdArg,
                    PerformerName $nameArg,
                    OrderNo $orderNoArg,
                ): bool => $performerIdArg->value === $uuid
                    && $nameArg->value === $name
                    && $orderNoArg->value === $orderNo,
            )
            ->andReturn($expectedPerformer)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (PerformerName $arg): bool => $arg->value === $name)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $name, $orderNo);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedPerformer, $result->unwrap());
    }

    #[Test]
    public function prepareForUpdateSameNameSelf(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = '共演者';
        $orderNo = 1;

        $expectedPerformer = $this->createPerformer($uuid, $name, $orderNo);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    PerformerId $performerIdArg,
                    PerformerName $nameArg,
                    OrderNo $orderNoArg,
                ): bool => $performerIdArg->value === $uuid
                    && $nameArg->value === $name
                    && $orderNoArg->value === $orderNo,
            )
            ->andReturn($expectedPerformer)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (PerformerName $arg): bool => $arg->value === $name)
            ->andReturn($expectedPerformer)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $name, $orderNo);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedPerformer, $result->unwrap());
    }

    #[Test]
    public function prepareForUpdateDuplicateNameOther(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $otherUuid = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $name = '共演者';
        $orderNo = 1;

        $expectedPerformer = $this->createPerformer($uuid, $name, $orderNo);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    PerformerId $performerIdArg,
                    PerformerName $nameArg,
                    OrderNo $orderNoArg,
                ): bool => $performerIdArg->value === $uuid
                    && $nameArg->value === $name
                    && $orderNoArg->value === $orderNo,
            )
            ->andReturn($expectedPerformer)
            ->once();

        $otherPerformer = $this->createPerformer($otherUuid, $name, $orderNo);

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (PerformerName $arg): bool => $arg->value === $name)
            ->andReturn($otherPerformer)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $name, $orderNo);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessRuleViolationError::class, $error);
        $this->assertSame('すでに使われている名前です "共演者"', $error->message);
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
