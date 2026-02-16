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
use Support\Domain\Error\DomainRuleViolationError;
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
        $creatorName = 'クリエイター';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $expectedCreator = $this->createCreator($uuid, $creatorName);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (CreatorId $creatorIdArg, CreatorName $creatorNameArg): bool => $creatorIdArg->value === $uuid
                    && $creatorNameArg->value === $creatorName,
            )
            ->andReturn($expectedCreator)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (CreatorName $arg): bool => $arg->value === $creatorName)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForCreate($creatorName);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedCreator, $result->unwrap());
    }

    #[Test]
    public function prepareForCreateDuplicateName(): void
    {
        $creatorName = 'クリエイター';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $expectedCreator = $this->createCreator($uuid, $creatorName);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (CreatorId $creatorIdArg, CreatorName $creatorNameArg): bool => $creatorIdArg->value === $uuid
                    && $creatorNameArg->value === $creatorName,
            )
            ->andReturn($expectedCreator)
            ->once();

        $existingCreator = $this->createCreator('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $creatorName);

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (CreatorName $arg): bool => $arg->value === $creatorName)
            ->andReturn($existingCreator)
            ->once();

        $result = $this->getInstance()->prepareForCreate($creatorName);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(DomainRuleViolationError::class, $error);
        $this->assertSame('すでに使われている名前です "クリエイター"', $error->message);
    }

    #[Test]
    public function prepareForUpdate(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $creatorName = 'クリエイター';

        $expectedCreator = $this->createCreator($uuid, $creatorName);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (CreatorId $creatorIdArg, CreatorName $creatorNameArg): bool => $creatorIdArg->value === $uuid
                    && $creatorNameArg->value === $creatorName,
            )
            ->andReturn($expectedCreator)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (CreatorName $arg): bool => $arg->value === $creatorName)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $creatorName);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedCreator, $result->unwrap());
    }

    #[Test]
    public function prepareForUpdateSameNameSelf(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $creatorName = 'クリエイター';

        $expectedCreator = $this->createCreator($uuid, $creatorName);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (CreatorId $creatorIdArg, CreatorName $creatorNameArg): bool => $creatorIdArg->value === $uuid
                    && $creatorNameArg->value === $creatorName,
            )
            ->andReturn($expectedCreator)
            ->once();

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (CreatorName $arg): bool => $arg->value === $creatorName)
            ->andReturn($expectedCreator)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $creatorName);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedCreator, $result->unwrap());
    }

    #[Test]
    public function prepareForUpdateDuplicateNameOther(): void
    {
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $otherUuid = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $creatorName = 'クリエイター';

        $expectedCreator = $this->createCreator($uuid, $creatorName);

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (CreatorId $creatorIdArg, CreatorName $creatorNameArg): bool => $creatorIdArg->value === $uuid
                    && $creatorNameArg->value === $creatorName,
            )
            ->andReturn($expectedCreator)
            ->once();

        $otherCreator = $this->createCreator($otherUuid, $creatorName);

        $this->repository->shouldReceive('findByName')
            ->withArgs(fn (CreatorName $arg): bool => $arg->value === $creatorName)
            ->andReturn($otherCreator)
            ->once();

        $result = $this->getInstance()->prepareForUpdate($uuid, $creatorName);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(DomainRuleViolationError::class, $error);
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
