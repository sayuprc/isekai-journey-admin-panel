<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Creator;

use App\Http\ViewModels\Web\Creator\CreatorView;
use App\Models\User;
use Auth\Route\AuthRouteMap;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\Route\CreatorRouteMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditCreatorTest extends TestCase
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
        $uuid = $this->generateUuid();

        $this->get(route(CreatorRouteMap::ShowEditForm, ['creatorId' => $uuid]))
            ->assertStatus(302)
            ->assertRedirect(route(AuthRouteMap::ShowLoginForm));
    }

    #[Test]
    public function withNotUuidStyleId(): void
    {
        $this->get(route(CreatorRouteMap::ShowEditForm, ['creatorId' => 'not-uuid-style-id']))
            ->assertStatus(404);
    }

    #[Test]
    public function showEditForm(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (CreatorId $arg): bool => $arg->value === $uuid))
            ->andReturn(new Creator(
                new CreatorId($uuid),
                new CreatorName('クリエイター名'),
            ))
            ->once();

        $response = $this->actingAs($this->user)
            ->get(route(CreatorRouteMap::ShowEditForm, ['creatorId' => $uuid]))
            ->assertStatus(200);

        $data = $response->getOriginalContent()->getData();

        $this->assertInstanceOf(CreatorView::class, $data['creator']);
    }

    #[Test]
    public function failureShowEditForm(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (CreatorId $arg): bool => $arg->value === $uuid))
            ->andReturnNull()
            ->once();

        $this->actingAs($this->user)
            ->get(route(CreatorRouteMap::ShowEditForm, ['creatorId' => $uuid]))
            ->assertStatus(302)
            ->assertLocation(route(CreatorRouteMap::List))
            ->assertInvalid(['message' => "Creator not found: {$uuid}"]);
    }

    #[Test]
    public function canEdit(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('findByName')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター名'))
            ->andReturnNull()
            ->once();

        $this->repository->shouldReceive('update')
            ->with(Mockery::on(
                fn (Creator $arg): bool => $arg->creatorId->value === $uuid
                    && $arg->creatorName->value === 'クリエイター名'
            ))
            ->andReturn(new CreatorId($uuid))
            ->once();

        $this->actingAs($this->user)
            ->post(route(CreatorRouteMap::Edit), [
                'creator_id' => $uuid,
                'creator_name' => 'クリエイター名',
            ])
            ->assertStatus(302)
            ->assertLocation(route(CreatorRouteMap::List))
            ->assertSessionHas('message', '更新しました');
    }

    #[Test]
    public function editFails(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('findByName')
            ->with(Mockery::on(fn (CreatorName $arg): bool => $arg->value === 'クリエイター名'))
            ->andReturn(new Creator(
                new CreatorId($uuid),
                new CreatorName('クリエイター名')
            ))
            ->once();

        $this->actingAs($this->user)
            ->post(route(CreatorRouteMap::Edit), [
                'creator_id' => $uuid,
                'creator_name' => 'クリエイター名',
            ])
            ->assertStatus(302)
            ->assertInvalid([
                'message' => 'Creator name already exists: クリエイター名',
            ]);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->post(route(CreatorRouteMap::Edit), [
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
