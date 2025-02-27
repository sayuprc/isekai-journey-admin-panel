<?php

declare(strict_types=1);

namespace JourneyLog\Infrastructures\Repositories;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Generated\IsekaiJourney\JourneyLog\CreateJourneyLogRequest;
use Generated\IsekaiJourney\JourneyLog\CreateJourneyLogResponse;
use Generated\IsekaiJourney\JourneyLog\DeleteJourneyLogRequest;
use Generated\IsekaiJourney\JourneyLog\DeleteJourneyLogResponse;
use Generated\IsekaiJourney\JourneyLog\EditJourneyLogRequest;
use Generated\IsekaiJourney\JourneyLog\EditJourneyLogResponse;
use Generated\IsekaiJourney\JourneyLog\GetJourneyLogRequest;
use Generated\IsekaiJourney\JourneyLog\GetJourneyLogResponse;
use Generated\IsekaiJourney\JourneyLog\JourneyLog as GrpcJourneyLog;
use Generated\IsekaiJourney\JourneyLog\JourneyLogLink as GrpcLink;
use Generated\IsekaiJourney\JourneyLog\JourneyLogServiceClient;
use Generated\IsekaiJourney\JourneyLog\ListJourneyLogsRequest;
use Generated\IsekaiJourney\JourneyLog\ListJourneyLogsResponse;
use Generated\IsekaiJourney\Shared\Date;
use Generated\IsekaiJourney\Shared\Status as GrpcStatus;
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
use Shared\Exceptions\APIException;
use Shared\Grpc\Status;
use Shared\Mapper\MapperInterface;

class JourneyLogRepository implements JourneyLogRepositoryInterface
{
    public function __construct(
        private readonly JourneyLogServiceClient $client,
        private readonly MapperInterface $mapper,
    ) {
    }

    /**
     * @throws APIException
     * @throws Exception
     *
     * @return JourneyLog[]
     */
    public function listJourneyLogs(): array
    {
        [$response, $status] = $this->client->ListJourneyLogs(new ListJourneyLogsRequest())->wait();
        $status = $this->mapper->map(Status::class, $status);

        assert($response instanceof ListJourneyLogsResponse);

        if (! $status->isOk()) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== GrpcStatus::SUCCESS) {
            throw new Exception("Response Errors: [{$response->getStatus()}] {$response->getMessage()}");
        }

        $journeyLogs = [];

        foreach ($response->getJourneyLogs() as $journeyLog) {
            assert($journeyLog instanceof GrpcJourneyLog);
            $journeyLogs[] = $this->toJourneyLog($journeyLog);
        }

