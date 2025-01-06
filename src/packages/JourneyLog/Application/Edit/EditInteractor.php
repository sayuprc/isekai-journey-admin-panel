<?php

declare(strict_types=1);

namespace JourneyLog\Application\Edit;

use DateTimeImmutable;
use JourneyLog\Domain\Entities\FromOn;
use JourneyLog\Domain\Entities\JourneyLog;
use JourneyLog\Domain\Entities\JourneyLogId;
use JourneyLog\Domain\Entities\JourneyLogLink;
use JourneyLog\Domain\Entities\JourneyLogLinkId;
use JourneyLog\Domain\Entities\JourneyLogLinkName;
use JourneyLog\Domain\Entities\JourneyLogLinkTypeId;
use JourneyLog\Domain\Entities\OrderNo;
use JourneyLog\Domain\Entities\Period;
use JourneyLog\Domain\Entities\Story;
use JourneyLog\Domain\Entities\ToOn;
use JourneyLog\Domain\Entities\Url;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Edit\EditJourneyLogLink;
use JourneyLog\UseCases\Edit\EditRequest;
use JourneyLog\UseCases\Edit\EditUseCaseInterface;
use Shared\Uuid\UuidGeneratorInterface;

class EditInteractor implements EditUseCaseInterface
{
    public function __construct(
        private readonly JourneyLogRepositoryInterface $client,
        private readonly UuidGeneratorInterface $generator,
    ) {
    }

    public function handle(EditRequest $request): void
    {
        $journeyLog = new JourneyLog(
            new JourneyLogId($request->journeyLogId),
            new Story($request->story),
            new Period(
                new FromOn(new DateTimeImmutable($request->fromOn)),
                new ToOn(new DateTimeImmutable($request->toOn))
            ),
            new OrderNo($request->orderNo),
            $this->toJourneyLogLinks($request->journeyLogLinks),
        );

        $this->client->editJourneyLog($journeyLog);
    }

    /**
     * @param array<EditJourneyLogLink> $data
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
