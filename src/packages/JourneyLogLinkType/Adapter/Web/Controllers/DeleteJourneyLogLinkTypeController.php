<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Adapter\Web\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Route\RouteMap;
use Exception;
use Illuminate\Http\RedirectResponse;
use JourneyLogLinkType\Port\UseCases\Delete\DeleteInteractor;
use JourneyLogLinkType\Port\UseCases\Delete\DeleteRequest;

class DeleteJourneyLogLinkTypeController extends Controller
{
    public function handle(DeleteRequest $request, DeleteInteractor $interactor): RedirectResponse
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
            ->route(RouteMap::LIST_JOURNEY_LOG_LINK_TYPE)
            ->with([
                'message' => '削除しました',
            ]);
    }
}
