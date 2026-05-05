<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Media\UpdatePresenter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
        $title = $request->input('title');
        $url = $request->input('url');
        $typeValue = $request->input('typeValue');
        $formatValue = $request->input('formatValue');

        $inputData = new UpdateInputData(
            $mediaId,
            is_string($title) ? $title : '',
            is_string($url) ? $url : '',
            is_scalar($typeValue) ? (int)$typeValue : 0,
            is_scalar($formatValue) ? (int)$formatValue : 0,
            (bool)$request->input('isDisplay', false),
        );

        return $this->presenter->present($this->useCase->handle($inputData));
    }
}
