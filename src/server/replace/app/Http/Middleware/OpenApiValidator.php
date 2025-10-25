<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\JsonResponse;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Schema\Exception\FormatMismatch;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use Nyholm\Psr7\Factory\Psr17Factory;
use OpenAPI\Client\Model\ValidationError;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tempest\Core\Priority;
use Tempest\Http\Request;
use Tempest\Http\RequestFactory;
use Tempest\Http\Response;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Webmozart\Assert\Assert;

#[Priority(Priority::HIGHEST)]
class OpenApiValidator implements HttpMiddleware
{
    private readonly PsrHttpFactory $psrHttpFactory;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ValidatorBuilder $builder,
        private readonly RequestFactory $requestFactory,
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

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $psrRequest = $this->requestFactory->make();

        try {
            $this->builder->getRequestValidator()->validate($psrRequest);
        } catch (ValidationFailed $e) {
            // TODO 項目ごとのバリデーションエラーを表示したい
            return $this->handleValidationFailed($e);
        }

        $response = $next($request);

        $psrResponse = $this->toPsrResponse($response);

        try {
            $this->builder->getResponseValidator()->validate(
                new OperationAddress($request->path, strtolower($request->method->value)),
                $psrResponse
            );
        } catch (ValidationFailed $e) {
            $this->logger->error('レスポンスバリデーションエラー', [
                'content' => $this->responseToString($response),
            ]);

            // サーバーレスポンス不整合はサーバー内部の問題なので 500 として返す
            return new JsonResponse(status: 500);
        }

        return $response;
    }

    private function toPsrResponse(Response $response): ResponseInterface
    {
        $symfonyResponse = new SymfonyResponse($this->responseToString($response), $response->status->value, $response->headers);

        return $this->psrHttpFactory->createResponse($symfonyResponse);
    }

    private function responseToString(Response $response): string
    {
        $value = json_encode($response->body);
        Assert::string($value);

        return $value;
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

        return new JsonResponse($error, 422);
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
