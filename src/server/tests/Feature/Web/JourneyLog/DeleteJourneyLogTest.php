<?php

declare(strict_types=1);

namespace Tests\Feature\Web\JourneyLog;

use App\Models\User;
use Auth\Route\AuthRouteMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Models\JourneyLogRepositoryInterface;
use JourneyLog\Route\JourneyLogRouteMap;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteJourneyLogTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private JourneyLogRepositoryInterface&MockInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->repository = Mockery::mock(JourneyLogRepositoryInterface::class);

        $this->app->bind(JourneyLogRepositoryInterface::class, fn (): JourneyLogRepositoryInterface => $this->repository);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->delete(route(JourneyLogRouteMap::Delete))
            ->assertStatus(302)
            ->assertRedirect(route(AuthRouteMap::ShowLoginForm));
    }

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('delete')
            ->with(Mockery::on(fn (JourneyLogId $arg): bool => $arg->value === $uuid))
            ->once();

        $this->actingAs($this->user)
            ->delete(route(JourneyLogRouteMap::Delete), [
                'journey_log_id' => $uuid,
            ])
            ->assertStatus(302)
            ->assertLocation(route(JourneyLogRouteMap::List))
            ->assertSessionHas('message', '削除しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->delete(route(JourneyLogRouteMap::Delete), [
                'journey_log_id' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'journey_log_id',
            ]);
    }
}
