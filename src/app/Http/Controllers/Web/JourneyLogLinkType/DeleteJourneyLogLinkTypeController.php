<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\JourneyLogLinkType;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use JourneyLogLinkType\Route\JourneyLogLinkTypeRouteMap;
use JourneyLogLinkType\UseCases\Delete\DeleteRequest;
use JourneyLogLinkType\UseCases\Delete\DeleteUseCaseInterface;

class DeleteJourneyLogLinkTypeController extends Controller
{
    public function handle(DeleteRequest $request, DeleteUseCaseInterface $interactor): RedirectResponse
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
            ->route(JourneyLogLinkTypeRouteMap::List)
            ->with([
                'message' => '削除しました',
            ]);
    }
}
