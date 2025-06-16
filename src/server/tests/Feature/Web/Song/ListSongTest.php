<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Song;

use App\Models\User;
use Auth\Route\AuthRouteMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Description;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Title;
use Song\Route\SongRouteMap;
use SongType\Domain\Models\SongTypeId;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class ListSongTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private MockInterface&SongRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => $this->generateUuid(),
        ]);

        $this->repository = Mockery::mock(SongRepositoryInterface::class);

        $this->app->bind(SongRepositoryInterface::class, fn (): SongRepositoryInterface => $this->repository);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->get(route(SongRouteMap::List))
            ->assertStatus(302)
            ->assertRedirect(route(AuthRouteMap::ShowLoginForm));
    }

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('all')
            ->andReturn([
                new Song(
                    new SongId($uuid),
                    new Title('楽曲1'),
                    new Description('説明1'),
                    new SongTypeId($uuid),
                    new OrderNo(1),
                    [],
                    [],
                    [],
                    [],
                ),
                new Song(
                    new SongId($uuid),
                    new Title('楽曲2'),
                    new Description('説明2'),
                    new SongTypeId($uuid),
                    new OrderNo(2),
                    [],
                    [],
                    [],
                    [],
                ),
            ])
            ->once();

        $response = $this->actingAs($this->user)
            ->get(route(SongRouteMap::List))
            ->assertStatus(200)
            ->assertViewIs('songs.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            'タイトル',
            '説明',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(2, $data['songs']);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $response = $this->actingAs($this->user)
            ->get(route(SongRouteMap::List))
            ->assertStatus(200)
            ->assertViewIs('songs.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            'タイトル',
            '説明',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(0, $data['songs']);
    }
}
