<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Adapter\Web\Controllers;

use App\Features\JourneyLogLinkType\Port\UseCases\Delete\DeleteInteractor;
use App\Features\JourneyLogLinkType\Port\UseCases\Delete\DeleteRequest;
use App\Http\Controllers\Controller;
use App\Shared\Route\RouteMap;
use Exception;
use Illuminate\Http\RedirectResponse;

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
