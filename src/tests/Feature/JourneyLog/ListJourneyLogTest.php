<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLog;

use App\Features\JourneyLog\Domain\Entities\FromOn;
use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLink;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLinkId;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLinkName;
use App\Features\JourneyLog\Domain\Entities\OrderNo;
use App\Features\JourneyLog\Domain\Entities\Period;
use App\Features\JourneyLog\Domain\Entities\Story;
use App\Features\JourneyLog\Domain\Entities\ToOn;
use App\Features\JourneyLog\Domain\Entities\Url;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use App\Models\User;
use App\Shared\Route\RouteMap;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListJourneyLogTest extends TestCase
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
        $this->get(route(RouteMap::LIST_JOURNEY_LOGS))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::SHOW_LOGIN_FORM));
    }

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->journeyLogRepository->shouldReceive('listJourneyLogs')
            ->andReturn([
                new JourneyLog(
                    new JourneyLogId($uuid),
                    new Story('軌跡 A'),
                    new Period(new FromOn(new DateTimeImmutable()), new ToOn(new DateTimeImmutable())),
                    new OrderNo(0),
                    [],
                ),
                new JourneyLog(
                    new JourneyLogId($uuid),
                    new Story('軌跡 B'),
                    new Period(new FromOn(new DateTimeImmutable()), new ToOn(new DateTimeImmutable())),
                    new OrderNo(0),
                    [
                        new JourneyLogLink(
                            new JourneyLogLinkId($uuid),
                            new JourneyLogLinkName('管理画面'),
                            new Url('https://local.admin.journey.isekaijoucho.fan'),
                            new OrderNo(0),
                            new JourneyLogLinkTypeId($uuid),
                        ),
                    ],
                ),
            ])
            ->once();

        $this->app->bind(
            JourneyLogRepositoryInterface::class,
            fn (): JourneyLogRepositoryInterface => $this->journeyLogRepository,
        );

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::LIST_JOURNEY_LOGS))
            ->assertStatus(200)
            ->assertViewIs('journeyLogs.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '内容',
            '期間',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(2, $data['journeyLogs']);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->journeyLogRepository->shouldReceive('listJourneyLogs')
            ->andReturn([])
            ->once();

        $this->app->bind(
            JourneyLogRepositoryInterface::class,
            fn (): JourneyLogRepositoryInterface => $this->journeyLogRepository,
        );

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::LIST_JOURNEY_LOGS))
            ->assertStatus(200)
            ->assertViewIs('journeyLogs.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '内容',
            '期間',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(0, $data['journeyLogs']);
    }
}
