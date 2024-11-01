<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Adapter\Web\Controllers;

use App\Features\JourneyLog\Adapter\Web\Requests\CreateRequest as WebCreateRequest;
use App\Features\JourneyLog\Port\UseCases\Create\CreateInteractor;
use App\Features\JourneyLog\Port\UseCases\Create\CreateRequest;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CreateJourneyLogController extends Controller
{
    public function index(): View
    {
        return view('journeyLogs.create.index');
    }

    public function handle(WebCreateRequest $request, CreateInteractor $interactor): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $interactor->handle(new CreateRequest(
                $validated['story'],
                $validated['from_on'],
                $validated['to_on'],
                (int)$validated['order_no'],
                array_map(function ($item) {
                    return [
                        'link_name' => $item['link_name'],
                        'url' => $item['url'],
                        'order_no' => (int)$item['order_no'],
                        'link_type_id' => $item['link_type_id'],
                    ];
                }, $validated['links'] ?? []),
            ));
        } catch (Exception $e) {
            return back()
                ->withErrors([
                    'message' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route('journey-logs.index')
            ->with(['message' => '登録完了しました']);
    }
}
