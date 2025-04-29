<?php

declare(strict_types=1);

namespace Feature\Web\SongType;

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

class ListSongTypeTest extends TestCase
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
        $this->get(route(RouteMap::ListSongTypes))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::ShowLoginForm));
    }

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('all')
            ->andReturn([
                new SongType(
                    new SongTypeId($uuid),
                    new SongTypeName('楽曲種別1'),
                    new OrderNo(1),
                ),
                new SongType(
                    new SongTypeId($uuid),
                    new SongTypeName('楽曲種別2'),
                    new OrderNo(2),
                ),
            ])
            ->once();

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::ListSongTypes))
            ->assertStatus(200)
            ->assertViewIs('songTypes.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '名前',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(2, $data['songTypes']);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::ListSongTypes))
            ->assertStatus(200)
            ->assertViewIs('songTypes.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '名前',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(0, $data['songTypes']);
    }
}
