<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Creator;

use App\Models\User;
use Auth\Route\AuthRouteMap;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\Route\CreatorRouteMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteCreatorTest extends TestCase
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
        $this->delete(route(CreatorRouteMap::Delete))
            ->assertStatus(302)
            ->assertRedirect(route(AuthRouteMap::ShowLoginForm));
    }

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('delete')
            ->with(Mockery::on(fn ($arg) => $arg instanceof CreatorId && $arg->value === $uuid))
            ->once();

        $this->actingAs($this->user)
            ->delete(route(CreatorRouteMap::Delete), [
                'creator_id' => $uuid,
            ])
            ->assertStatus(302)
            ->assertLocation(route(CreatorRouteMap::List))
            ->assertSessionHas('message', '削除しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->delete(route(CreatorRouteMap::Delete), [
                'creator_id' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'creator_id',
            ]);
    }
}
