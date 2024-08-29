<?php

declare(strict_types=1);

namespace App\Http\Controllers\JourneyLog;

use App\Http\Controllers\Controller;
use App\Http\Requests\JourneyLog\CreateRequest;
use App\Utils\DateFactory;
use Exception;
use Generated\IsekaiJourney\JourneyLog\CreateJourneyLogRequest;
use Generated\IsekaiJourney\JourneyLog\CreateJourneyLogResponse;
use Generated\IsekaiJourney\JourneyLog\JourneyLogServiceClient;
use Generated\IsekaiJourney\JourneyLog\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use stdClass;

use const Grpc\STATUS_OK;

class CreateJourneyLogController extends Controller
{
    public function __construct(private readonly JourneyLogServiceClient $client)
    {
    }

    public function index(): View
    {
        return view('journeyLogs.create.index');
    }

    public function handle(CreateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $grpcRequest = new CreateJourneyLogRequest();
        $grpcRequest->setSummary($validated['summary']);
        $grpcRequest->setStory($validated['story']);
        $grpcRequest->setFromOn(DateFactory::fromString($validated['from_on']));
        $grpcRequest->setToOn(DateFactory::fromString($validated['to_on']));
        $grpcRequest->setOrderNo($validated['order_no']);

        /**
         * @var CreateJourneyLogResponse $response
         * @var stdClass                 $status
         */
        [$response, $status] = $this->client->CreateJourneyLog($grpcRequest)->wait();

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
            ->route('journey-logs.index')
            ->with(['message' => '登録完了しました']);
    }
}
