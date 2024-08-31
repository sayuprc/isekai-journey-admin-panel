<?php

declare(strict_types=1);

namespace App\Http\Controllers\JourneyLog;

use App\Http\Controllers\Controller;
use App\Http\Requests\JourneyLog\DeleteRequest;
use Exception;
use Generated\IsekaiJourney\JourneyLog\DeleteJourneyLogRequest;
use Generated\IsekaiJourney\JourneyLog\DeleteJourneyLogResponse;
use Generated\IsekaiJourney\JourneyLog\JourneyLogServiceClient;
use Generated\IsekaiJourney\JourneyLog\Status;
use Illuminate\Http\RedirectResponse;
use stdClass;

use const Grpc\STATUS_OK;

class DeleteJourneyLogController extends Controller
{
    public function __construct(private readonly JourneyLogServiceClient $client)
    {
    }

    public function handle(DeleteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $grpcRequest = new DeleteJourneyLogRequest();
        $grpcRequest->setJourneyLogId($validated['journey_log_id']);

        /**
         * @var DeleteJourneyLogResponse $response
         * @var stdClass                 $status
         */
        [$response, $status] = $this->client->DeleteJourneyLog($grpcRequest)->wait();

        if ($status->code !== STATUS_OK) {
            throw new Exception("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== Status::SUCCESS) {
            return back()->withErrors([
                'message' => $response->getMessage(),
            ]);
        }

        return redirect()
            ->route('journey-logs.index')
            ->with([
                'message' => '削除しました',
            ]);
    }
}
