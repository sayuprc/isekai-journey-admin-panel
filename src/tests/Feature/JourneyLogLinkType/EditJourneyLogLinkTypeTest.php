<?php

declare(strict_types=1);

namespace JourneyLogLinkType;

use App\Features\JourneyLogLinkType\Adapter\Web\Presenters\ViewJourneyLogLinkType;
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

class EditJourneyLogLinkTypeTest extends TestCase
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
        $this->get(route(
            'journey-log-link-types.edit.index',
            ['journeyLogLinkTypeId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA']
        ))->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function withNotUuidStyleId(): void
    {
        $this->get(route(
            'journey-log-link-types.edit.index',
            ['journeyLogLinkTypeId' => 'not-uuid-style-id']
        ))->assertStatus(404);
    }

    #[Test]
    public function showEditForm(): void
    {
        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            function (): JourneyLogLinkTypeRepositoryInterface {
                $func = fn (): JourneyLogLinkType => new JourneyLogLinkType(
                    new JourneyLogLinkTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                    new JourneyLogLinkTypeName('名前'),
                    new OrderNo(1),
                );

                return $this->getJourneyLogLinkTypeRepository(getJourneyLogLinkType: $func);
            }
        );

        $response = $this->actingAs($this->user)
            ->get(route('journey-log-link-types.edit.index', ['journeyLogLinkTypeId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA']))
            ->assertStatus(200);

        $data = $response->getOriginalContent()->getData();

        $this->assertInstanceOf(ViewJourneyLogLinkType::class, $data['journeyLogLinkType']);
    }
}
