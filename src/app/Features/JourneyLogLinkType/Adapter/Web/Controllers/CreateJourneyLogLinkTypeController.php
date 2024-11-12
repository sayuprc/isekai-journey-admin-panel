<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Adapter\Web\Controllers;

use App\Features\JourneyLogLinkType\Adapter\Web\Requests\CreateRequest as WebRequest;
use App\Features\JourneyLogLinkType\Port\UseCases\Create\CreateInteractor;
use App\Features\JourneyLogLinkType\Port\UseCases\Create\CreateRequest;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

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
            ->route('journey-log-link-types.index')
            ->with(['message' => '登録完了しました']);
    }
}
