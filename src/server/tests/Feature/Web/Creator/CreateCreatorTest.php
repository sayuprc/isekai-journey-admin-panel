<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Creator;

use App\Models\User;
use Auth\Route\AuthRouteMap;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Route\CreatorRouteMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateCreatorTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private CreatorRepositoryInterface&MockInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);

        $this->app->bind(CreatorRepositoryInterface::class, fn (): CreatorRepositoryInterface => $this->repository);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->get(route(CreatorRouteMap::ShowCreateForm))
            ->assertStatus(302)
            ->assertRedirect(route(AuthRouteMap::ShowLoginForm));
    }

    #[Test]
    public function showCreateForm(): void
    {
        $this->actingAs($this->user)
            ->get(route(CreatorRouteMap::ShowCreateForm))
            ->assertStatus(200);
    }

    #[Test]
    public function canCreate(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('findByName')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター'))
            ->andReturnNull()
            ->once();

        $this->repository->shouldReceive('insert')
            ->with(Mockery::on(
                fn (Creator $arg): bool => $arg->creatorId->value === $uuid
                    && $arg->creatorName->value === 'クリエイター'
            ))
            ->once();

        $this->actingAs($this->user)
            ->post(route(CreatorRouteMap::Create), [
                'creator_name' => 'クリエイター',
            ])
            ->assertStatus(302)
            ->assertLocation(route(CreatorRouteMap::List))
            ->assertSessionHas('message', '登録完了しました');
    }

    #[Test]
    public function createFails(): void
    {
        $this->repository->shouldReceive('findByName')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター'))
            ->andReturn(new Creator(
                new CreatorId($this->generateUuid()),
                new CreatorName('クリエイター')
            ))
            ->once();

        $this->actingAs($this->user)
            ->post(route(CreatorRouteMap::Create), [
                'creator_name' => 'クリエイター',
            ])
            ->assertStatus(302)
            ->assertInvalid([
                'message' => 'Creator name already exists: クリエイター',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->post(route(CreatorRouteMap::Create), [
                'creator_name' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'creator_name',
            ]);
    }
}
