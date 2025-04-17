<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\Creator;

use App\Http\ViewModels\Web\Creator\CreatorView;
use Creator\UseCases\Get\GetResponse;

class CreatorPresenter
{
    public function present(GetResponse $response): CreatorView
    {
        return new CreatorView(
            $response->creator->creatorId->value,
            $response->creator->creatorName->value,
        );
    }
}
