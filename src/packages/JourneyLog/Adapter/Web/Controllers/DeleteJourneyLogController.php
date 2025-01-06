<?php

declare(strict_types=1);

namespace JourneyLog\Adapter\Web\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Route\RouteMap;
use Exception;
use Illuminate\Http\RedirectResponse;
use JourneyLog\Port\UseCases\Delete\DeleteInteractor;
use JourneyLog\Port\UseCases\Delete\DeleteRequest;

class DeleteJourneyLogController extends Controller
{
    public function handle(DeleteRequest $request, DeleteInteractor $interactor): RedirectResponse
    {
        try {
            $interactor->handle($request);
        } catch (Exception $e) {
            return back()->withErrors([
                'message' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route(RouteMap::LIST_JOURNEY_LOGS)
            ->with([
                'message' => '削除しました',
            ]);
    }
}
