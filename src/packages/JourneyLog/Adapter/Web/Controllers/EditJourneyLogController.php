<?php

declare(strict_types=1);

namespace JourneyLog\Adapter\Web\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Route\RouteMap;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use JourneyLog\Adapter\Web\Presenters\ViewJourneyLog;
use JourneyLog\Adapter\Web\Presenters\ViewPeriod;
use JourneyLog\Domain\Entities\JourneyLogLink;
use JourneyLog\Port\UseCases\Edit\EditInteractor;
use JourneyLog\Port\UseCases\Edit\EditRequest;
use JourneyLog\Port\UseCases\Get\GetInteractor;
use JourneyLog\Port\UseCases\Get\GetRequest;
use JourneyLogLinkType\Adapter\Web\Presenters\ListViewJourneyLogLinkType;
use JourneyLogLinkType\Port\UseCases\List\ListInteractor;

class EditJourneyLogController extends Controller
{
    public function index(string $journeyLogId, GetInteractor $getInteractor, ListInteractor $listInteractor): RedirectResponse|View
    {
        try {
            $getResponse = $getInteractor->handle(new GetRequest($journeyLogId));
            $listResponse = $listInteractor->handle();
        } catch (Exception $e) {
            return redirect()
                ->route(RouteMap::LIST_JOURNEY_LOGS)
                ->withErrors([
                    'message' => $e->getMessage(),
                ]);
        }

        // TODO リファクタする
        $journeyLog = new ViewJourneyLog(
            $getResponse->journeyLog->journeyLogId->value,
            $getResponse->journeyLog->story->value,
            new ViewPeriod($getResponse->journeyLog->period->fromOn->value),
            new ViewPeriod($getResponse->journeyLog->period->toOn->value),
            $getResponse->journeyLog->orderNo->value,
            array_map(function (JourneyLogLink $journeyLogLink): array {
                return [
                    'journey_log_link_name' => $journeyLogLink->journeyLogLinkName->value,
                    'url' => $journeyLogLink->url->value,
                    'order_no' => $journeyLogLink->orderNo->value,
                    'journey_log_link_type_id' => $journeyLogLink->journeyLogLinkTypeId->value,
                ];
            }, $getResponse->journeyLog->journeyLogLinks)
        );

        $journeyLogLinkTypes = [];

        foreach ($listResponse->journeyLogLinkTypes as $journeyLogLinkType) {
            $journeyLogLinkTypes[] = new ListViewJourneyLogLinkType($journeyLogLinkType);
        }

        return view('journeyLogs.edit.index', compact('journeyLog', 'journeyLogLinkTypes'));
    }

    public function handle(EditRequest $request, EditInteractor $interactor): RedirectResponse
    {
        try {
            $interactor->handle($request);
        } catch (Exception $e) {
            return back()
                ->withErrors([
                    'message' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route(RouteMap::LIST_JOURNEY_LOGS)
            ->with([
                'message' => '更新しました',
            ]);
    }
}
