<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Adapter\Web\Controllers;

use App\Features\JourneyLogLinkType\Adapter\Web\Presenters\ViewJourneyLogLinkType;
use App\Features\JourneyLogLinkType\Adapter\Web\Requests\EditRequest as WebRequest;
use App\Features\JourneyLogLinkType\Port\UseCases\Edit\EditInteractor;
use App\Features\JourneyLogLinkType\Port\UseCases\Edit\EditRequest;
use App\Features\JourneyLogLinkType\Port\UseCases\Get\GetInteractor;
use App\Features\JourneyLogLinkType\Port\UseCases\Get\GetRequest;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class EditJourneyLogLinkTypeController extends Controller
{
    public function index(string $journeyLogLinkTypeId, GetInteractor $interactor): RedirectResponse|View
    {
        try {
            $response = $interactor->handle(new GetRequest($journeyLogLinkTypeId));
        } catch (Exception $e) {
            return redirect()
                ->route('journey-log-link-types.index')
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
            ->route('journey-log-link-types.index')
            ->with([
                'message' => '更新しました',
            ]);
    }
}
