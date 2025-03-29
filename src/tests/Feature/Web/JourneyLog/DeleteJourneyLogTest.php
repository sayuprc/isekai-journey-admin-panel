<?php

declare(strict_types=1);

namespace Tests\Feature\Web\JourneyLog;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use JourneyLog\Domain\Entities\JourneyLogId;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shared\Route\RouteMap;
use Tests\TestCase;

class DeleteJourneyLogTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private JourneyLogRepositoryInterface&LegacyMockInterface $journeyLogRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->journeyLogRepository = Mockery::mock(JourneyLogRepositoryInterface::class);
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

        $this->journeyLogRepository->shouldReceive('deleteJourneyLog')
            ->with(Mockery::on(function (JourneyLogId $arg) use ($uuid): bool {
                return $arg->value === $uuid;
            }))
            ->once();

        $this->app->bind(
            JourneyLogRepositoryInterface::class,
            fn (): JourneyLogRepositoryInterface => $this->journeyLogRepository,
        );

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
