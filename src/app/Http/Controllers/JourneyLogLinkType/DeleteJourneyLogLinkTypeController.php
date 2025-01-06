<?php

declare(strict_types=1);

namespace App\Http\Controllers\JourneyLogLinkType;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use JourneyLogLinkType\UseCases\Delete\DeleteRequest;
use JourneyLogLinkType\UseCases\Delete\DeleteUseCaseInterface;
use Shared\Route\RouteMap;

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
            ->route(RouteMap::LIST_JOURNEY_LOG_LINK_TYPE)
            ->with([
                'message' => '削除しました',
            ]);
    }
}
