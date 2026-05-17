<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use AdminUser\Application\Admin\UseCase\Register\RegisterOutputData;
use App\Http\Presenters\Api\Admin\V1\AdminUser\Converter;
use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\RegisterAdminUserResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class RegisterPresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<RegisterOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (RegisterOutputData $outputData) => [
                new RegisterAdminUserResponse()->setAdminUser($this->converter->toOpenApiAdminUser($outputData->adminUser)),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
