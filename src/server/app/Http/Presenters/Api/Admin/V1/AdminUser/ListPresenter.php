<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\AdminUser;

use AdminUser\Application\Admin\UseCase\List\ListOutputData;
use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\AdminUserListResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class ListPresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<ListOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (ListOutputData $outputData) => [
                new AdminUserListResponse()->setAdminUsers(array_map($this->converter->toOpenApiAdminUser(...), $outputData->adminUsers)),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
