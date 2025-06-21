<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use Support\Contracts\ConfigInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;

class OpenApiValidator
{
    private readonly PsrHttpFactory $psrHttpFactory;

    public function __construct(
        private readonly ValidatorBuilder $builder,
        Psr17Factory $psr17Factory,
        ConfigInterface $config,
    ) {
        $this->builder->fromYamlFile($config->getString('openapi.path'));

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

        $this->builder->getRequestValidator()->validate($psrRequest);

        $response = $next($request);

        $psrResponse = $this->psrHttpFactory->createResponse($response);

        $this->builder->getResponseValidator()->validate(
            new OperationAddress($request->getPathInfo(), strtolower($request->getMethod())),
            $psrResponse
        );

        return $response;
    }
}
