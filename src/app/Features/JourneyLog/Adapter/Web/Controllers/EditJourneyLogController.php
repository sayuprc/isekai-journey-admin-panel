<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Adapter\Web\Controllers;

use App\Features\JourneyLog\Adapter\Web\Presenters\ViewPeriod;
use App\Features\JourneyLog\Adapter\Web\Presenters\ViewJourneyLog;
use App\Features\JourneyLog\Adapter\Web\Requests\EditRequest as WebEditRequest;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLink;
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
            new ViewPeriod($response->journeyLog->period->fromOn),
            new ViewPeriod($response->journeyLog->period->toOn),
            $response->journeyLog->orderNo->value,
            array_map(function (JourneyLogLink $journeyLogLink): array {
                return [
                    'journey_log_link_name' => $journeyLogLink->journeyLogLinkName->value,
                    'url' => $journeyLogLink->url->value,
                    'order_no' => $journeyLogLink->orderNo->value,
                    'journey_log_link_type_id' => $journeyLogLink->journeyLogLinkTypeId->value,
                ];
            }, $response->journeyLog->journeyLogLinks)
        );

        return view('journeyLogs.edit.index', compact('journeyLog'));
    }

    public function handle(WebEditRequest $request, EditInteractor $interactor): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $interactor->handle(new EditRequest(
                $validated['journey_log_id'],
                $validated['story'],
                $validated['from_on'],
                $validated['to_on'],
                (int)$validated['order_no'],
                array_map(function (array $item): array {
                    return [
                        'journey_log_link_name' => $item['journey_log_link_name'],
                        'url' => $item['url'],
                        'order_no' => (int)$item['order_no'],
                        'journey_log_link_type_id' => $item['journey_log_link_type_id'],
                    ];
                }, $validated['journey_log_links'] ?? []),
            ));
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
