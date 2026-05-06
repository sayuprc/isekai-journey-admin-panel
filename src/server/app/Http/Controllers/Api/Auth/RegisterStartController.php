<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Auth\RegisterStartPresenter;
use Auth\Application\UseCase\RegisterStart\RegisterStartInputData;
use Auth\Application\UseCase\RegisterStart\RegisterStartUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterStartController extends Controller
{
    public function __construct(
        private readonly RegisterStartUseCase $useCase,
        private readonly RegisterStartPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->presenter->present(
            $this->useCase->handle(
                new RegisterStartInputData(
                    $request->string('email')->toString(),
                    $request->string('registrationToken')->toString(),
                ),
            ),
        );
    }
}
