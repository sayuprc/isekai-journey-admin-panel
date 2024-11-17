<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLogLinkType;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use App\Models\User;
use App\Shared\Route\RouteMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateJourneyLogLinkTypeTest extends TestCase
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
        $this->get(route(RouteMap::SHOW_CREATE_JOURNEY_LOG_LINK_TYPE_FORM))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::SHOW_LOGIN_FORM));
    }

    #[Test]
    public function showCreateForm(): void
    {
        $this->actingAs($this->user)
            ->get(route(RouteMap::SHOW_CREATE_JOURNEY_LOG_LINK_TYPE_FORM))
            ->assertStatus(200);
    }

    #[Test]
    public function canCreate(): void
    {
        $uuid = $this->generateUuid();

        $this->journeyLogLinkTypeRepository->shouldReceive('createJourneyLogLinkType')
            ->with(Mockery::on(function (JourneyLogLinkType $arg) use ($uuid): bool {
                return $arg->journeyLogLinkTypeId->value === $uuid
                    && $arg->journeyLogLinkTypeName->value === '軌跡リンク種別A'
                    && $arg->orderNo->value === 1;
            }))
            ->once();

        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            fn (): JourneyLogLinkTypeRepositoryInterface => $this->journeyLogLinkTypeRepository,
        );

        $this->actingAs($this->user)
            ->post(route(RouteMap::CREATE_JOURNEY_LOG_LINK_TYPE), [
                'journey_log_link_type_name' => '軌跡リンク種別A',
                'order_no' => '1',
            ])
            ->assertStatus(302)
            ->assertLocation(route(RouteMap::LIST_JOURNEY_LOG_LINK_TYPE))
            ->assertSessionHas('message', '登録完了しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->post(route(RouteMap::CREATE_JOURNEY_LOG_LINK_TYPE), [
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
