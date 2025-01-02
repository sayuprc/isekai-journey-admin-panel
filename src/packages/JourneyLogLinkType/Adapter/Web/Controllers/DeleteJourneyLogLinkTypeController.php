<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Adapter\Web\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use JourneyLogLinkType\Adapter\Web\Requests\DeleteRequest as WebRequest;
use JourneyLogLinkType\UseCases\Delete\DeleteInteractor;
use JourneyLogLinkType\UseCases\Delete\DeleteRequest;
use Shared\Route\RouteMap;

class DeleteJourneyLogLinkTypeController extends Controller
{
    public function handle(WebRequest $request, DeleteInteractor $interactor): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $interactor->handle(new DeleteRequest($validated['journey_log_link_type_id']));
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
                'message' => '削除しました',
            ]);
    }
}
