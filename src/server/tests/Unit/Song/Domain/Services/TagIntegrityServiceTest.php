<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Services;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\TagFactoryInterface;
use Song\Domain\Models\TagId;
use Song\Domain\Models\TagName;
use Song\Domain\Models\TagRepositoryInterface;
use Song\Domain\Services\TagIntegrityService;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class TagIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private MockInterface&UuidGeneratorInterface $generator;

    private MockInterface&TagFactoryInterface $factory;

    private MockInterface&TagRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->factory = Mockery::mock(TagFactoryInterface::class);
        $this->repository = Mockery::mock(TagRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $name = 'ライブ定番';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $maxOrderNo = 0;

        $this->generator->shouldReceive('generate')->with()->andReturn($uuid)->once();
        $this->repository->shouldReceive('getMaxOrderNo')->with()->andReturn($maxOrderNo)->once();

        $expectedTag = $this->createSongTag($uuid, $name, $maxOrderNo + 10);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (TagId $tagIdArg, TagName $nameArg, OrderNo $orderNoArg): bool => $tagIdArg->value === $uuid
                    && $nameArg->value === $name
                    && $orderNoArg->value === $maxOrderNo + 10,
            )
            ->andReturn($expectedTag)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (TagName $arg): bool => $arg->value === $name)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForCreate($name);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedTag, $result->unwrap());
    }

    #[Test]
    public function prepareForCreateDuplicateName(): void
    {
        $name = 'ライブ定番';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $maxOrderNo = 0;

        $this->generator->shouldReceive('generate')->with()->andReturn($uuid)->once();
        $this->repository->shouldReceive('getMaxOrderNo')->with()->andReturn($maxOrderNo)->once();

        $expectedTag = $this->createSongTag($uuid, $name, $maxOrderNo + 10);

        $this->factory->shouldReceive('create')
            ->andReturn($expectedTag)
            ->once();

        $existingTag = $this->createSongTag('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $name, $maxOrderNo + 10);

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (TagName $arg): bool => $arg->value === $name)
            ->andReturn($existingTag)
            ->once();

        $result = $this->getInstance()->prepareForCreate($name);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessRuleViolationError::class, $error);
        $this->assertSame('すでに使われている名前です "ライブ定番"', $error->message);
    }

    #[Test]
    public function prepareForUpdateDuplicateNameOther(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $otherUuid = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $name = 'ライブ定番';
        $orderNo = 1;

        $expectedTag = $this->createSongTag($uuid, $name, $orderNo);

        $this->factory->shouldReceive('create')
            ->andReturn($expectedTag)
            ->once();

        $otherTag = $this->createSongTag($otherUuid, $name, $orderNo);

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (TagName $arg): bool => $arg->value === $name)
            ->andReturn($otherTag)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $name, $orderNo);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessRuleViolationError::class, $error);
        $this->assertSame('すでに使われている名前です "ライブ定番"', $error->message);
    }

    private function getInstance(): TagIntegrityService
    {
        return new TagIntegrityService(
            $this->generator,
            $this->factory,
            $this->repository,
        );
    }
}
