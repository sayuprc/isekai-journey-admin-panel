<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Media\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Media\Application\UseCase\Update\UpdateInputData;
use Media\Application\UseCase\Update\UpdateUseCase;

class UpdateMediaController extends Controller
{
    public function __construct(
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(Request $request, string $mediaId): JsonResponse
    {
        $inputData = new UpdateInputData(
            $mediaId,
            $request->string('title')->toString(),
            $request->string('url')->toString(),
            $request->integer('typeValue'),
            $request->integer('formatValue'),
            $request->boolean('isDisplay'),
        );

        return $this->presenter->present($this->useCase->handle($inputData));
    }
}
