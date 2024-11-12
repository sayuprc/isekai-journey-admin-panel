<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Infrastructures\Repositories;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use App\Features\JourneyLogLinkType\Domain\Entities\OrderNo;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use App\Shared\Exceptions\APIException;
use Exception;
use Generated\IsekaiJourney\JourneyLogLinkType\CreateJourneyLogLinkTypeRequest;
use Generated\IsekaiJourney\JourneyLogLinkType\CreateJourneyLogLinkTypeResponse;
use Generated\IsekaiJourney\JourneyLogLinkType\JourneyLogLinkType as GrpcJourneyLogLinkType;
use Generated\IsekaiJourney\JourneyLogLinkType\JourneyLogLinkTypeServiceClient;
use Generated\IsekaiJourney\JourneyLogLinkType\ListJourneyLogLinkTypesRequest;
use Generated\IsekaiJourney\JourneyLogLinkType\ListJourneyLogLinkTypesResponse;
use Generated\IsekaiJourney\Shared\Status;
use stdClass;

use const Grpc\STATUS_OK;

class JourneyLogLinkTypeRepository implements JourneyLogLinkTypeRepositoryInterface
{
    public function __construct(private readonly JourneyLogLinkTypeServiceClient $client)
    {
    }

    public function listJourneyLogLinkTypes(): array
    {
        /**
         * @var ListJourneyLogLinkTypesResponse $response
         * @var stdClass                        $status
         */
        [$response, $status] = $this->client->ListJourneyLogLinkTypes(new ListJourneyLogLinkTypesRequest())->wait();

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== Status::SUCCESS) {
            throw new Exception("Response Errors: [{$response->getStatus()}] {$response->getMessage()}");
        }

        $journeyLogLinkTypes = [];

        /** @var GrpcJourneyLogLinkType $journeyLogLinkType */
        foreach ($response->getJourneyLogLinkTypes() as $journeyLogLinkType) {
            $journeyLogLinkTypes[] = $this->toJourneyLogLinkType($journeyLogLinkType);
        }

        return $journeyLogLinkTypes;
    }

    public function createJourneyLogLinkType(JourneyLogLinkType $journeyLogLinkType): void
    {
        $request = new CreateJourneyLogLinkTypeRequest();

        $request->setJourneyLogLinkTypeName($journeyLogLinkType->journeyLogLinkTypeName->value);
        $request->setOrderNo($journeyLogLinkType->orderNo->value);

        /**
         * @var CreateJourneyLogLinkTypeResponse $response
         * @var stdClass                         $status
         */
        [$response, $status] = $this->client->CreateJourneyLogLinkType($request)->wait();

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== Status::SUCCESS) {
            throw new Exception($response->getMessage());
        }
    }

    private function toJourneyLogLinkType(GrpcJourneyLogLinkType $journeyLogLinkType): JourneyLogLinkType
    {
        return new JourneyLogLinkType(
            new JourneyLogLinkTypeId($journeyLogLinkType->getJourneyLogLinkTypeId()),
            new JourneyLogLinkTypeName($journeyLogLinkType->getJourneyLogLinkTypeName()),
            new OrderNo($journeyLogLinkType->getOrderNo())
        );
    }
}
