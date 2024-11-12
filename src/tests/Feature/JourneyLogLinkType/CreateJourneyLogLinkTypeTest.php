<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLogLinkType;

use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateJourneyLogLinkTypeTest extends TestCase
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
        $this->get(route('journey-log-link-types.create.index'))
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function showCreateForm(): void
    {
        $this->actingAs($this->user)
            ->get(route('journey-log-link-types.create.index'))
            ->assertStatus(200);
    }

    #[Test]
    public function canCreate(): void
    {
        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            fn (): JourneyLogLinkTypeRepositoryInterface => $this->getJourneyLogLinkTypeRepository()
        );

        $this->actingAs($this->user)
            ->post(route('journey-log-link-types.create.handle'), [
                'journey_log_link_type_name' => '軌跡リンク種別A',
                'order_no' => '1',
            ])
            ->assertStatus(302)
            ->assertLocation(route('journey-log-link-types.index'))
            ->assertSessionHas('message', '登録完了しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            fn (): JourneyLogLinkTypeRepositoryInterface => $this->getJourneyLogLinkTypeRepository()
        );

        $this->actingAs($this->user)
            ->post(route('journey-log-link-types.create.handle'), [
                'journey_log_link_type_name' => '',
                'order_no' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'journey_log_link_type_name',
                'order_no',
            ]);
    }
}
