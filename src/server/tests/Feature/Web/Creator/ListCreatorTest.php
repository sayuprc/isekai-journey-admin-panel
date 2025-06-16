<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Creator;

use App\Models\User;
use Auth\Route\AuthRouteMap;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Route\CreatorRouteMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListCreatorTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private CreatorRepositoryInterface&MockInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);

        $this->app->bind(CreatorRepositoryInterface::class, fn (): CreatorRepositoryInterface => $this->repository);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->get(route(CreatorRouteMap::List))
            ->assertStatus(302)
            ->assertRedirect(route(AuthRouteMap::ShowLoginForm));
    }

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('all')
            ->andReturn([
                new Creator(
                    new CreatorId($uuid),
                    new CreatorName('クリエイターA')
                ),
                new Creator(
                    new CreatorId($uuid),
                    new CreatorName('クリエイターB')
                ),
            ])
            ->once();

        $response = $this->actingAs($this->user)
            ->get(route(CreatorRouteMap::List))
            ->assertStatus(200)
            ->assertViewIs('creators.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            'クリエイター名',
            '',
        ], $data['heads']);

        $this->assertSame([
            'order' => [[0, 'asc']],
        ], $data['config']);

        $this->assertCount(2, $data['creators']);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $response = $this->actingAs($this->user)
            ->get(route(CreatorRouteMap::List))
            ->assertStatus(200)
            ->assertViewIs('creators.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            'クリエイター名',
            '',
        ], $data['heads']);

        $this->assertSame([
            'order' => [[0, 'asc']],
        ], $data['config']);

        $this->assertCount(0, $data['creators']);
    }
}
