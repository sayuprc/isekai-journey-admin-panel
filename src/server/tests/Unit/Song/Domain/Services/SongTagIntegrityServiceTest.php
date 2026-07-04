<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Services;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Tag\SongTagName;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Domain\Services\SongTagIntegrityService;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SongTagIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private MockInterface&UuidGeneratorInterface $generator;

    private MockInterface&SongTagRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->repository = Mockery::mock(SongTagRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $name = 'テストタグA';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $maxOrderNo = 20;

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->repository->shouldReceive('getMaxOrderNo')
            ->with()
            ->andReturn($maxOrderNo)
            ->once();

        $expectedTag = $this->createSongTag($uuid, $name, $maxOrderNo + 10);

        $this->repository->shouldReceive('findByName')
            ->withArgs(static fn (SongTagName $arg): bool => $arg->value === $name)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForCreate($name);

        $this->assertTrue($result->isOk());
        $this->assertEquals($expectedTag, $result->unwrap());
    }

    #[Test]
    public function prepareForCreateDuplicateName(): void
    {
        $name = 'テストタグA';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $maxOrderNo = 20;

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->repository->shouldReceive('getMaxOrderNo')
            ->with()
            ->andReturn($maxOrderNo)
            ->once();

        $expectedTag = $this->createSongTag($uuid, $name, $maxOrderNo + 10);

        $this->repository->shouldReceive('findByName')
            ->withArgs(static fn (SongTagName $arg): bool => $arg->value === $name)
            ->andReturn($this->createSongTag('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $name, 10))
            ->once();

        $result = $this->getInstance()->prepareForCreate($name);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessRuleViolationError::class, $error);
        $this->assertSame('すでに使われている名前です "テストタグA"', $error->message);
    }

    #[Test]
    public function prepareForUpdate(): void
    {
        $songTagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'テストタグA';
        $orderNo = 20;

        $expectedTag = $this->createSongTag($songTagId, $name, $orderNo);

        $this->repository->shouldReceive('findByName')
            ->withArgs(static fn (SongTagName $arg): bool => $arg->value === $name)
            ->andReturn($expectedTag)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($songTagId, $name, $orderNo);

        $this->assertTrue($result->isOk());
        $this->assertEquals($expectedTag, $result->unwrap());
    }

    #[Test]
    public function prepareForUpdateDuplicateName(): void
    {
        $songTagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'テストタグA';
        $orderNo = 20;

        $expectedTag = $this->createSongTag($songTagId, $name, $orderNo);

        $this->repository->shouldReceive('findByName')
            ->withArgs(static fn (SongTagName $arg): bool => $arg->value === $name)
            ->andReturn($this->createSongTag('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $name, 10))
            ->once();

        $result = $this->getInstance()->prepareForUpdate($songTagId, $name, $orderNo);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessRuleViolationError::class, $error);
        $this->assertSame('すでに使われている名前です "テストタグA"', $error->message);
    }

    private function getInstance(): SongTagIntegrityService
    {
        return new SongTagIntegrityService(
            $this->generator,
            $this->repository,
        );
    }
}
