<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Creator;

use App\Http\Controllers\Controller;
use Creator\Application\UseCase\Create\CreateInputData;
use Creator\Application\UseCase\Create\CreateUseCaseInterface;
use Creator\Route\CreatorRouteMap;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CreateCreatorController extends Controller
{
    public function index(): View
    {
        return view('creators.create.index');
    }

    public function handle(CreateInputData $inputData, CreateUseCaseInterface $interactor): RedirectResponse
    {
        $result = $interactor->handle($inputData);

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
