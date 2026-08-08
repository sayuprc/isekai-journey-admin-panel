<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Release;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\ReleaseJacketArtUploadResponse;
use Release\Application\Admin\UseCase\UploadJacketArt\UploadJacketArtOutputData;

class UploadJacketArtPresenter
{
    public function present(UploadJacketArtOutputData $outputData): JsonResponse
    {
        return response()->json(
            new ReleaseJacketArtUploadResponse()->setJacketArtUrl($outputData->jacketArtUrl),
            200,
        );
    }
}
