<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLogLinkType;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use App\Features\JourneyLogLinkType\Domain\Entities\OrderNo;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListJourneyLogLinkTypeTest extends TestCase
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
        $this->get(route('journey-log-link-types.index'))
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function showList(): void
    {
        $this->app->bind(JourneyLogLinkTypeRepositoryInterface::class, function () {
            return new class () implements JourneyLogLinkTypeRepositoryInterface {
                public function listJourneyLogLinkTypes(): array
                {
                    return [
                        new JourneyLogLinkType(
                            new JourneyLogLinkTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                            new JourneyLogLinkTypeName('名前1'),
                            new OrderNo(1),
                        ),
                        new JourneyLogLinkType(
                            new JourneyLogLinkTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAB'),
                            new JourneyLogLinkTypeName('名前2'),
                            new OrderNo(2),
                        ),
                    ];
                }
            };
        });

        $response = $this->actingAs($this->user)
            ->get(route('journey-log-link-types.index'))
            ->assertStatus(200)
            ->assertViewIs('journeyLogLinkTypes.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '名前',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(2, $data['journeyLogLinkTypes']);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->app->bind(JourneyLogLinkTypeRepositoryInterface::class, function () {
            return new class () implements JourneyLogLinkTypeRepositoryInterface {
                public function listJourneyLogLinkTypes(): array
                {
                    return [];
                }
            };
        });

        $response = $this->actingAs($this->user)
            ->get(route('journey-log-link-types.index'))
            ->assertStatus(200)
            ->assertViewIs('journeyLogLinkTypes.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '名前',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(0, $data['journeyLogLinkTypes']);
    }
}
