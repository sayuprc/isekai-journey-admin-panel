<?php

declare(strict_types=1);

namespace Tests\Feature\Web\JourneyLog;

use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use JourneyLog\Domain\Models\FromOn;
use JourneyLog\Domain\Models\JourneyLog;
use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Models\JourneyLogLink;
use JourneyLog\Domain\Models\JourneyLogLinkId;
use JourneyLog\Domain\Models\JourneyLogLinkName;
use JourneyLog\Domain\Models\Period;
use JourneyLog\Domain\Models\Story;
use JourneyLog\Domain\Models\ToOn;
use JourneyLog\Domain\Models\Url;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Support\Route\RouteMap;
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
        $this->get(route(RouteMap::ListJourneyLogs))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::ShowLoginForm));
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
            ->get(route(RouteMap::ListJourneyLogs))
            ->assertStatus(200)
            ->assertViewIs('journeyLogs.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '期間',
            '内容',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertSame([
            'order' => [[0, 'asc']],
        ], $data['config']);

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
            ->get(route(RouteMap::ListJourneyLogs))
            ->assertStatus(200)
            ->assertViewIs('journeyLogs.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '期間',
            '内容',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertSame([
            'order' => [[0, 'asc']],
        ], $data['config']);

        $this->assertCount(0, $data['journeyLogs']);
    }
}
