<?php

declare(strict_types=1);

namespace App\Http\Controllers\JourneyLog;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class CreateJourneyLogController extends Controller
{
    public function index(): View
    {
        return view('journeyLogs.create.index');
    }
}
