<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Song;

use App\Models\User;
use Auth\Route\AuthRouteMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Archives\ArchiveType;
use Song\Domain\Models\Archives\NonLink\NonLinkArchive;
use Song\Domain\Models\Archives\Twitter\TwitterArchive;
use Song\Domain\Models\Archives\YouTube\YouTubeArchive;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Route\SongRouteMap;
use Tests\TestCase;

class CreateSongTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private MockInterface&SongRepositoryInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->repository = Mockery::mock(SongRepositoryInterface::class);

        $this->app->bind(SongRepositoryInterface::class, fn (): SongRepositoryInterface => $this->repository);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->get(route(SongRouteMap::ShowCreateForm))
            ->assertStatus(302)
            ->assertRedirect(route(AuthRouteMap::ShowLoginForm));
    }

    #[Test]
    public function showCreateForm(): void
    {
        $this->actingAs($this->user)
            ->get(route(SongRouteMap::ShowCreateForm))
            ->assertStatus(200);
    }

    #[Test]
    public function canCreate(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('insert')
            ->with(
                Mockery::on(
                    fn (Song $arg): bool => $arg->title->value === 'タイトル'
                        && $arg->description->value === '説明'
                        && $arg->songTypeId->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                        && $arg->orderNo->value === 1
                        && count($arg->lyricists) === 1
                        && $arg->lyricists[0]->creatorId->value === $uuid
                        && $arg->lyricists[0]->orderNo->value === 1
                        && count($arg->composers) === 1
                        && $arg->composers[0]->creatorId->value === $uuid
                        && $arg->composers[0]->orderNo->value === 1
                        && count($arg->arrangers) === 1
                        && $arg->arrangers[0]->creatorId->value === $uuid
                        && $arg->arrangers[0]->orderNo->value === 1
                        && count($arg->archives) === 3
                        && $arg->archives[0] instanceof YouTubeArchive
                        && $arg->archives[0]->archiveName->value === 'YouTube'
                        && $arg->archives[0]->videoUrl->value === 'https://example.com/video'
                        && $arg->archives[0]->thumbnailUrl->value === 'https://example.com/thumbnail.jpg'
                        && $arg->archives[0]->archivedOn->value->format('Y-m-d') === '2025-05-10'
                        && $arg->archives[0]->orderNo->value === 1
                        && $arg->archives[1] instanceof TwitterArchive
                        && $arg->archives[1]->archiveName->value === 'Twitter'
                        && $arg->archives[1]->postUrl->value === 'https://example.com/post'
                        && $arg->archives[1]->archivedOn->value->format('Y-m-d') === '2025-05-09'
                        && $arg->archives[1]->orderNo->value === 2
                        && $arg->archives[2] instanceof NonLinkArchive
                        && $arg->archives[2]->archivedOn->value->format('Y-m-d') === '2025-05-08'
                        && $arg->archives[2]->orderNo->value === 3
                )
            )
            ->once();

        $this->actingAs($this->user)
            ->post(route(SongRouteMap::Create), [
                'title' => 'タイトル',
                'description' => '説明',
                'song_type_id' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
                'order_no' => 1,
                'lyricists' => [
                    [
                        'creator_id' => $uuid,
                        'order_no' => 1,
                    ],
                ],
                'composers' => [
                    [
                        'creator_id' => $uuid,
                        'order_no' => 1,
                    ],
                ],
                'arrangers' => [
                    [
                        'creator_id' => $uuid,
                        'order_no' => 1,
                    ],
                ],
                'archives' => [
                    [
                        'archive_type' => ArchiveType::YouTube->value,
                        'archive_name' => 'YouTube',
                        'video_url' => 'https://example.com/video',
                        'thumbnail_url' => 'https://example.com/thumbnail.jpg',
                        'archived_on' => '2025-05-10',
                        'order_no' => 1,
                    ],
                    [
                        'archive_type' => ArchiveType::Twitter->value,
                        'archive_name' => 'Twitter',
                        'post_url' => 'https://example.com/post',
                        'archived_on' => '2025-05-09',
                        'order_no' => 2,
                    ],
                    [
                        'archive_type' => ArchiveType::NonLink->value,
                        'archived_on' => '2025-05-08',
                        'order_no' => 3,
                    ],
                ],
            ])
            ->assertStatus(302)
            ->assertLocation(route(SongRouteMap::List))
            ->assertSessionHas('message', '登録完了しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->post(route(SongRouteMap::Create), [
                'title' => '',
                'description' => '',
                'song_type_id' => '',
                'order_no' => '',
                'lyricists' => [
                    [
                        'creator_id' => '',
                        'order_no' => '',
                    ],
                ],
                'composers' => [
                    [
                        'creator_id' => '',
                        'order_no' => '',
                    ],
                ],
                'arrangers' => [
                    [
                        'creator_id' => '',
                        'order_no' => '',
                    ],
                ],
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'title',
                'description',
                'song_type_id',
                'order_no',
                'lyricists.0.creator_id',
                'lyricists.0.order_no',
                'composers.0.creator_id',
                'composers.0.order_no',
                'arrangers.0.creator_id',
                'arrangers.0.order_no',
            ]);
    }
}
