<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\Creator;

use App\Http\ViewModels\Web\Creator\CreatorView;
use Creator\Application\UseCase\Get\GetOutputData;

class CreatorPresenter
{
    public function present(GetOutputData $outputData): CreatorView
    {
        return new CreatorView(
            $outputData->creator->creatorId->value,
            $outputData->creator->creatorName->value,
        );
    }
}
