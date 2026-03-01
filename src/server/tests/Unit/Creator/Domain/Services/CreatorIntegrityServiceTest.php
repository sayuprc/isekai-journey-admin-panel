<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Domain\Services;

use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorIntegrityService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class CreatorIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private MockInterface&UuidGeneratorInterface $generator;

    private CreatorFactoryInterface&MockInterface $factory;

    private CreatorRepositoryInterface&MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->factory = Mockery::mock(CreatorFactoryInterface::class);
        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $name = 'クリエイター';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $maxOrderNo = 0;

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->repository->shouldReceive('getMaxOrderNo')
            ->with()
            ->andReturn($maxOrderNo)
            ->once();

        $expectedCreator = $this->createCreator($uuid, $name, $maxOrderNo + 10);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (CreatorId $creatorIdArg, CreatorName $nameArg, OrderNo $orderNoArg): bool => $creatorIdArg->value === $uuid
                    && $nameArg->value === $name
                    && $orderNoArg->value === $maxOrderNo + 10,
            )
            ->andReturn($expectedCreator)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (CreatorName $arg): bool => $arg->value === $name)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForCreate($name);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedCreator, $result->unwrap());
    }

    #[Test]
    public function prepareForCreateDuplicateName(): void
    {
        $name = 'クリエイター';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $maxOrderNo = 0;

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->repository->shouldReceive('getMaxOrderNo')
            ->with()
            ->andReturn($maxOrderNo)
            ->once();

        $expectedCreator = $this->createCreator($uuid, $name, $maxOrderNo + 10);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (CreatorId $creatorIdArg, CreatorName $nameArg, OrderNo $orderNoArg): bool => $creatorIdArg->value === $uuid
                    && $nameArg->value === $name
                    && $orderNoArg->value === $maxOrderNo + 10,
            )
            ->andReturn($expectedCreator)
            ->once();

        $existingCreator = $this->createCreator('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $name, $maxOrderNo + 10);

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (CreatorName $arg): bool => $arg->value === $name)
            ->andReturn($existingCreator)
            ->once();

        $result = $this->getInstance()->prepareForCreate($name);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessRuleViolationError::class, $error);
        $this->assertSame('すでに使われている名前です "クリエイター"', $error->message);
    }

    #[Test]
    public function prepareForUpdate(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'クリエイター';
        $orderNo = 1;

        $expectedCreator = $this->createCreator($uuid, $name, $orderNo);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (CreatorId $creatorIdArg, CreatorName $nameArg, OrderNo $orderNoArg): bool => $creatorIdArg->value === $uuid
                    && $nameArg->value === $name
                    && $orderNoArg->value === $orderNo,
            )
            ->andReturn($expectedCreator)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (CreatorName $arg): bool => $arg->value === $name)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $name, $orderNo);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedCreator, $result->unwrap());
    }

    #[Test]
    public function prepareForUpdateSameNameSelf(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'クリエイター';
        $orderNo = 1;

        $expectedCreator = $this->createCreator($uuid, $name, $orderNo);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (CreatorId $creatorIdArg, CreatorName $nameArg, OrderNo $orderNoArg): bool => $creatorIdArg->value === $uuid
                    && $nameArg->value === $name
                    && $orderNoArg->value === $orderNo,
            )
            ->andReturn($expectedCreator)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (CreatorName $arg): bool => $arg->value === $name)
            ->andReturn($expectedCreator)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $name, $orderNo);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedCreator, $result->unwrap());
    }

    #[Test]
    public function prepareForUpdateDuplicateNameOther(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $otherUuid = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $name = 'クリエイター';
        $orderNo = 1;

        $expectedCreator = $this->createCreator($uuid, $name, $orderNo);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (CreatorId $creatorIdArg, CreatorName $nameArg, OrderNo $orderNoArg): bool => $creatorIdArg->value === $uuid
                    && $nameArg->value === $name
                    && $orderNoArg->value === $orderNo,
            )
            ->andReturn($expectedCreator)
            ->once();

        $otherCreator = $this->createCreator($otherUuid, $name, $orderNo);

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (CreatorName $arg): bool => $arg->value === $name)
            ->andReturn($otherCreator)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $name, $orderNo);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessRuleViolationError::class, $error);
        $this->assertSame('すでに使われている名前です "クリエイター"', $error->message);
    }

    private function getInstance(): CreatorIntegrityService
    {
        return new CreatorIntegrityService(
            $this->generator,
            $this->factory,
            $this->repository,
        );
    }
}
