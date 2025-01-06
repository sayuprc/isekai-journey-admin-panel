<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\Create;

use App\Features\JourneyLog\Domain\Entities\FromOn;
use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLink;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLinkId;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLinkName;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLinkTypeId;
use App\Features\JourneyLog\Domain\Entities\OrderNo;
use App\Features\JourneyLog\Domain\Entities\Period;
use App\Features\JourneyLog\Domain\Entities\Story;
use App\Features\JourneyLog\Domain\Entities\ToOn;
use App\Features\JourneyLog\Domain\Entities\Url;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use App\Shared\Uuid\UuidGeneratorInterface;
use DateTimeImmutable;

class CreateInteractor
{
    public function __construct(
        private readonly JourneyLogRepositoryInterface $client,
        private readonly UuidGeneratorInterface $generator,
    ) {
    }

    public function handle(CreateRequest $request): void
    {
        $journeyLog = new JourneyLog(
            new JourneyLogId($this->generator->generate()), // 新規登録に ID は不要なのでダミーの ID を持たせている
            new Story($request->story),
            new Period(
                new FromOn(new DateTimeImmutable($request->fromOn)),
                new ToOn(new DateTimeImmutable($request->toOn))
            ),
            new OrderNo($request->orderNo),
            $this->toJourneyLogLinks($request->journeyLogLinks),
        );

        $this->client->createJourneyLog($journeyLog);
    }

    /**
     * @param array<CreateJourneyLogLink> $data
     *
     * @return array<JourneyLogLink>
     */
    private function toJourneyLogLinks(array $data): array
    {
        $journeyLogLinks = [];

        foreach ($data as $link) {
            $journeyLogLinks[] = new JourneyLogLink(
                new JourneyLogLinkId($this->generator->generate()), // リンクはデリートインサートなのでダミー値でよい
                new JourneyLogLinkName($link->journeyLogLinkName),
                new Url($link->url),
                new OrderNo($link->orderNo),
                new JourneyLogLinkTypeId($link->journeyLogLinkTypeId),
            );
        }

        return $journeyLogLinks;
    }
}
