<?php

declare(strict_types=1);

namespace Tests\Feature\Song;

use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shared\Route\RouteMap;
use Song\Domain\Entities\Description;
use Song\Domain\Entities\OrderNo;
use Song\Domain\Entities\ReleasedOn;
use Song\Domain\Entities\Song;
use Song\Domain\Entities\SongId;
use Song\Domain\Entities\SongLink;
use Song\Domain\Entities\SongLinkId;
use Song\Domain\Entities\SongLinkName;
use Song\Domain\Entities\SongTypeId;
use Song\Domain\Entities\Title;
use Song\Domain\Entities\Url;
use Song\Domain\Repositories\SongRepositoryInterface;
use Tests\TestCase;

class ListSongTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private LegacyMockInterface&SongRepositoryInterface $songRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => $this->generateUuid(),
        ]);

        $this->songRepository = Mockery::mock(SongRepositoryInterface::class);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->get(route(RouteMap::LIST_SONGS))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::SHOW_LOGIN_FORM));
    }

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->songRepository->shouldReceive('listSongs')
            ->andReturn([
                new Song(
                    new SongId($uuid),
                    new Title('楽曲1'),
                    new Description('説明1'),
                    new ReleasedOn(new DateTimeImmutable()),
                    new SongTypeId($uuid),
                    new OrderNo(1),
                    []
                ),
                new Song(
                    new SongId($uuid),
                    new Title('楽曲2'),
                    new Description('説明2'),
                    new ReleasedOn(new DateTimeImmutable()),
                    new SongTypeId($uuid),
                    new OrderNo(2),
                    [
                        new SongLink(
                            new SongLinkId($uuid),
                            new SongLinkName('楽曲リンク名'),
                            new Url('https://'),
                            new OrderNo(1),
                        ),
                    ]
                ),
            ])
            ->once();

        $this->app->bind(SongRepositoryInterface::class, fn (): SongRepositoryInterface => $this->songRepository);

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::LIST_SONGS))
            ->assertStatus(200)
            ->assertViewIs('songs.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            'タイトル',
            '説明',
            'リリース日',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(2, $data['songs']);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->songRepository->shouldReceive('listSongs')
            ->andReturn([])
            ->once();

        $this->app->bind(SongRepositoryInterface::class, fn (): SongRepositoryInterface => $this->songRepository);

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::LIST_SONGS))
            ->assertStatus(200)
            ->assertViewIs('songs.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            'タイトル',
            '説明',
            'リリース日',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(0, $data['songs']);
    }
}
