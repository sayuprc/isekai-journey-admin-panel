<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Creator;

use App\Models\User;
use Creator\Domain\Models\Creator;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Route\RouteMap;
use Tests\TestCase;

class CreateCreatorTest extends TestCase
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
        $this->get(route(RouteMap::ShowCreateCreatorForm))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::ShowLoginForm));
    }

    #[Test]
    public function showCreateForm(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::ShowCreateCreatorForm))
            ->assertStatus(200);
    }

    #[Test]
    public function canCreate(): void
    {
        $uuid = $this->generateUuid();

        $this->creatorRepository->shouldReceive('createCreator')
            ->with(Mockery::on(
                fn (Creator $arg): bool => $arg->creatorId->value === $uuid
                    && $arg->creatorName->value === 'クリエイター'
            ))
            ->once();

        $this->app->bind(CreatorRepositoryInterface::class, fn (): CreatorRepositoryInterface => $this->creatorRepository);

        $this->actingAs($this->user)
            ->post(route(RouteMap::CreateCreator), [
                'creator_name' => 'クリエイター',
            ])
            ->assertStatus(302)
            ->assertLocation(route(RouteMap::ListCreators))
            ->assertSessionHas('message', '登録完了しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->post(route(RouteMap::CreateCreator), [
                'creator_name' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'creator_name',
            ]);
    }
}
