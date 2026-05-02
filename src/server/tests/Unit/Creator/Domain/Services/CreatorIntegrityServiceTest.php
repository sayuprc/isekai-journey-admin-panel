<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Domain\Services;

use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorIntegrityService;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class CreatorIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private MockInterface&UuidGeneratorInterface $generator;

    private CreatorRepositoryInterface&MockInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
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

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (CreatorName $arg): bool => $arg->value === $name)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForCreate($name);

        $this->assertTrue($result->isOk());
        $this->assertEquals($expectedCreator, $result->unwrap());
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

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (CreatorName $arg): bool => $arg->value === $name)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $name, $orderNo);

        $this->assertTrue($result->isOk());
        $this->assertEquals($expectedCreator, $result->unwrap());
    }

    #[Test]
    public function prepareForUpdateSameNameSelf(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'クリエイター';
        $orderNo = 1;

        $expectedCreator = $this->createCreator($uuid, $name, $orderNo);

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (CreatorName $arg): bool => $arg->value === $name)
            ->andReturn($expectedCreator)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $name, $orderNo);

        $this->assertTrue($result->isOk());
        $this->assertEquals($expectedCreator, $result->unwrap());
    }

    #[Test]
    public function prepareForUpdateDuplicateNameOther(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $otherUuid = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $name = 'クリエイター';
        $orderNo = 1;

        $expectedCreator = $this->createCreator($uuid, $name, $orderNo);

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
            $this->repository,
        );
    }
}
