<?php

declare(strict_types=1);

namespace Tests\Feature\Web\SongType;

use App\Http\ViewModels\Web\SongType\SongTypeView;
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

class EditSongTypeTest extends TestCase
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
        $uuid = $this->generateUuid();

        $this->get(route(RouteMap::ShowEditSongTypeForm, ['songTypeId' => $uuid]))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::ShowLoginForm));
    }

    #[Test]
    public function withNotUuidStyleId(): void
    {
        $this->get(route(RouteMap::ShowEditSongTypeForm, ['songTypeId' => 'not-uuid-style-id']))
            ->assertStatus(404);
    }

    #[Test]
    public function showEditForm(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (SongTypeId $arg): bool => $arg->value === $uuid))
            ->andReturn(new SongType(
                new SongTypeId($uuid),
                new SongTypeName('楽曲種別'),
                new OrderNo(1),
            ))
            ->once();

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::ShowEditSongTypeForm, ['songTypeId' => $uuid]))
            ->assertStatus(200);

        $data = $response->getOriginalContent()->getData();

        $this->assertInstanceOf(SongTypeView::class, $data['songType']);
    }

    #[Test]
    public function failureShowEditForm(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (SongTypeId $arg): bool => $arg->value === $uuid))
            ->andReturnNull()
            ->once();

        $this->actingAs($this->user)
            ->get(route(RouteMap::ShowEditSongTypeForm, ['songTypeId' => $uuid]))
            ->assertStatus(302)
            ->assertLocation(route(RouteMap::ListSongTypes))
            ->assertInvalid(['message' => "Song type not found: {$uuid}"]);
    }

    #[Test]
    public function canEdit(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('findByName')
            ->with(Mockery::on(fn (SongTypeName $arg): bool => $arg->value === '楽曲種別'))
            ->andReturnNull()
            ->once();

        $this->repository->shouldReceive('update')
            ->with(Mockery::on(
                fn (SongType $arg): bool => $arg->songTypeId->value === $uuid
                    && $arg->songTypeName->value === '楽曲種別'
                    && $arg->orderNo->value === 1
            ))
            ->andReturn(new SongTypeId($uuid))
            ->once();

        $this->actingAs($this->user)
            ->post(route(RouteMap::EditSongType), [
                'song_type_id' => $uuid,
                'song_type_name' => '楽曲種別',
                'order_no' => 1,
            ])
            ->assertStatus(302)
            ->assertLocation(route(RouteMap::ListSongTypes))
            ->assertSessionHas('message', '更新しました');
    }

    #[Test]
    public function editFails(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('findByName')
            ->with(Mockery::on(fn (SongTypeName $arg): bool => $arg->value === '楽曲種別'))
            ->andReturn(new SongType(
                new SongTypeId('FFFFFFFF-FFFF-FFFF-FFFF-FFFFFFFFFFFF'),
                new SongTypeName('楽曲種別'),
                new OrderNo(1),
            ))
            ->once();

        $this->actingAs($this->user)
            ->post(route(RouteMap::EditSongType), [
                'song_type_id' => $uuid,
                'song_type_name' => '楽曲種別',
                'order_no' => 1,
            ])
            ->assertStatus(302)
            ->assertInvalid([
                'message' => "Song type already exists: {$uuid}",
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->post(route(RouteMap::EditSongType), [
                'song_type_id' => '',
                'song_type_name' => '',
                'order_no' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'song_type_id',
                'song_type_name',
                'order_no',
            ]);
    }
}
