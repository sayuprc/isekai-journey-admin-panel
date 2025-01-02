<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Adapter\Web\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use JourneyLogLinkType\Adapter\Web\Presenters\ViewJourneyLogLinkType;
use JourneyLogLinkType\Adapter\Web\Requests\EditRequest as WebRequest;
use JourneyLogLinkType\UseCases\Edit\EditInteractor;
use JourneyLogLinkType\UseCases\Edit\EditRequest;
use JourneyLogLinkType\UseCases\Get\GetInteractor;
use JourneyLogLinkType\UseCases\Get\GetRequest;
use Shared\Route\RouteMap;

class EditJourneyLogLinkTypeController extends Controller
{
    public function index(string $journeyLogLinkTypeId, GetInteractor $interactor): RedirectResponse|View
    {
        try {
            $response = $interactor->handle(new GetRequest($journeyLogLinkTypeId));
        } catch (Exception $e) {
            return redirect()
                ->route(RouteMap::LIST_JOURNEY_LOG_LINK_TYPE)
                ->withErrors([
                    'message' => $e->getMessage(),
                ]);
        }

        $journeyLogLinkType = new ViewJourneyLogLinkType(
            $response->journeyLogLinkType->journeyLogLinkTypeId->value,
            $response->journeyLogLinkType->journeyLogLinkTypeName->value,
            $response->journeyLogLinkType->orderNo->value,
        );

        return view('journeyLogLinkTypes.edit.index', compact('journeyLogLinkType'));
    }

    public function handle(WebRequest $request, EditInteractor $interactor): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $interactor->handle(new EditRequest(
                $validated['journey_log_link_type_id'],
                $validated['journey_log_link_type_name'],
                (int)$validated['order_no'],
            ));
        } catch (Exception $e) {
            return back()
                ->withErrors([
                    'message' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route(RouteMap::LIST_JOURNEY_LOG_LINK_TYPE)
            ->with([
                'message' => '更新しました',
            ]);
    }
}
