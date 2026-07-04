<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Release;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Release\UploadJacketArtPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Release\Application\Admin\UseCase\UploadJacketArt\UploadJacketArtInputData;
use Release\Application\Admin\UseCase\UploadJacketArt\UploadJacketArtUseCase;

class UploadJacketArtController extends Controller
{
    public function __construct(
        private readonly UploadJacketArtUseCase $useCase,
        private readonly UploadJacketArtPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return new UploadJacketArtInputData(
            $request->getContent(),
            (string)$request->headers->get('content-type', ''),
        )
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
