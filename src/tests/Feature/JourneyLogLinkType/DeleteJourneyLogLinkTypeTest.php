<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLogLinkType;

use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteJourneyLogLinkTypeTest extends TestCase
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
        $this->delete(route('journey-log-link-types.delete.handle'))
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function canDelete(): void
    {
        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            fn (): JourneyLogLinkTypeRepositoryInterface => $this->getJourneyLogLinkTypeRepository()
        );

        $this->actingAs($this->user)
            ->delete(route('journey-log-link-types.delete.handle'), [
                'journey_log_link_type_id' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            ])
            ->assertStatus(302)
            ->assertLocation(route('journey-log-link-types.index'))
            ->assertSessionHas('message', '削除しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->delete(route('journey-log-link-types.delete.handle'), [
                'journey_log_link_type_id' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'journey_log_link_type_id',
            ]);
    }
}
