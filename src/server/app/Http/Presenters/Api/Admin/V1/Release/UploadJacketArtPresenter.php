<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Release;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\ReleaseJacketArtUploadResponse;
use Release\Application\Admin\UseCase\UploadJacketArt\UploadJacketArtOutputData;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class UploadJacketArtPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<UploadJacketArtOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            static fn (UploadJacketArtOutputData $outputData) => [
                new ReleaseJacketArtUploadResponse()->setJacketArtUrl($outputData->jacketArtUrl),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
