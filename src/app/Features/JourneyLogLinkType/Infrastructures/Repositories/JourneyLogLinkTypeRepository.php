<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Infrastructures\Repositories;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use App\Features\JourneyLogLinkType\Domain\Entities\OrderNo;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use App\Shared\Exceptions\APIException;
use App\Shared\Grpc\Status;
use App\Shared\Mapper\MapperInterface;
use Exception;
use Generated\IsekaiJourney\JourneyLogLinkType\CreateJourneyLogLinkTypeRequest;
use Generated\IsekaiJourney\JourneyLogLinkType\CreateJourneyLogLinkTypeResponse;
use Generated\IsekaiJourney\JourneyLogLinkType\DeleteJourneyLogLinkTypeRequest;
use Generated\IsekaiJourney\JourneyLogLinkType\DeleteJourneyLogLinkTypeResponse;
use Generated\IsekaiJourney\JourneyLogLinkType\EditJourneyLogLinkTypeRequest;
use Generated\IsekaiJourney\JourneyLogLinkType\EditJourneyLogLinkTypeResponse;
use Generated\IsekaiJourney\JourneyLogLinkType\GetJourneyLogLinkTypeRequest;
use Generated\IsekaiJourney\JourneyLogLinkType\GetJourneyLogLinkTypeResponse;
use Generated\IsekaiJourney\JourneyLogLinkType\JourneyLogLinkType as GrpcJourneyLogLinkType;
use Generated\IsekaiJourney\JourneyLogLinkType\JourneyLogLinkTypeServiceClient;
use Generated\IsekaiJourney\JourneyLogLinkType\ListJourneyLogLinkTypesRequest;
use Generated\IsekaiJourney\JourneyLogLinkType\ListJourneyLogLinkTypesResponse;
use Generated\IsekaiJourney\Shared\Status as GrpcStatus;

use const Grpc\STATUS_OK;

class JourneyLogLinkTypeRepository implements JourneyLogLinkTypeRepositoryInterface
{
    public function __construct(
        private readonly JourneyLogLinkTypeServiceClient $client,
        private readonly MapperInterface $mapper,
    ) {
    }

    public function listJourneyLogLinkTypes(): array
    {
        [$response, $status] = $this->client->ListJourneyLogLinkTypes(new ListJourneyLogLinkTypesRequest())->wait();
        $status = $this->mapper->mapFromJson(Status::class, $status);

        assert($response instanceof ListJourneyLogLinkTypesResponse);

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== GrpcStatus::SUCCESS) {
            throw new Exception("Response Errors: [{$response->getStatus()}] {$response->getMessage()}");
        }

        $journeyLogLinkTypes = [];

        foreach ($response->getJourneyLogLinkTypes() as $journeyLogLinkType) {
            assert($journeyLogLinkType instanceof GrpcJourneyLogLinkType);
            $journeyLogLinkTypes[] = $this->toJourneyLogLinkType($journeyLogLinkType);
        }

        return $journeyLogLinkTypes;
    }

    public function createJourneyLogLinkType(JourneyLogLinkType $journeyLogLinkType): void
    {
        $request = new CreateJourneyLogLinkTypeRequest();

        $request->setJourneyLogLinkTypeName($journeyLogLinkType->journeyLogLinkTypeName->value);
        $request->setOrderNo($journeyLogLinkType->orderNo->value);

        [$response, $status] = $this->client->CreateJourneyLogLinkType($request)->wait();
        $status = $this->mapper->mapFromJson(Status::class, $status);

        assert($response instanceof CreateJourneyLogLinkTypeResponse);

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== GrpcStatus::SUCCESS) {
            throw new Exception($response->getMessage());
        }
    }

    public function getJourneyLogLinkType(JourneyLogLinkTypeId $journeyLogLinkTypeId): JourneyLogLinkType
    {
        $request = new GetJourneyLogLinkTypeRequest();

        $request->setJourneyLogLinkTypeId($journeyLogLinkTypeId->value);

        [$response, $status] = $this->client->GetJourneyLogLinkType($request)->wait();
        $status = $this->mapper->mapFromJson(Status::class, $status);

        assert($response instanceof GetJourneyLogLinkTypeResponse);

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== GrpcStatus::SUCCESS) {
            throw new Exception($response->getMessage());
        }

        $grpcJourneyLogLinkType = $response->getJourneyLogLinkType();
        assert(! is_null($grpcJourneyLogLinkType));

        return $this->toJourneyLogLinkType($grpcJourneyLogLinkType);
    }

    public function editJourneyLogLinkType(JourneyLogLinkType $journeyLogLinkType): void
    {
        $request = new EditJourneyLogLinkTypeRequest();

        $request->setJourneyLogLinkTypeId($journeyLogLinkType->journeyLogLinkTypeId->value);
        $request->setJourneyLogLinkTypeName($journeyLogLinkType->journeyLogLinkTypeName->value);
        $request->setOrderNo($journeyLogLinkType->orderNo->value);

        [$response, $status] = $this->client->EditJourneyLogLinkType($request)->wait();
        $status = $this->mapper->mapFromJson(Status::class, $status);

        assert($response instanceof EditJourneyLogLinkTypeResponse);

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== GrpcStatus::SUCCESS) {
            throw new Exception($response->getMessage());
        }
    }

    public function deleteJourneyLogLinkType(JourneyLogLinkTypeId $journeyLogLinkTypeId): void
    {
        $request = new DeleteJourneyLogLinkTypeRequest();

        $request->setJourneyLogLinkTypeId($journeyLogLinkTypeId->value);

        [$response, $status] = $this->client->DeleteJourneyLogLinkType($request)->wait();
        $status = $this->mapper->mapFromJson(Status::class, $status);

        assert($response instanceof DeleteJourneyLogLinkTypeResponse);

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== GrpcStatus::SUCCESS) {
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
