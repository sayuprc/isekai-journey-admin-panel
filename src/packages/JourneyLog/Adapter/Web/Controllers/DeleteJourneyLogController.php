<?php

declare(strict_types=1);

namespace JourneyLog\Adapter\Web\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use JourneyLog\Adapter\Web\Requests\DeleteRequest as WebDeleteRequest;
use JourneyLog\Port\UseCases\Delete\DeleteInteractor;
use JourneyLog\Port\UseCases\Delete\DeleteRequest;
use Shared\Route\RouteMap;

class DeleteJourneyLogController extends Controller
{
    public function handle(WebDeleteRequest $request, DeleteInteractor $interactor): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $interactor->handle(new DeleteRequest($validated['journey_log_id']));
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
