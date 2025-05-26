<?php

declare(strict_types=1);

namespace Tests\Feature\Web\JourneyLogLinkType;

use App\Models\User;
use Auth\Route\AuthRouteMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\Route\JourneyLogLinkTypeRouteMap;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteJourneyLogLinkTypeTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private JourneyLogLinkTypeRepositoryInterface&MockInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->repository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);

        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            fn (): JourneyLogLinkTypeRepositoryInterface => $this->repository
        );
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->delete(route(JourneyLogLinkTypeRouteMap::Delete))
            ->assertStatus(302)
            ->assertRedirect(route(AuthRouteMap::ShowLoginForm));
    }

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('delete')
            ->with(Mockery::on(fn (JourneyLogLinkTypeId $arg): bool => $arg->value === $uuid))
            ->once();

        $this->actingAs($this->user)
            ->delete(route(JourneyLogLinkTypeRouteMap::Delete), [
                'journey_log_link_type_id' => $uuid,
            ])
            ->assertStatus(302)
            ->assertLocation(route(JourneyLogLinkTypeRouteMap::List))
            ->assertSessionHas('message', '削除しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->delete(route(JourneyLogLinkTypeRouteMap::Delete), [
                'journey_log_link_type_id' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'journey_log_link_type_id',
            ]);
    }
}
