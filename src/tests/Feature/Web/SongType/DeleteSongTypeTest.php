<?php

declare(strict_types=1);

namespace Tests\Feature\Web\SongType;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use Support\Route\RouteMap;
use Tests\TestCase;

class DeleteSongTypeTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private MockInterface&SongTypeRepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->repository = Mockery::mock(SongTypeRepositoryInterface::class);

        $this->app->bind(SongTypeRepositoryInterface::class, fn (): SongTypeRepositoryInterface => $this->repository);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->delete(route(RouteMap::DeleteSongType))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::ShowLoginForm));
    }

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('delete')
            ->with(Mockery::on(fn (SongTypeId $arg) => $arg->value === $uuid))
            ->once();

        $this->actingAs($this->user)
            ->delete(route(RouteMap::DeleteSongType), [
                'song_type_id' => $uuid,
            ])
            ->assertStatus(302)
            ->assertLocation(route(RouteMap::ListSongTypes))
            ->assertSessionHas('message', '削除しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->delete(route(RouteMap::DeleteSongType), [
                'song_type_id' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'song_type_id',
            ]);
    }
}
