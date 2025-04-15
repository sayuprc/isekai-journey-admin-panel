<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\Creator;

use App\Http\ViewModels\Web\Creator\CreatorListView;
use Creator\Domain\Models\Creator;
use Creator\UseCases\List\ListResponse;

class CreatorListPresenter
{
    /**
     * @return array<CreatorListView>
     */
    public function present(ListResponse $response): array
    {
        return array_map(
            fn (Creator $creator): CreatorListView => new CreatorListView(
                $creator->creatorId->value,
                $creator->creatorName->value,
            ),
            $response->creators
        );
    }
}
