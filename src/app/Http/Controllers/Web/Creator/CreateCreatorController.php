<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Creator;

use App\Http\Controllers\Controller;
use Creator\Route\CreatorRouteMap;
use Creator\UseCases\Create\CreateRequest;
use Creator\UseCases\Create\CreateUseCaseInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CreateCreatorController extends Controller
{
    public function index(): View
    {
        return view('creators.create.index');
    }

    public function handle(CreateRequest $request, CreateUseCaseInterface $interactor): RedirectResponse
    {
        $result = $interactor->handle($request);

        return $result->isErr()
            ? back()
                ->withErrors([
                    'message' => $result->unwrapErr(),
                ])
                ->withInput()
            : redirect()
                ->route(CreatorRouteMap::List)
                ->with(['message' => '登録完了しました']);
    }
}
