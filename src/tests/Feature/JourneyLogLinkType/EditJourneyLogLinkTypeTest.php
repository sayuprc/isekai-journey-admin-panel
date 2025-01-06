<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLogLinkType;

use App\Http\Presenters\JourneyLogLinkType\ViewJourneyLogLinkType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use JourneyLogLinkType\Domain\Entities\OrderNo;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shared\Route\RouteMap;
use Tests\TestCase;

class EditJourneyLogLinkTypeTest extends TestCase
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
        $uuid = $this->generateUuid();

        $this->get(route(RouteMap::SHOW_EDIT_JOURNEY_LOG_LINK_TYPE_FORM, ['journeyLogLinkTypeId' => $uuid]))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::SHOW_LOGIN_FORM));
    }

    #[Test]
    public function withNotUuidStyleId(): void
    {
        $this->get(route(
            RouteMap::SHOW_EDIT_JOURNEY_LOG_LINK_TYPE_FORM,
            ['journeyLogLinkTypeId' => 'not-uuid-style-id']
        ))->assertStatus(404);
    }

    #[Test]
    public function showEditForm(): void
    {
        $uuid = $this->generateUuid();

        $this->journeyLogLinkTypeRepository->shouldReceive('getJourneyLogLinkType')
            ->with(Mockery::on(function (JourneyLogLinkTypeId $arg) use ($uuid): bool {
                return $arg->value === $uuid;
            }))
            ->andReturn(new JourneyLogLinkType(
                new JourneyLogLinkTypeId($uuid),
                new JourneyLogLinkTypeName('名前'),
                new OrderNo(1),
            ))
            ->once();

        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            fn (): JourneyLogLinkTypeRepositoryInterface => $this->journeyLogLinkTypeRepository,
        );

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::SHOW_EDIT_JOURNEY_LOG_LINK_TYPE_FORM, ['journeyLogLinkTypeId' => $uuid]))
            ->assertStatus(200);

        $data = $response->getOriginalContent()->getData();

        $this->assertInstanceOf(ViewJourneyLogLinkType::class, $data['journeyLogLinkType']);
    }

    #[Test]
    public function canEdit(): void
    {
        $uuid = $this->generateUuid();

        $this->journeyLogLinkTypeRepository->shouldReceive('editJourneyLogLinkType')
            ->with(Mockery::on(function (JourneyLogLinkType $arg) use ($uuid): bool {
                return $arg->journeyLogLinkTypeId->value === $uuid
                    && $arg->journeyLogLinkTypeName->value === '動画'
                    && $arg->orderNo->value === 1;
            }))
            ->once();

        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            fn (): JourneyLogLinkTypeRepositoryInterface => $this->journeyLogLinkTypeRepository,
        );

        $this->actingAs($this->user)
            ->post(route(RouteMap::EDIT_JOURNEY_LOG_LINK_TYPE), [
                'journey_log_link_type_id' => $uuid,
                'journey_log_link_type_name' => '動画',
                'order_no' => '1',
            ])
            ->assertStatus(302)
            ->assertLocation(route(RouteMap::LIST_JOURNEY_LOG_LINK_TYPE))
            ->assertSessionHas('message', '更新しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->post(route(RouteMap::EDIT_JOURNEY_LOG_LINK_TYPE), [
                'journey_log_link_type_id' => '',
                'journey_log_link_type_name' => '',
                'order_no' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'journey_log_link_type_id',
                'journey_log_link_type_name',
                'order_no',
            ]);
    }
}
