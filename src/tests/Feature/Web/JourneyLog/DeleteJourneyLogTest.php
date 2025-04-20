<?php

declare(strict_types=1);

namespace Tests\Feature\Web\JourneyLog;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Route\RouteMap;
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
        $this->delete(route(RouteMap::DeleteJourneyLog))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::ShowLoginForm));
    }

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('delete')
            ->with(Mockery::on(fn (JourneyLogId $arg): bool => $arg->value === $uuid))
            ->once();

        $this->actingAs($this->user)
            ->delete(route(RouteMap::DeleteJourneyLog), [
                'journey_log_id' => $uuid,
            ])
            ->assertStatus(302)
            ->assertLocation(route(RouteMap::ListJourneyLogs))
            ->assertSessionHas('message', '削除しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->delete(route(RouteMap::DeleteJourneyLog), [
                'journey_log_id' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'journey_log_id',
            ]);
    }
}
