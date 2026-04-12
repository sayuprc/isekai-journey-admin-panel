<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Services;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagFactoryInterface;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Domain\Services\SongTagIntegrityService;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class SongTagIntegrityServiceTest extends TestCase
{
    private MockInterface&UuidGeneratorInterface $generator;

    private MockInterface&SongTagFactoryInterface $factory;

    private MockInterface&SongTagRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->factory = Mockery::mock(SongTagFactoryInterface::class);
        $this->repository = Mockery::mock(SongTagRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $name = 'ロック';
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

        $expectedTag = new SongTag(
            SongTagId::reconstruct($uuid),
            SongTagName::reconstruct($name),
            OrderNo::reconstruct($maxOrderNo + 10),
        );

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (SongTagId $idArg, SongTagName $nameArg, OrderNo $orderNoArg): bool => $idArg->value === $uuid
                    && $nameArg->value === $name
                    && $orderNoArg->value === $maxOrderNo + 10,
            )
            ->andReturn($expectedTag)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (SongTagName $arg): bool => $arg->value === $name)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForCreate($name);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedTag, $result->unwrap());
    }

    #[Test]
    public function prepareForCreateWithExistingMaxOrderNo(): void
    {
        $name = 'ロック';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $maxOrderNo = 30;

        $this->generator->shouldReceive('generate')
            ->andReturn($uuid)
            ->once();

        $this->repository->shouldReceive('getMaxOrderNo')
            ->andReturn($maxOrderNo)
            ->once();

        $expectedTag = new SongTag(
            SongTagId::reconstruct($uuid),
            SongTagName::reconstruct($name),
            OrderNo::reconstruct($maxOrderNo + 10),
        );

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (SongTagId $idArg, SongTagName $nameArg, OrderNo $orderNoArg): bool => $orderNoArg->value === $maxOrderNo + 10,
            )
            ->andReturn($expectedTag)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForCreate($name);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedTag, $result->unwrap());
    }

    #[Test]
    public function prepareForCreateDuplicateName(): void
    {
        $name = 'ロック';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $maxOrderNo = 0;

        $this->generator->shouldReceive('generate')
            ->andReturn($uuid)
            ->once();

        $this->repository->shouldReceive('getMaxOrderNo')
            ->andReturn($maxOrderNo)
            ->once();

        $expectedTag = new SongTag(
            SongTagId::reconstruct($uuid),
            SongTagName::reconstruct($name),
            OrderNo::reconstruct($maxOrderNo + 10),
        );

        $this->factory->shouldReceive('create')
            ->andReturn($expectedTag)
            ->once();

        $existingTag = new SongTag(
            SongTagId::reconstruct('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
            SongTagName::reconstruct($name),
            OrderNo::reconstruct(10),
        );

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (SongTagName $arg): bool => $arg->value === $name)
            ->andReturn($existingTag)
            ->once();

        $result = $this->getInstance()->prepareForCreate($name);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessRuleViolationError::class, $error);
        $this->assertSame('すでに使われているタグ名です "ロック"', $error->message);
    }

    #[Test]
    public function prepareForUpdate(): void
    {
        $songTagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'ロック';
        $orderNo = 20;

        $expectedTag = new SongTag(
            SongTagId::reconstruct($songTagId),
            SongTagName::reconstruct($name),
            OrderNo::reconstruct($orderNo),
        );

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (SongTagId $idArg, SongTagName $nameArg, OrderNo $orderNoArg): bool => $idArg->value === $songTagId
                    && $nameArg->value === $name
                    && $orderNoArg->value === $orderNo,
            )
            ->andReturn($expectedTag)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (SongTagName $arg): bool => $arg->value === $name)
            ->andReturn($expectedTag)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($songTagId, $name, $orderNo);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedTag, $result->unwrap());
    }

    #[Test]
    public function prepareForUpdateDuplicateName(): void
    {
        $songTagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'ロック';
        $orderNo = 20;

        $expectedTag = new SongTag(
            SongTagId::reconstruct($songTagId),
            SongTagName::reconstruct($name),
            OrderNo::reconstruct($orderNo),
        );

        $this->factory->shouldReceive('create')
            ->andReturn($expectedTag)
            ->once();

        $existingTag = new SongTag(
            SongTagId::reconstruct('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
            SongTagName::reconstruct($name),
            OrderNo::reconstruct(10),
        );

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (SongTagName $arg): bool => $arg->value === $name)
            ->andReturn($existingTag)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($songTagId, $name, $orderNo);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessRuleViolationError::class, $error);
        $this->assertSame('すでに使われているタグ名です "ロック"', $error->message);
    }

    private function getInstance(): SongTagIntegrityService
    {
        return new SongTagIntegrityService(
            $this->generator,
            $this->factory,
            $this->repository,
        );
    }
}
