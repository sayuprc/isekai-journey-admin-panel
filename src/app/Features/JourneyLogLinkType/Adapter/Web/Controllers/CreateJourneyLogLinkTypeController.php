<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Adapter\Web\Controllers;

use App\Features\JourneyLogLinkType\Port\UseCases\Create\CreateInteractor;
use App\Features\JourneyLogLinkType\Port\UseCases\Create\CreateRequest;
use App\Http\Controllers\Controller;
use App\Shared\Route\RouteMap;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CreateJourneyLogLinkTypeController extends Controller
{
    public function index(): View
    {
        return view('journeyLogLinkTypes.create.index');
    }

    public function handle(CreateRequest $request, CreateInteractor $interactor): RedirectResponse
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
            ->with(['message' => '登録完了しました']);
    }
}
