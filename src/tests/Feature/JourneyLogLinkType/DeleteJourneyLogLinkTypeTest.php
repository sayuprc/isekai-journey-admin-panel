<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLogLinkType;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use App\Models\User;
use App\Shared\Route\RouteMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteJourneyLogLinkTypeTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private JourneyLogLinkTypeRepositoryInterface&LegacyMockInterface $journeyLogLinkTypeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->journeyLogLinkTypeRepository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->delete(route(RouteMap::DELETE_JOURNEY_LOG_LINK_TYPE))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::SHOW_LOGIN_FORM));
    }

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->journeyLogLinkTypeRepository->shouldReceive('deleteJourneyLogLinkType')
            ->with(Mockery::on(function (JourneyLogLinkTypeId $arg) use ($uuid): bool {
                return $arg->value === $uuid;
            }))
            ->once();

        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            fn (): JourneyLogLinkTypeRepositoryInterface => $this->journeyLogLinkTypeRepository,
        );

        $this->actingAs($this->user)
            ->delete(route(RouteMap::DELETE_JOURNEY_LOG_LINK_TYPE), [
                'journey_log_link_type_id' => $uuid,
            ])
            ->assertStatus(302)
            ->assertLocation(route(RouteMap::LIST_JOURNEY_LOG_LINK_TYPE))
            ->assertSessionHas('message', '削除しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->delete(route(RouteMap::DELETE_JOURNEY_LOG_LINK_TYPE), [
                'journey_log_link_type_id' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'journey_log_link_type_id',
            ]);
    }
}
