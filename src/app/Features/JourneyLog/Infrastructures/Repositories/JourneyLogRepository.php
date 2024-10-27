<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Infrastructures\Repositories;

use App\Features\JourneyLog\Domain\Entities\Period;
use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Entities\OrderNo;
use App\Features\JourneyLog\Domain\Entities\Story;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use App\Features\JourneyLog\Infrastructures\Repositories\Exceptions\APIException;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Generated\IsekaiJourney\JourneyLog\CreateJourneyLogRequest;
use Generated\IsekaiJourney\JourneyLog\CreateJourneyLogResponse;
use Generated\IsekaiJourney\JourneyLog\Date;
use Generated\IsekaiJourney\JourneyLog\DeleteJourneyLogRequest;
use Generated\IsekaiJourney\JourneyLog\DeleteJourneyLogResponse;
use Generated\IsekaiJourney\JourneyLog\EditJourneyLogRequest;
use Generated\IsekaiJourney\JourneyLog\EditJourneyLogResponse;
use Generated\IsekaiJourney\JourneyLog\GetJourneyLogRequest;
use Generated\IsekaiJourney\JourneyLog\GetJourneyLogResponse;
use Generated\IsekaiJourney\JourneyLog\JourneyLog as GrpcJourneyLog;
use Generated\IsekaiJourney\JourneyLog\JourneyLogServiceClient;
use Generated\IsekaiJourney\JourneyLog\ListJourneyLogsRequest;
use Generated\IsekaiJourney\JourneyLog\ListJourneyLogsResponse;
use Generated\IsekaiJourney\JourneyLog\Status;
use stdClass;

use const Grpc\STATUS_OK;

class JourneyLogRepository implements JourneyLogRepositoryInterface
{
    public function __construct(private readonly JourneyLogServiceClient $client)
    {
    }

    /**
     * @throws APIException
     * @throws Exception
     *
     * @return array<JourneyLog>
     */
    public function listJourneyLogs(): array
    {
        /**
         * @var ListJourneyLogsResponse $response
         * @var stdClass                $status
         */
        [$response, $status] = $this->client->ListJourneyLogs(new ListJourneyLogsRequest())->wait();

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== Status::SUCCESS) {
            throw new Exception("Response Errors: [{$response->getStatus()}] {$response->getMessage()}");
        }

        $journeyLogs = [];

        /** @var GrpcJourneyLog $journeyLog */
        foreach ($response->getJourneyLogs() as $journeyLog) {
            $journeyLogs[] = new JourneyLog(
                new JourneyLogId($journeyLog->getJourneyLogId()),
                new Story($journeyLog->getStory()),
                new Period(
                    new DateTimeImmutable(
                        sprintf(
                            '%04s-%02s-%02s',
                            $journeyLog->getFromOn()->getYear(),
                            $journeyLog->getFromOn()->getMonth(),
                            $journeyLog->getFromOn()->getDay(),
                        )
                    ),
                    new DateTimeImmutable(
                        sprintf(
                            '%04s-%02s-%02s',
                            $journeyLog->getToOn()->getYear(),
                            $journeyLog->getToOn()->getMonth(),
                            $journeyLog->getToOn()->getDay(),
                        )
                    ),
                ),
                new OrderNo($journeyLog->getOrderNo()),
            );
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
        $request->setFromOn($this->createDateFromDateTimeInterface($journeyLog->period->fromOn));
        $request->setToOn($this->createDateFromDateTimeInterface($journeyLog->period->toOn));
        $request->setOrderNo($journeyLog->orderNo->value);

        /**
         * @var CreateJourneyLogResponse $response
         * @var stdClass                 $status
         */
        [$response, $status] = $this->client->CreateJourneyLog($request)->wait();

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== Status::SUCCESS) {
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

        /**
         * @var GetJourneyLogResponse $response
         * @var stdClass              $status
         */
        [$response,$status] = $this->client->GetJourneyLog($request)->wait();

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== Status::SUCCESS) {
            throw new Exception($response->getMessage());
        }

        $journeyLog = $response->getJourneyLog();

        return new JourneyLog(
            $journeyLogId,
            new Story($journeyLog->getStory()),
            new Period(
                new DateTimeImmutable(
                    sprintf(
                        '%04s-%02s-%02s',
                        $journeyLog->getFromOn()->getYear(),
                        $journeyLog->getFromOn()->getMonth(),
                        $journeyLog->getFromOn()->getDay(),
                    )
                ),
                new DateTimeImmutable(
                    sprintf(
                        '%04s-%02s-%02s',
                        $journeyLog->getToOn()->getYear(),
                        $journeyLog->getToOn()->getMonth(),
                        $journeyLog->getToOn()->getDay(),
                    )
                )
            ),
            new OrderNo($journeyLog->getOrderNo()),
        );
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
        $request->setFromOn($this->createDateFromDateTimeInterface($journeyLog->period->fromOn));
        $request->setToOn($this->createDateFromDateTimeInterface($journeyLog->period->toOn));
        $request->setOrderNo($journeyLog->orderNo->value);

        /**
         * @var EditJourneyLogResponse $response
         * @var stdClass               $status
         */
        [$response,$status] = $this->client->EditJourneyLog($request)->wait();

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== Status::SUCCESS) {
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

        /**
         * @var DeleteJourneyLogResponse $response
         * @var stdClass                 $status
         */
        [$response, $status] = $this->client->DeleteJourneyLog($request)->wait();

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== Status::SUCCESS) {
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
}
