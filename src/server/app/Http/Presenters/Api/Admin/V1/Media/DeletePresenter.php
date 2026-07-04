<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Media;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class DeletePresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<null, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        return $result->match(
            static fn () => response()->json(status: 204),
            fn (UseCaseError $error) => response()->json(...$this->resolveError($error)),
        );
    }
}
