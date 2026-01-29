<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Domain\Services;

use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorNameDuplicateCheckService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class CreatorNameDuplicateCheckServiceTest extends TestCase
{
    use EntityFactory;

    private CreatorRepositoryInterface&MockInterface $repository;

    private CreatorNameDuplicateCheckService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);

        $this->service = new CreatorNameDuplicateCheckService($this->repository);
    }

    #[Test]
    public function isExists(): void
    {
        $this->repository->shouldReceive('findByName')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター'))
            ->andReturn($this->createCreator($this->generateUuid(), 'クリエイター'))
            ->once();

        $this->assertTrue($this->service->exists(CreatorName::create('クリエイター')->unwrap()));
    }

    #[Test]
    public function isNonexistent(): void
    {
        $this->repository->shouldReceive('findByName')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター'))
            ->andReturn(null)
            ->once();

        $this->assertFalse($this->service->exists(CreatorName::create('クリエイター')->unwrap()));
    }
}
