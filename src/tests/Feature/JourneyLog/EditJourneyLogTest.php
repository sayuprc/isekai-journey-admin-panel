<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLog;

use App\Models\User;
use App\Shared\Route\RouteMap;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use JourneyLog\Adapter\Web\Presenters\ViewJourneyLog;
use JourneyLog\Domain\Entities\FromOn;
use JourneyLog\Domain\Entities\JourneyLog;
use JourneyLog\Domain\Entities\JourneyLogId;
use JourneyLog\Domain\Entities\OrderNo;
use JourneyLog\Domain\Entities\Period;
use JourneyLog\Domain\Entities\Story;
use JourneyLog\Domain\Entities\ToOn;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditJourneyLogTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private JourneyLogRepositoryInterface&LegacyMockInterface $journeyLogRepository;

    private JourneyLogLinkTypeRepositoryInterface&LegacyMockInterface $journeyLogLinkTypeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->journeyLogRepository = Mockery::mock(JourneyLogRepositoryInterface::class);
        $this->journeyLogLinkTypeRepository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $uuid = $this->generateUuid();

        $this->get(route(RouteMap::SHOW_EDIT_JOURNEY_LOG_FORM, ['journeyLogId' => $uuid]))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::SHOW_LOGIN_FORM));
    }

    #[Test]
    public function withNotUuidStyleId(): void
    {
        $this->get(route(RouteMap::SHOW_EDIT_JOURNEY_LOG_FORM, ['journeyLogId' => 'not-uuid-style-id']))
            ->assertStatus(404);
    }

    #[Test]
    public function showEditForm(): void
    {
        $uuid = $this->generateUuid();

        $this->journeyLogLinkTypeRepository->shouldReceive('listJourneyLogLinkTypes')
            ->andReturn([])
            ->once();

        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            fn (): JourneyLogLinkTypeRepositoryInterface => $this->journeyLogLinkTypeRepository,
        );

        $this->journeyLogRepository->shouldReceive('getJourneyLog')
            ->with(Mockery::on(function (JourneyLogId $arg) use ($uuid): bool {
                return $arg->value === $uuid;
            }))
            ->andReturn(
                new JourneyLog(
                    new JourneyLogId($uuid),
                    new Story('軌跡'),
                    new Period(new FromOn(new DateTimeImmutable()), new ToOn(new DateTimeImmutable())),
                    new OrderNo(1),
                    []
                )
            )
            ->once();

        $this->app->bind(
            JourneyLogRepositoryInterface::class,
            fn (): JourneyLogRepositoryInterface => $this->journeyLogRepository,
        );

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::SHOW_EDIT_JOURNEY_LOG_FORM, ['journeyLogId' => $uuid]))
            ->assertStatus(200);

        $data = $response->getOriginalContent()->getData();

        $this->assertInstanceOf(ViewJourneyLog::class, $data['journeyLog']);
        $this->assertCount(0, $data['journeyLogLinkTypes']);
    }

    #[Test]
    public function canEdit(): void
    {
        $uuid = $this->generateUuid();

        $this->journeyLogRepository->shouldReceive('editJourneyLog')
            ->with(Mockery::on(function (JourneyLog $arg) use ($uuid): bool {
                return $arg->journeyLogId->value === $uuid
                     && $arg->story->value === '軌跡'
                     && $arg->period->fromOn->value->format('Y-m-d') === '2019-12-09'
                     && $arg->period->toOn->value->format('Y-m-d') === '2019-12-09'
                     && $arg->orderNo->value === 1
                     && count($arg->journeyLogLinks) === 1
                     && $arg->journeyLogLinks[0]->journeyLogLinkId->value === $uuid
                     && $arg->journeyLogLinks[0]->journeyLogLinkName->value === '管理画面'
                     && $arg->journeyLogLinks[0]->url->value === 'https://local.admin.journey.isekaijoucho.fan'
                     && $arg->journeyLogLinks[0]->orderNo->value === 1
                     && $arg->journeyLogLinks[0]->journeyLogLinkTypeId->value === $uuid;
            }))
            ->andReturn(new JourneyLogId($uuid))
            ->once();

        $this->app->bind(
            JourneyLogRepositoryInterface::class,
            fn (): JourneyLogRepositoryInterface => $this->journeyLogRepository,
        );

        $this->actingAs($this->user)
            ->post(route(RouteMap::EDIT_JOURNEY_LOG), [
                'journey_log_id' => $uuid,
                'story' => '軌跡',
                'from_on' => '2019-12-09',
                'to_on' => '2019-12-09',
                'order_no' => '1',
                'journey_log_links' => [
                    [
                        'journey_log_link_name' => '管理画面',
                        'url' => 'https://local.admin.journey.isekaijoucho.fan',
                        'order_no' => '1',
                        'journey_log_link_type_id' => $uuid,
                    ],
                ],
            ])
            ->assertStatus(302)
            ->assertLocation(route(RouteMap::LIST_JOURNEY_LOGS))
            ->assertSessionHas('message', '更新しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->post(route(RouteMap::EDIT_JOURNEY_LOG), [
                'journey_log_id' => '',
                'story' => '',
                'from_on' => '',
                'to_on' => '',
                'order_no' => '',
                'journey_log_links' => [
                    [
                        'journey_log_link_name' => '',
                        'url' => '',
                        'order_no' => '',
                        'journey_log_link_type_id' => '',
                    ],
                ],
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'journey_log_id',
                'story',
                'from_on',
                'to_on',
                'order_no',
                'journey_log_links.0.journey_log_link_name',
                'journey_log_links.0.url',
                'journey_log_links.0.order_no',
                'journey_log_links.0.journey_log_link_type_id',
            ]);
    }

    #[Test]
    public function invalidFormatDate(): void
    {
        $this->actingAs($this->user)
            ->post(route(RouteMap::EDIT_JOURNEY_LOG), [
                'story' => '軌跡',
                'from_on' => '2019/12/09',
                'to_on' => '2019/12/09',
                'order_no' => '1',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'from_on',
                'to_on',
            ]);
    }

    #[Test]
    public function inversionDate(): void
    {
        $this->actingAs($this->user)
            ->post(route(RouteMap::EDIT_JOURNEY_LOG), [
                'story' => '軌跡',
                'from_on' => '2019/12/09',
                'to_on' => '2019/12/08',
                'order_no' => '1',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'from_on',
                'to_on',
            ]);
    }
}
