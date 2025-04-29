<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Support\Mapper\MapperInterface;

class WebRequestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->auth();

        $this->journeyLog();

        $this->journeyLogLinkType();

        $this->songType();

        $this->creator();
    }

    private function auth(): void
    {
        $this->app->bind(\Auth\UseCases\Login\LoginRequest::class, function (): \Auth\UseCases\Login\LoginRequest {
            $request = $this->app->make(\App\Http\Requests\Web\Auth\LoginRequest::class);
            assert($request instanceof \App\Http\Requests\Web\Auth\LoginRequest);

            return $this->getMapper()->map(\Auth\UseCases\Login\LoginRequest::class, $request->validated());
        });
    }

    private function journeyLog(): void
    {
        $this->app->bind(\JourneyLog\UseCases\Create\CreateRequest::class, function (): \JourneyLog\UseCases\Create\CreateRequest {
            $request = $this->app->make(\App\Http\Requests\Web\JourneyLog\CreateRequest::class);
            assert($request instanceof \App\Http\Requests\Web\JourneyLog\CreateRequest);

            return $this->getMapper()->map(\JourneyLog\UseCases\Create\CreateRequest::class, $request->validated());
        });

        $this->app->bind(\JourneyLog\UseCases\Edit\EditRequest::class, function (): \JourneyLog\UseCases\Edit\EditRequest {
            $request = $this->app->make(\App\Http\Requests\Web\JourneyLog\EditRequest::class);
            assert($request instanceof \App\Http\Requests\Web\JourneyLog\EditRequest);

            return $this->getMapper()->map(\JourneyLog\UseCases\Edit\EditRequest::class, $request->validated());
        });

        $this->app->bind(\JourneyLog\UseCases\Delete\DeleteRequest::class, function (): \JourneyLog\UseCases\Delete\DeleteRequest {
            $request = $this->app->make(\App\Http\Requests\Web\JourneyLog\DeleteRequest::class);
            assert($request instanceof \App\Http\Requests\Web\JourneyLog\DeleteRequest);

            return $this->getMapper()->map(\JourneyLog\UseCases\Delete\DeleteRequest::class, $request->validated());
        });
    }

    private function journeyLogLinkType(): void
    {
        $this->app->bind(\JourneyLogLinkType\UseCases\Create\CreateRequest::class, function (): \JourneyLogLinkType\UseCases\Create\CreateRequest {
            $request = $this->app->make(\App\Http\Requests\Web\JourneyLogLinkType\CreateRequest::class);
            assert($request instanceof \App\Http\Requests\Web\JourneyLogLinkType\CreateRequest);

            return $this->getMapper()->map(\JourneyLogLinkType\UseCases\Create\CreateRequest::class, $request->validated());
        });

        $this->app->bind(\JourneyLogLinkType\UseCases\Edit\EditRequest::class, function (): \JourneyLogLinkType\UseCases\Edit\EditRequest {
            $request = $this->app->make(\App\Http\Requests\Web\JourneyLogLinkType\EditRequest::class);
            assert($request instanceof \App\Http\Requests\Web\JourneyLogLinkType\EditRequest);

            return $this->getMapper()->map(\JourneyLogLinkType\UseCases\Edit\EditRequest::class, $request->validated());
        });

        $this->app->bind(\JourneyLogLinkType\UseCases\Delete\DeleteRequest::class, function (): \JourneyLogLinkType\UseCases\Delete\DeleteRequest {
            $request = $this->app->make(\App\Http\Requests\Web\JourneyLogLinkType\DeleteRequest::class);
            assert($request instanceof \App\Http\Requests\Web\JourneyLogLinkType\DeleteRequest);

            return $this->getMapper()->map(\JourneyLogLinkType\UseCases\Delete\DeleteRequest::class, $request->validated());
        });
    }

    private function songType(): void
    {
        $this->app->bind(\SongType\UseCases\Create\CreateRequest::class, function (): \SongType\UseCases\Create\CreateRequest {
            $request = $this->app->make(\App\Http\Requests\Web\SongType\CreateRequest::class);
            assert($request instanceof \App\Http\Requests\Web\SongType\CreateRequest);

            return $this->getMapper()->map(\SongType\UseCases\Create\CreateRequest::class, $request->validated());
        });

        $this->app->bind(\SongType\UseCases\Edit\EditRequest::class, function (): \SongType\UseCases\Edit\EditRequest {
            $request = $this->app->make(\App\Http\Requests\Web\SongType\EditRequest::class);
            assert($request instanceof \App\Http\Requests\Web\SongType\EditRequest);

            return $this->getMapper()->map(\SongType\UseCases\Edit\EditRequest::class, $request->validated());
        });

        $this->app->bind(\SongType\UseCases\Delete\DeleteRequest::class, function (): \SongType\UseCases\Delete\DeleteRequest {
            $request = $this->app->make(\App\Http\Requests\Web\SongType\DeleteRequest::class);
            assert($request instanceof \App\Http\Requests\Web\SongType\DeleteRequest);

            return $this->getMapper()->map(\SongType\UseCases\Delete\DeleteRequest::class, $request->validated());
        });
    }

    private function creator(): void
    {
        $this->app->bind(\Creator\UseCases\Create\CreateRequest::class, function (): \Creator\UseCases\Create\CreateRequest {
            $request = $this->app->make(\App\Http\Requests\Web\Creator\CreateRequest::class);
            assert($request instanceof \App\Http\Requests\Web\Creator\CreateRequest);

            return $this->getMapper()->map(\Creator\UseCases\Create\CreateRequest::class, $request->validated());
        });

        $this->app->bind(\Creator\UseCases\Edit\EditRequest::class, function (): \Creator\UseCases\Edit\EditRequest {
            $request = $this->app->make(\App\Http\Requests\Web\Creator\EditRequest::class);
            assert($request instanceof \App\Http\Requests\Web\Creator\EditRequest);

            return $this->getMapper()->map(\Creator\UseCases\Edit\EditRequest::class, $request->validated());
        });

        $this->app->bind(\Creator\UseCases\Delete\DeleteRequest::class, function (): \Creator\UseCases\Delete\DeleteRequest {
            $request = $this->app->make(\App\Http\Requests\Web\Creator\DeleteRequest::class);
            assert($request instanceof \App\Http\Requests\Web\Creator\DeleteRequest);

            return $this->getMapper()->map(\Creator\UseCases\Delete\DeleteRequest::class, $request->validated());
        });
    }

    protected function getMapper(): MapperInterface
    {
        return $this->app->make(MapperInterface::class);
    }
}
