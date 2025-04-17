<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Creator;

use App\Models\User;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Route\RouteMap;
use Tests\TestCase;

class ListCreatorTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private CreatorRepositoryInterface&MockInterface $creatorRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->creatorRepository = Mockery::mock(CreatorRepositoryInterface::class);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->get(route(RouteMap::ListCreators))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::ShowLoginForm));
    }

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->creatorRepository->shouldReceive('listCreators')
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

        $this->app->bind(CreatorRepositoryInterface::class, fn (): CreatorRepositoryInterface => $this->creatorRepository);

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::ListCreators))
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
        $this->creatorRepository->shouldReceive('listCreators')
            ->andReturn([])
            ->once();

        $this->app->bind(CreatorRepositoryInterface::class, fn (): CreatorRepositoryInterface => $this->creatorRepository);

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::ListCreators))
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
