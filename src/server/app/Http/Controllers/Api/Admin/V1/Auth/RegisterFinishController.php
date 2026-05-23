<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use AdminUser\Application\Admin\UseCase\RegisterFinish\RegisterFinishInputData;
use AdminUser\Application\Admin\UseCase\RegisterFinish\RegisterFinishUseCase;
use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\RegisterFinishPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterFinishController extends Controller
{
    public function __construct(
        private readonly RegisterFinishUseCase $registerFinishUseCase,
        private readonly RegisterFinishPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $credential */
        $credential = $request->array('credential');

        $inputData = new RegisterFinishInputData(
            $request->string('authCeremonyId')->toString(),
            $credential,
        );

        return $this->presenter->present($this->registerFinishUseCase->handle($inputData));
    }
}
