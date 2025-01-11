<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Shared\Mapper\MapperInterface;

class RequestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->auth();

        $this->journeyLog();

        $this->journeyLogLinkType();
    }

    private function auth(): void
    {
        $this->app->bind(\Auth\UseCases\Login\LoginRequest::class, function (): \Auth\UseCases\Login\LoginRequest {
            $request = $this->app->make(\App\Http\Requests\Auth\LoginRequest::class);
            assert($request instanceof \App\Http\Requests\Auth\LoginRequest);

            return $this->getMapper()->map(\Auth\UseCases\Login\LoginRequest::class, $request->validated());
        });
    }

    private function journeyLog(): void
    {
        $this->app->bind(\JourneyLog\UseCases\Create\CreateRequest::class, function (): \JourneyLog\UseCases\Create\CreateRequest {
            $request = $this->app->make(\App\Http\Requests\JourneyLog\CreateRequest::class);
            assert($request instanceof \App\Http\Requests\JourneyLog\CreateRequest);

            return $this->getMapper()->map(\JourneyLog\UseCases\Create\CreateRequest::class, $request->validated());
        });

        $this->app->bind(\JourneyLog\UseCases\Edit\EditRequest::class, function (): \JourneyLog\UseCases\Edit\EditRequest {
            $request = $this->app->make(\App\Http\Requests\JourneyLog\EditRequest::class);
            assert($request instanceof \App\Http\Requests\JourneyLog\EditRequest);

            return $this->getMapper()->map(\JourneyLog\UseCases\Edit\EditRequest::class, $request->validated());
        });

        $this->app->bind(\JourneyLog\UseCases\Delete\DeleteRequest::class, function (): \JourneyLog\UseCases\Delete\DeleteRequest {
            $request = $this->app->make(\App\Http\Requests\JourneyLog\DeleteRequest::class);
            assert($request instanceof \App\Http\Requests\JourneyLog\DeleteRequest);

            return $this->getMapper()->map(\JourneyLog\UseCases\Delete\DeleteRequest::class, $request->validated());
        });
    }

    private function journeyLogLinkType(): void
    {
        $this->app->bind(\JourneyLogLinkType\UseCases\Create\CreateRequest::class, function (): \JourneyLogLinkType\UseCases\Create\CreateRequest {
            $request = $this->app->make(\App\Http\Requests\JourneyLogLinkType\CreateRequest::class);
            assert($request instanceof \App\Http\Requests\JourneyLogLinkType\CreateRequest);

            return $this->getMapper()->map(\JourneyLogLinkType\UseCases\Create\CreateRequest::class, $request->validated());
        });

        $this->app->bind(\JourneyLogLinkType\UseCases\Edit\EditRequest::class, function (): \JourneyLogLinkType\UseCases\Edit\EditRequest {
            $request = $this->app->make(\App\Http\Requests\JourneyLogLinkType\EditRequest::class);
            assert($request instanceof \App\Http\Requests\JourneyLogLinkType\EditRequest);

            return $this->getMapper()->map(\JourneyLogLinkType\UseCases\Edit\EditRequest::class, $request->validated());
        });

        $this->app->bind(\JourneyLogLinkType\UseCases\Delete\DeleteRequest::class, function (): \JourneyLogLinkType\UseCases\Delete\DeleteRequest {
            $request = $this->app->make(\App\Http\Requests\JourneyLogLinkType\DeleteRequest::class);
            assert($request instanceof \App\Http\Requests\JourneyLogLinkType\DeleteRequest);

            return $this->getMapper()->map(\JourneyLogLinkType\UseCases\Delete\DeleteRequest::class, $request->validated());
        });
    }

    protected function getMapper(): MapperInterface
    {
        return $this->app->make(MapperInterface::class);
    }
}
