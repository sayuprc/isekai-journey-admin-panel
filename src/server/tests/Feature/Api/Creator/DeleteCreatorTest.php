<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Route\CreatorRouteMap;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteCreatorTest extends TestCase
{
    // TODO モックやめる
    private CreatorRepositoryInterface&MockInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);

        $this->app->bind(CreatorRepositoryInterface::class, fn (): CreatorRepositoryInterface => $this->repository);
    }

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('delete')
            ->with(Mockery::on(fn (CreatorId $arg) => $arg->value === $uuid))
            ->once();

        $this->delete(route(CreatorRouteMap::Delete, $uuid))
            ->assertStatus(204);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }
}
