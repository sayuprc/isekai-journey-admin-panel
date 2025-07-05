<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Route\CreatorRouteMap;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateCreatorTest extends TestCase
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
    public function canCreate(): void
    {
        $this->repository->shouldReceive('findByName')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'ヰ世界情緒'))
            ->andReturnNull()
            ->once();

        $this->repository->shouldReceive('insert')
            ->with(Mockery::on(fn (Creator $arg): bool => $arg->creatorName->value === 'ヰ世界情緒'))
            ->once();

        $this->postJson(route(CreatorRouteMap::Create), [
            'creatorName' => 'ヰ世界情緒',
        ])->assertStatus(200)
            ->assertJson([
                'creator' => [
                    'creatorName' => 'ヰ世界情緒',
                ],
            ]);
    }

    #[Test]
    public function createFails(): void
    {
        $this->markTestSkipped('実装する');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('実装する');
    }
}
