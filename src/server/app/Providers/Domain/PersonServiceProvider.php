<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Http\Request;
use Override;
use Person\Application\UseCase\Create\CreateInputData;
use Person\Domain\Models\PersonRepositoryInterface;
use Person\Infrastructures\PersonRepository;

class PersonServiceProvider extends EnvServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(PersonRepositoryInterface::class, PersonRepository::class);

        $this->app->bind(CreateInputData::class, function (): CreateInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(CreateInputData::class, $request->all());
        });
    }
}
