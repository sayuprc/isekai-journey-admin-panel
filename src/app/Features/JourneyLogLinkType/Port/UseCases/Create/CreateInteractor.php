<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Port\UseCases\Create;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use App\Features\JourneyLogLinkType\Domain\Entities\OrderNo;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;

class CreateInteractor
{
    private const string DUMMY_UUID = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    public function __construct(private readonly JourneyLogLinkTypeRepositoryInterface $repository)
    {
    }

    public function handle(CreateRequest $request): void
    {
        $jouenryLogLinkType = new JourneyLogLinkType(
            new JourneyLogLinkTypeId(self::DUMMY_UUID),
            new JourneyLogLinkTypeName($request->journeyLogLinkTypeName),
            new OrderNo($request->orderNo),
        );

        $this->repository->createJourneyLogLinkType($jouenryLogLinkType);
    }
}
