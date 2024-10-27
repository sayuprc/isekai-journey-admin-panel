<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Adapter\Web\Controllers;

use App\Features\JourneyLog\Adapter\Web\Presenters\ViewDate;
use App\Features\JourneyLog\Adapter\Web\Presenters\ViewJourneyLog;
use App\Features\JourneyLog\Adapter\Web\Requests\EditRequest as WebEditRequest;
use App\Features\JourneyLog\Port\UseCases\Edit\EditInteractor;
use App\Features\JourneyLog\Port\UseCases\Edit\EditRequest;
use App\Features\JourneyLog\Port\UseCases\Get\GetInteractor;
use App\Features\JourneyLog\Port\UseCases\Get\GetRequest;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class EditJourneyLogController extends Controller
{
    public function index(string $journeyLogId, GetInteractor $interactor): RedirectResponse|View
    {
        try {
            $response = $interactor->handle(new GetRequest($journeyLogId));
        } catch (Exception $e) {
            return redirect()
                ->route('journey-logs.index')
                ->withErrors([
                    'message' => $e->getMessage(),
                ]);
        }

        // TODO リファクタする
        $journeyLog = new ViewJourneyLog(
            $response->journeyLog->journeyLogId->value,
            $response->journeyLog->story->value,
            new ViewDate($response->journeyLog->eventDate->fromOn),
            new ViewDate($response->journeyLog->eventDate->toOn),
            $response->journeyLog->orderNo->value,
        );

        return view('journeyLogs.edit.index', compact('journeyLog'));
    }

    public function handle(WebEditRequest $request, EditInteractor $handler): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $handler->handle(
                new EditRequest(
                    $validated['journey_log_id'],
                    $validated['story'],
                    $validated['from_on'],
                    $validated['to_on'],
                    (int)$validated['order_no'],
                )
            );
        } catch (Exception $e) {
            return back()
                ->withErrors([
                    'message' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route('journey-logs.index')
            ->with([
                'message' => '更新しました',
            ]);
    }
}
