<?php

declare(strict_types=1);

namespace App\Http\Controllers\JourneyLog;

use App\Http\Controllers\Controller;
use App\Http\ViewModel\ViewDate;
use App\Http\ViewModel\ViewJourneyLog;
use Exception;
use Generated\IsekaiJourney\JourneyLog\JourneyLog;
use Generated\IsekaiJourney\JourneyLog\JourneyLogServiceClient;
use Generated\IsekaiJourney\JourneyLog\ListJourneyLogsRequest;
use Generated\IsekaiJourney\JourneyLog\ListJourneyLogsResponse;
use Generated\IsekaiJourney\JourneyLog\Status;
use Illuminate\Contracts\View\View;
use stdClass;

use const Grpc\STATUS_OK;

class ListJourneyLogsController extends Controller
{
    public function __construct(private readonly JourneyLogServiceClient $client)
    {
    }

    public function index(): View
    {
        /**
         * @var ListJourneyLogsResponse $response
         * @var stdClass                $status
         */
        [$response, $status] = $this->client->ListJourneyLogs(new ListJourneyLogsRequest())->wait();

        if ($status->code !== STATUS_OK) {
            throw new Exception("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== Status::SUCCESS) {
            throw new Exception("Response Errors: [{$response->getStatus()}] {$response->getMessage()}");
        }

        $heads = [
            '概要',
            '内容',
            '期間',
            '表示順',
            '',
        ];

        $journeyLogs = [];

        /** @var JourneyLog $journeyLog */
        foreach ($response->getJourneyLogs() as $journeyLog) {
            $journeyLogs[] = new ViewJourneyLog(
                $journeyLog->getJourneyLogId(),
                $journeyLog->getSummary(),
                $journeyLog->getStory(),
                new ViewDate(
                    $journeyLog->getFromOn()->getYear(),
                    $journeyLog->getFromOn()->getMonth(),
                    $journeyLog->getFromOn()->getDay()
                ),
                new ViewDate(
                    $journeyLog->getToOn()->getYear(),
                    $journeyLog->getToOn()->getMonth(),
                    $journeyLog->getToOn()->getDay()
                ),
                $journeyLog->getOrderNo()
            );
        }

        return view('journeyLogs.list.index', compact('heads', 'journeyLogs'));
    }
}
