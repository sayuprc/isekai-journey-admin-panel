<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Adapter\Web\Controllers;

use App\Features\JourneyLogLinkType\Adapter\Web\Requests\DeleteRequest as WebRequest;
use App\Features\JourneyLogLinkType\Port\UseCases\Delete\DeleteInteractor;
use App\Features\JourneyLogLinkType\Port\UseCases\Delete\DeleteRequest;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;

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
            ->route('journey-log-link-types.index')
            ->with([
                'message' => '削除しました',
            ]);
    }
}
