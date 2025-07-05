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

class UpdateCreatorTest extends TestCase
{
    // TODO モックをやめる
    private CreatorRepositoryInterface&MockInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);

        $this->app->bind(CreatorRepositoryInterface::class, fn (): CreatorRepositoryInterface => $this->repository);
    }

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('findByName')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'ヰ世界情緒'))
            ->andReturnNull()
            ->once();

        $this->repository->shouldReceive('update')
            ->with(
                Mockery::on(
                    fn (Creator $arg): bool => $arg->creatorId->value === $uuid
                        && $arg->creatorName->value === 'ヰ世界情緒'
                )
            )
            ->andReturn(new CreatorId($uuid))
            ->once();

        $this->putJson(route(CreatorRouteMap::Update, $uuid), [
            'creatorName' => 'ヰ世界情緒',
        ])->assertStatus(200)
            ->assertJson([
                'creator' => [
                    'creatorId' => $uuid,
                    'creatorName' => 'ヰ世界情緒',
                ],
            ]);
    }

    #[Test]
    public function updateFails(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }
}
