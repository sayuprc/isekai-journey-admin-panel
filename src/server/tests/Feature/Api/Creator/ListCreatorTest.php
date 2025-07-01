<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Creator;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Route\CreatorRouteMap;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListCreatorTest extends TestCase
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
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('all')
            ->andReturn([
                new Creator(
                    new CreatorId($uuid),
                    new CreatorName('ヰ世界情緒'),
                ),
            ])
            ->once();

        $this->get(route(CreatorRouteMap::List))
            ->assertStatus(200)
            ->assertJson([
                'creators' => [
                    [
                        'creatorId' => $uuid,
                        'creatorName' => 'ヰ世界情緒',
                    ],
                ],
            ]);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $this->get(route(CreatorRouteMap::List))
            ->assertStatus(200)
            ->assertJson(['creators' => []]);
    }
}
