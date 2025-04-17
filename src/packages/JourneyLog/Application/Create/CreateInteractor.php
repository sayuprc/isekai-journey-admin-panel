<?php

declare(strict_types=1);

namespace JourneyLog\Application\Create;

use DateTimeImmutable;
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
use JourneyLog\UseCases\Create\CreateJourneyLogLink;
use JourneyLog\UseCases\Create\CreateRequest;
use JourneyLog\UseCases\Create\CreateUseCaseInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use Support\Domain\ValueObjects\OrderNo;
use Support\Uuid\UuidGeneratorInterface;

class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private readonly JourneyLogRepositoryInterface $repository,
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

        $this->repository->createJourneyLog($journeyLog);
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
