<?php

declare(strict_types=1);

namespace App\Http\Controllers\JourneyLog;

use App\Http\Controllers\Controller;
use App\Http\Requests\JourneyLog\EditRequest;
use App\Http\ViewModel\Edit\JourneyLog;
use App\Http\ViewModel\ViewDate;
use App\Utils\DateFactory;
use Exception;
use Generated\IsekaiJourney\JourneyLog\EditJourneyLogRequest;
use Generated\IsekaiJourney\JourneyLog\EditJourneyLogResponse;
use Generated\IsekaiJourney\JourneyLog\GetJourneyLogRequest;
use Generated\IsekaiJourney\JourneyLog\GetJourneyLogResponse;
use Generated\IsekaiJourney\JourneyLog\JourneyLogServiceClient;
use Generated\IsekaiJourney\JourneyLog\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use stdClass;

use const Grpc\STATUS_OK;

class EditJourneyLogController extends Controller
{
    public function __construct(private readonly JourneyLogServiceClient $client)
    {
    }

    public function index(string $journeyLogId): RedirectResponse|View
    {
        $grpcRequest = new GetJourneyLogRequest();
        $grpcRequest->setJourneyLogId($journeyLogId);

        /**
         * @var GetJourneyLogResponse $response
         * @var stdClass              $status
         */
        [$response, $status] = $this->client->getJourneyLog($grpcRequest)->wait();

        if ($status->code !== STATUS_OK) {
            throw new Exception("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== Status::SUCCESS) {
            return redirect()
                ->route('journey-logs.index')
                ->withErrors([
                    'message' => $response->getMessage(),
                ]);
        }

        $responseJourneyLog = $response->getJourneyLog();
        $fromOn = $responseJourneyLog->getFromOn();
        $toOn = $responseJourneyLog->getToOn();

        $journeyLog = new JourneyLog(
            $responseJourneyLog->getJourneyLogId(),
            $responseJourneyLog->getSummary(),
            $responseJourneyLog->getStory(),
            new ViewDate($fromOn->getYear(), $fromOn->getMonth(), $fromOn->getDay()),
            new ViewDate($toOn->getYear(), $toOn->getMonth(), $toOn->getDay()),
            $responseJourneyLog->getOrderNo()
        );

        return view('journeyLogs.edit.index', compact('journeyLog'));
    }

    public function handle(EditRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $grpcRequest = new EditJourneyLogRequest();
        $grpcRequest->setJourneyLogId($validated['journey_log_id']);
        $grpcRequest->setSummary($validated['summary']);
        $grpcRequest->setStory($validated['story']);
        $grpcRequest->setFromOn(DateFactory::fromString($validated['from_on']));
        $grpcRequest->setToOn(DateFactory::fromString($validated['to_on']));
        $grpcRequest->setOrderNo($validated['order_no']);

        /**
         * @var EditJourneyLogResponse $response
         * @var stdClass               $status
         */
        [$response, $status] = $this->client->EditJourneyLog($grpcRequest)->wait();

        if ($status->code !== STATUS_OK) {
            throw new Exception("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== Status::SUCCESS) {
            return back()
                ->withErrors([
                    'message' => $response->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route('journey-logs.edit.index', $response->getJourneyLog()->getJourneyLogId())
            ->with([
                'message' => '更新しました',
            ]);
    }
}
