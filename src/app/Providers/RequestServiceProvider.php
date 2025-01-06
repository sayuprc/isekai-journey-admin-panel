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
    }

    private function auth(): void
    {
        $this->app->bind(\Auth\UseCases\Login\LoginRequest::class, function (): \Auth\UseCases\Login\LoginRequest {
            $request = $this->app->make(\App\Http\Requests\Auth\LoginRequest::class);
            assert($request instanceof \App\Http\Requests\Auth\LoginRequest);

            return $this->getMapper()->mapFromArray(\Auth\UseCases\Login\LoginRequest::class, $request->validated());
        });
    }

    private function journeyLog(): void
    {
        $this->app->bind(\JourneyLog\UseCases\Create\CreateRequest::class, function (): \JourneyLog\UseCases\Create\CreateRequest {
            $request = $this->app->make(\App\Http\Requests\JourneyLog\CreateRequest::class);
            assert($request instanceof \App\Http\Requests\JourneyLog\CreateRequest);

            return $this->getMapper()->mapFromArray(\JourneyLog\UseCases\Create\CreateRequest::class, $request->validated());
        });

        $this->app->bind(\JourneyLog\UseCases\Edit\EditRequest::class, function (): \JourneyLog\UseCases\Edit\EditRequest {
            $request = $this->app->make(\App\Http\Requests\JourneyLog\EditRequest::class);
            assert($request instanceof \App\Http\Requests\JourneyLog\EditRequest);

            return $this->getMapper()->mapFromArray(\JourneyLog\UseCases\Edit\EditRequest::class, $request->validated());
        });

        $this->app->bind(\JourneyLog\UseCases\Delete\DeleteRequest::class, function (): \JourneyLog\UseCases\Delete\DeleteRequest {
            $request = $this->app->make(\App\Http\Requests\JourneyLog\DeleteRequest::class);
            assert($request instanceof \App\Http\Requests\JourneyLog\DeleteRequest);

            return $this->getMapper()->mapFromArray(\JourneyLog\UseCases\Delete\DeleteRequest::class, $request->validated());
        });
    }

    protected function getMapper(): MapperInterface
    {
        return $this->app->make(MapperInterface::class);
    }
}
