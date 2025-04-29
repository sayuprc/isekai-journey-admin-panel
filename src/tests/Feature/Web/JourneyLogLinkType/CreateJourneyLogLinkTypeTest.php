<?php

declare(strict_types=1);

namespace Tests\Feature\Web\JourneyLogLinkType;

use App\Models\User;
use Auth\Route\AuthRouteMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\Route\JourneyLogLinkTypeRouteMap;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateJourneyLogLinkTypeTest extends TestCase
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
        $this->get(route(JourneyLogLinkTypeRouteMap::ShowCreateForm))
            ->assertStatus(302)
            ->assertRedirect(route(AuthRouteMap::ShowLoginForm));
    }

    #[Test]
    public function showCreateForm(): void
    {
        $this->actingAs($this->user)
            ->get(route(JourneyLogLinkTypeRouteMap::ShowCreateForm))
            ->assertStatus(200);
    }

    #[Test]
    public function canCreate(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('insert')
            ->with(Mockery::on(
                fn (JourneyLogLinkType $arg): bool => $arg->journeyLogLinkTypeId->value === $uuid
                    && $arg->journeyLogLinkTypeName->value === '軌跡リンク種別A'
                    && $arg->orderNo->value === 1
            ))
            ->once();

        $this->actingAs($this->user)
            ->post(route(JourneyLogLinkTypeRouteMap::Create), [
                'journey_log_link_type_name' => '軌跡リンク種別A',
                'order_no' => '1',
            ])
            ->assertStatus(302)
            ->assertLocation(route(JourneyLogLinkTypeRouteMap::List))
            ->assertSessionHas('message', '登録完了しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->post(route(JourneyLogLinkTypeRouteMap::Create), [
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
