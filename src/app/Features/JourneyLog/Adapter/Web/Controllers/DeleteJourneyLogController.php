<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Adapter\Web\Controllers;

use App\Features\JourneyLog\Adapter\Web\Requests\DeleteRequest as WebDeleteRequest;
use App\Features\JourneyLog\Port\UseCases\Delete\DeleteInteractor;
use App\Features\JourneyLog\Port\UseCases\Delete\DeleteRequest;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;

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
            ->route('journey-logs.index')
            ->with([
                'message' => '削除しました',
            ]);
    }
}
