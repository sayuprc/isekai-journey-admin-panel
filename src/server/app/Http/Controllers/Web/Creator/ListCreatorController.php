<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Creator;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Web\Creator\CreatorListPresenter;
use Creator\UseCases\List\ListUseCaseInterface;
use Illuminate\Contracts\View\View;

class ListCreatorController extends Controller
{
    public function index(ListUseCaseInterface $interactor, CreatorListPresenter $presenter): View
    {
        $heads = [
            'クリエイター名',
            '',
        ];

        $config = [
            'order' => [[0, 'asc']],
        ];

        $creators = $presenter->present($interactor->handle());

        return view('creators.list.index', compact('heads', 'config', 'creators'));
    }
}