        return $journeyLogs;
    }

    /**
     * @throws APIException
     * @throws Exception
     */
    public function createJourneyLog(JourneyLog $journeyLog): void
    {
        $request = new CreateJourneyLogRequest();

        $request->setStory($journeyLog->story->value);
        $request->setFromOn($this->createDateFromDateTimeInterface($journeyLog->period->fromOn->value));
        $request->setToOn($this->createDateFromDateTimeInterface($journeyLog->period->toOn->value));
        $request->setOrderNo($journeyLog->orderNo->value);
        $request->setJourneyLogLinks($this->toGrpcLinks($journeyLog->journeyLogLinks));

        [$response, $status] = $this->client->CreateJourneyLog($request)->wait();
        $status = $this->mapper->map(Status::class, $status);

        assert($response instanceof CreateJourneyLogResponse);

        if (! $status->isOk()) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== GrpcStatus::SUCCESS) {
            throw new Exception($response->getMessage());
        }
    }

    /**
     * @throws APIException
     * @throws Exception
     */
    public function getJourneyLog(JourneyLogId $journeyLogId): JourneyLog
    {
        $request = new GetJourneyLogRequest();
        $request->setJourneyLogId($journeyLogId->value);

        [$response, $status] = $this->client->GetJourneyLog($request)->wait();
        $status = $this->mapper->map(Status::class, $status);

        assert($response instanceof GetJourneyLogResponse);

        if (! $status->isOk()) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== GrpcStatus::SUCCESS) {
            throw new Exception($response->getMessage());
        }

        $grpcJourneyLog = $response->getJourneyLog();
        assert(! is_null($grpcJourneyLog));

        return $this->toJourneyLog($grpcJourneyLog);
    }

    /**
     * @throws APIException
     * @throws Exception
     */
    public function editJourneyLog(JourneyLog $journeyLog): JourneyLogId
    {
        $request = new EditJourneyLogRequest();
        $request->setJourneyLogId($journeyLog->journeyLogId->value);
        $request->setStory($journeyLog->story->value);
        $request->setFromOn($this->createDateFromDateTimeInterface($journeyLog->period->fromOn->value));
        $request->setToOn($this->createDateFromDateTimeInterface($journeyLog->period->toOn->value));
        $request->setOrderNo($journeyLog->orderNo->value);
        $request->setJourneyLogLinks($this->toGrpcLinks($journeyLog->journeyLogLinks));

        [$response, $status] = $this->client->EditJourneyLog($request)->wait();
        $status = $this->mapper->map(Status::class, $status);

        assert($response instanceof EditJourneyLogResponse);

        if (! $status->isOk()) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== GrpcStatus::SUCCESS) {
            throw new Exception($response->getMessage());
        }

        return $journeyLog->journeyLogId;
    }

    /**
     * @throws APIException
     * @throws Exception
     */
    public function deleteJourneyLog(JourneyLogId $journeyLogId): void
    {
        $request = new DeleteJourneyLogRequest();
        $request->setJourneyLogId($journeyLogId->value);

        [$response, $status] = $this->client->DeleteJourneyLog($request)->wait();
        $status = $this->mapper->map(Status::class, $status);

        assert($response instanceof DeleteJourneyLogResponse);

        if (! $status->isOk()) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== GrpcStatus::SUCCESS) {
            throw new Exception($response->getMessage());
        }
    }

    private function createDateFromDateTimeInterface(DateTimeInterface $value): Date
    {
        $date = new Date();

        return $date->setYear((int)$value->format('Y'))
            ->setMonth((int)$value->format('m'))
            ->setDay((int)$value->format('d'));
    }

    private function toJourneyLog(GrpcJourneyLog $journeyLog): JourneyLog
    {
        $journeyLogLinks = [];

        foreach ($journeyLog->getJourneyLogLinks() as $link) {
            assert($link instanceof GrpcLink);
            $journeyLogLinks[] = new JourneyLogLink(
                new JourneyLogLinkId($link->getJourneyLogLinkId()),
                new JourneyLogLinkName($link->getJourneyLogLinkName()),
                new Url($link->getUrl()),
                new OrderNo($link->getOrderNo()),
                new JourneyLogLinkTypeId($link->getJourneyLogLinkTypeId()),
            );
        }

        $grpcFromOn = $journeyLog->getFromOn();
        assert(! is_null($grpcFromOn));

        $grpcToOn = $journeyLog->getToOn();
        assert(! is_null($grpcToOn));

        return new JourneyLog(
            new JourneyLogId($journeyLog->getJourneyLogId()),
            new Story($journeyLog->getStory()),
            new Period(
                new FromOn(
                    new DateTimeImmutable(
                        sprintf(
                            '%04s-%02s-%02s',
                            $grpcFromOn->getYear(),
                            $grpcFromOn->getMonth(),
                            $grpcFromOn->getDay(),
                        )
                    ),
                ),
                new ToOn(
                    new DateTimeImmutable(
                        sprintf(
                            '%04s-%02s-%02s',
                            $grpcToOn->getYear(),
                            $grpcToOn->getMonth(),
                            $grpcToOn->getDay(),
                        )
                    )
                )
            ),
            new OrderNo($journeyLog->getOrderNo()),
            $journeyLogLinks,
        );
    }

    /**
     * @param JourneyLogLink[] $journeyLogLinks
     *
     * @return GrpcLink[]
     */
    private function toGrpcLinks(array $journeyLogLinks): array
    {
        $grpcLinks = [];

        foreach ($journeyLogLinks as $link) {
            $grpcLink = new GrpcLink();
            $grpcLink->setJourneyLogLinkId($link->journeyLogLinkId->value);
            $grpcLink->setJourneyLogLinkName($link->journeyLogLinkName->value);
            $grpcLink->setUrl($link->url->value);
            $grpcLink->setOrderNo($link->orderNo->value);
            $grpcLink->setJourneyLogLinkTypeId($link->journeyLogLinkTypeId->value);

            $grpcLinks[] = $grpcLink;
        }

        return $grpcLinks;
    }
}
