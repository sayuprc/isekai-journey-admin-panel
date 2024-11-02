<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLog;

use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Entities\Link;
use App\Features\JourneyLog\Domain\Entities\LinkId;
use App\Features\JourneyLog\Domain\Entities\LinkName;
use App\Features\JourneyLog\Domain\Entities\LinkTypeId;
use App\Features\JourneyLog\Domain\Entities\OrderNo;
use App\Features\JourneyLog\Domain\Entities\Period;
use App\Features\JourneyLog\Domain\Entities\Story;
use App\Features\JourneyLog\Domain\Entities\Url;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListJourneyLogTest extends TestCase
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
        $this->get(route('journey-logs.index'))
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function showList(): void
    {
        $this->app->bind(JourneyLogRepositoryInterface::class, function () {
            return new class () implements JourneyLogRepositoryInterface {
                public function listJourneyLogs(): array
                {
                    return [
                        new JourneyLog(
                            new JourneyLogId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                            new Story('軌跡 A'),
                            new Period(new DateTimeImmutable(), new DateTimeImmutable()),
                            new OrderNo(0),
                            [],
                        ),
                        new JourneyLog(
                            new JourneyLogId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAB'),
                            new Story('軌跡 B'),
                            new Period(new DateTimeImmutable(), new DateTimeImmutable()),
                            new OrderNo(0),
                            [
                                new Link(
                                    new LinkId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAC'),
                                    new LinkName('管理画面'),
                                    new Url('https://local.admin.journey.isekaijoucho.fan'),
                                    new OrderNo(0),
                                    new LinkTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAD')
                                ),
                            ],
                        ),
                    ];
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

        $response = $this->actingAs($this->user)
            ->get(route('journey-logs.index'))
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
        $this->app->bind(JourneyLogRepositoryInterface::class, function () {
            return new class () implements JourneyLogRepositoryInterface {
                public function listJourneyLogs(): array
                {
                    return [];
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

        $response = $this->actingAs($this->user)
            ->get(route('journey-logs.index'))
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
