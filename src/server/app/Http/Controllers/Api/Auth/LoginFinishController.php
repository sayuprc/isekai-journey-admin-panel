<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Auth\LoginFinishPresenter;
use Auth\Application\UseCase\LoginFinish\LoginFinishInputData;
use Auth\Application\UseCase\LoginFinish\LoginFinishUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginFinishController extends Controller
{
    public function __construct(
        private readonly LoginFinishUseCase $useCase,
        private readonly LoginFinishPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $credential */
        $credential = $request->input('credential', []);

        return $this->presenter->present(
            $this->useCase->handle(
                new LoginFinishInputData(
                    $request->string('authCeremonyId')->toString(),
                    $credential,
                ),
            ),
        );
    }
}
