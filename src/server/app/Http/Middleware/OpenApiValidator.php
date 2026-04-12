<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidSecurity;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Schema\Exception\FormatMismatch;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use Nyholm\Psr7\Factory\Psr17Factory;
use OpenAPI\Client\Model\ValidationError;
use OpenAPI\Client\Model\ValidationErrorDetail;
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
        $operationAddress = $this->resolveOperationAddress($request);

        try {
            $this->builder->getRoutedRequestValidator()->validate($operationAddress, $psrRequest);
        } catch (InvalidSecurity) {
            return response()->json([], 401);
        } catch (ValidationFailed $e) {
            // TODO 項目ごとのバリデーションエラーを表示したい
            return $this->handleValidationFailed($e);
        }

        $response = $next($request);

        $psrResponse = $this->psrHttpFactory->createResponse($response);

        try {
            $this->builder->getResponseValidator()->validate($operationAddress, $psrResponse);
        } catch (ValidationFailed $e) {
            $this->logger->error('レスポンスバリデーションエラー', [
                'content' => $response->getContent(),
            ]);

            // サーバーレスポンス不整合はサーバー内部の問題なので 500 として返す
            return response()->json(status: 500);
        }

        return $response;
    }

    private function resolveOperationAddress(Request $request): OperationAddress
    {
        /** @var Route|null $route */
        $route = $request->route();

        if (is_null($route)) {
            return new OperationAddress($request->getPathInfo(), strtolower($request->getMethod()));
        }

        $specPath = preg_replace('#^admin/[^/]+#', '', $route->uri()) ?? $route->uri();

        return new OperationAddress('/' . ltrim($specPath, '/'), strtolower($request->getMethod()));
    }

    private function handleValidationFailed(ValidationFailed $exception): JsonResponse
    {
        $previous = $exception->getPrevious();

        if ($previous instanceof SchemaMismatch) {
            $detail = $this->formatSchemaMismatch($previous);
        } else {
            $detail = new ValidationErrorDetail()
                ->setField('')
                ->setMessage('予期せぬエラー');
        }

        $error = new ValidationError()->setErrors([$detail]);

        return response()->json($error, 422);
    }

    private function formatSchemaMismatch(SchemaMismatch $exception): ValidationErrorDetail
    {
        $breadcrumb = $exception->dataBreadCrumb();

        if (is_null($breadcrumb)) {
            $field = '';
            $message = '予期せぬエラー';
        } else {
            $field = implode('/', $breadcrumb->buildChain());

            $message = match (true) {
                $exception instanceof FormatMismatch => sprintf('The value does not match the expected format: %s.', $exception->format()),
                default => $exception->getMessage(),
            };
        }

        return new ValidationErrorDetail()
            ->setField($field)
            ->setMessage($message);
    }
}
