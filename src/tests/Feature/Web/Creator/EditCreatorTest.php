<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Creator;

use App\Http\ViewModels\Web\Creator\CreatorView;
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

class EditCreatorTest extends TestCase
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
        $uuid = $this->generateUuid();

        $this->get(route(RouteMap::ShowEditCreatorForm, ['creatorId' => $uuid]))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::ShowLoginForm));
    }

    #[Test]
    public function withNotUuidStyleId(): void
    {
        $this->get(route(RouteMap::ShowEditCreatorForm, ['creatorId' => 'not-uuid-style-id']))
            ->assertStatus(404);
    }

    #[Test]
    public function showEditForm(): void
    {
        $uuid = $this->generateUuid();

        $this->creatorRepository->shouldReceive('find')
            ->with(Mockery::on(fn ($arg) => $arg instanceof CreatorId && $arg->value === $uuid))
            ->andReturn(new Creator(
                new CreatorId($uuid),
                new CreatorName('クリエイター名'),
            ))
            ->once();

        $this->app->bind(
            CreatorRepositoryInterface::class,
            fn (): CreatorRepositoryInterface => $this->creatorRepository,
        );

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::ShowEditCreatorForm, ['creatorId' => $uuid]))
            ->assertStatus(200);

        $data = $response->getOriginalContent()->getData();

        $this->assertInstanceOf(CreatorView::class, $data['creator']);
    }

    #[Test]
    public function canEdit(): void
    {
        $uuid = $this->generateUuid();

        $this->creatorRepository->shouldReceive('update')
            ->with(Mockery::on(
                fn ($arg) => $arg instanceof Creator
                && $arg->creatorId->value === $uuid
                && $arg->creatorName->value === 'クリエイター名'
            ))
            ->andReturn(new CreatorId($uuid))
            ->once();

        $this->app->bind(
            CreatorRepositoryInterface::class,
            fn (): CreatorRepositoryInterface => $this->creatorRepository,
        );

        $this->actingAs($this->user)
            ->post(route(RouteMap::EditCreator), [
                'creator_id' => $uuid,
                'creator_name' => 'クリエイター名',
            ])
            ->assertStatus(302)
            ->assertLocation(route(RouteMap::ListCreators))
            ->assertSessionHas('message', '更新しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->post(route(RouteMap::EditCreator), [
                'creator_id' => '',
                'creator_name' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'creator_id',
                'creator_name',
            ]);
    }
}
