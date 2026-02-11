<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidSecurity;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Schema\Exception\FormatMismatch;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use Nyholm\Psr7\Factory\Psr17Factory;
use OpenAPI\Client\Model\ValidationError;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;

class OpenApiValidator
{
    private readonly PsrHttpFactory $psrHttpFactory;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ValidatorBuilder $builder,
        Psr17Factory $psr17Factory,
        OpenApiConfig $config,
    ) {
        $this->builder->fromYamlFile($config->path);

        $this->psrHttpFactory = new PsrHttpFactory(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
        );
    }

    /**
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $psrRequest = $this->psrHttpFactory->createRequest($request);

        try {
            $this->builder->getRequestValidator()->validate($psrRequest);
        } catch (InvalidSecurity) {
            return response()->json([], 401);
        } catch (ValidationFailed $e) {
            // TODO 項目ごとのバリデーションエラーを表示したい
            return $this->handleValidationFailed($e);
        }

        $response = $next($request);

        $psrResponse = $this->psrHttpFactory->createResponse($response);

        try {
            $this->builder->getResponseValidator()->validate(
                new OperationAddress($request->getPathInfo(), strtolower($request->getMethod())),
                $psrResponse,
            );
        } catch (ValidationFailed $e) {
            $this->logger->error('レスポンスバリデーションエラー', [
                'content' => $response->getContent(),
            ]);

            // サーバーレスポンス不整合はサーバー内部の問題なので 500 として返す
            return response()->json(status: 500);
        }

        return $response;
    }

    private function handleValidationFailed(ValidationFailed $exception): JsonResponse
    {
        $previous = $exception->getPrevious();

        if ($previous instanceof SchemaMismatch) {
            $error = $this->formatSchemaMismatch($previous);
        } else {
            $error = new ValidationError()
                ->setField('')
                ->setMessage('予期せぬエラー');
        }

        return response()->json($error, 422);
    }

    private function formatSchemaMismatch(SchemaMismatch $exception): ValidationError
    {
        $breadcrumb = $exception->dataBreadCrumb();

        if (is_null($breadcrumb)) {
            $field = '';
            $message = '予期せぬエラー';
        } else {
            $field = implode('/', $breadcrumb->buildChain());

            // TODO エラーに応じてメッセージを変える
            $message = match (true) {
                $exception instanceof FormatMismatch => sprintf('The value does not match the expected format: %s.', $exception->format()),
                default => $exception->getMessage(),
            };
        }

        return new ValidationError()
            ->setField($field)
            ->setMessage($message);
    }
}
