<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLog;

use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteJourneyLogTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->delete(route('journey-logs.delete.handle'))
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function canDelete(): void
    {
        $this->app->bind(JourneyLogRepositoryInterface::class, function () {
            return new class () implements JourneyLogRepositoryInterface {
                public function listJourneyLogs(): array
                {
                }

                public function createJourneyLog(JourneyLog $journeyLog): void
                {
                }

                public function getJourneyLog(JourneyLogId $journeyLogId): JourneyLog
                {
                }

                public function editJourneyLog(JourneyLog $journeyLog): JourneyLogId
                {
                }

                public function deleteJourneyLog(JourneyLogId $journeyLogId): void
                {
                }
            };
        });

        $this->actingAs($this->user)
            ->delete(route('journey-logs.delete.handle'), [
                'journey_log_id' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            ])
            ->assertStatus(302)
            ->assertLocation(route('journey-logs.index'))
            ->assertSessionHas('message');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->app->bind(JourneyLogRepositoryInterface::class, function () {
            return new class () implements JourneyLogRepositoryInterface {
                public function listJourneyLogs(): array
                {
                }

                public function createJourneyLog(JourneyLog $journeyLog): void
                {
                }

                public function getJourneyLog(JourneyLogId $journeyLogId): JourneyLog
                {
                }

                public function editJourneyLog(JourneyLog $journeyLog): JourneyLogId
                {
                }

                public function deleteJourneyLog(JourneyLogId $journeyLogId): void
                {
                }
            };
        });

        $this->actingAs($this->user)
            ->delete(route('journey-logs.delete.handle'), [
                'journey_log_id' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'journey_log_id',
            ]);
    }
}
