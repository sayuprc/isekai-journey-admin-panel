<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Adapter\Web\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use JourneyLogLinkType\Adapter\Web\Requests\CreateRequest as WebRequest;
use JourneyLogLinkType\UseCases\Create\CreateInteractor;
use JourneyLogLinkType\UseCases\Create\CreateRequest;
use Shared\Route\RouteMap;

class CreateJourneyLogLinkTypeController extends Controller
{
    public function index(): View
    {
        return view('journeyLogLinkTypes.create.index');
    }

    public function handle(WebRequest $request, CreateInteractor $interactor): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $interactor->handle(new CreateRequest(
                $validated['journey_log_link_type_name'],
                (int)$validated['order_no']
            ));
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
