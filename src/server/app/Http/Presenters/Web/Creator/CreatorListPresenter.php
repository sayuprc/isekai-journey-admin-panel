<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\Creator;

use App\Http\ViewModels\Web\Creator\CreatorListView;
use Creator\Domain\Models\Creator;
use Creator\UseCases\List\ListOutputData;

class CreatorListPresenter
{
    /**
     * @return array<CreatorListView>
     */
    public function present(ListOutputData $outputData): array
    {
        return array_map(
            fn (Creator $creator): CreatorListView => new CreatorListView(
                $creator->creatorId->value,
                $creator->creatorName->value,
            ),
            $outputData->creators
        );
    }
}
