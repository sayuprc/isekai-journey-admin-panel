<?php

declare(strict_types=1);

namespace Tests\Feature\Web\SongType;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use Support\Domain\ValueObjects\OrderNo;
use Support\Route\RouteMap;
use Tests\TestCase;

class CreateSongTypeTest extends TestCase
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
        $this->get(route(RouteMap::ShowCreateSongTypeForm))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::ShowLoginForm));
    }

    #[Test]
    public function showCreateForm(): void
    {
        $this->actingAs($this->user)
            ->get(route(RouteMap::ShowCreateSongTypeForm))
            ->assertStatus(200);
    }

    #[Test]
    public function canCreate(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('findByName')
            ->with(Mockery::on(fn (SongTypeName $arg): bool => $arg->value === '楽曲種別'))
            ->andReturnNull()
            ->once();

        $this->repository->shouldReceive('insert')
            ->with(Mockery::on(
                fn (SongType $arg): bool => $arg->songTypeId->value === $uuid
                    && $arg->songTypeName->value === '楽曲種別'
                    && $arg->orderNo->value === 1
            ))
            ->once();

        $this->actingAs($this->user)
            ->post(route(RouteMap::CreateSongType), [
                'song_type_name' => '楽曲種別',
                'order_no' => 1,
            ])
            ->assertStatus(302)
            ->assertLocation(route(RouteMap::ListSongTypes))
            ->assertSessionHas('message', '登録完了しました');
    }

    #[Test]
    public function createFails(): void
    {
        $this->repository->shouldReceive('findByName')
            ->with(Mockery::on(fn (SongTypeName $arg): bool => $arg->value === '楽曲種別'))
            ->andReturn(new SongType(
                new SongTypeId($this->generateUuid()),
                new SongTypeName('楽曲種別'),
                new OrderNo(1),
            ))
            ->once();

        $this->actingAs($this->user)
            ->post(route(RouteMap::CreateSongType), [
                'song_type_name' => '楽曲種別',
                'order_no' => 1,
            ])
            ->assertStatus(302)
            ->assertInvalid([
                'message' => 'Song type already exists: 楽曲種別',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->post(route(RouteMap::CreateSongType), [
                'song_type_name' => '',
                'order_no' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'song_type_name',
                'order_no',
            ]);
    }
}
