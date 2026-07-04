<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Auth\Application\Admin\UseCase\Login\LoginStartOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\LoginStartResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class LoginStartPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<LoginStartOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            static fn (LoginStartOutputData $output) => [
                new LoginStartResponse()
                    ->setAuthCeremonyId($output->authCeremonyId)
                    ->setPublicKey((object)$output->publicKey),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
