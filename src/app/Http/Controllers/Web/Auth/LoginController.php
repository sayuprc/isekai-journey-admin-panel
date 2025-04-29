<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Auth\Route\AuthRouteMap;
use Auth\UseCases\Login\LoginRequest;
use Auth\UseCases\Login\LoginUseCaseInterface;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Translation\Translator;
use JourneyLog\Route\JourneyLogRouteMap;

class LoginController extends Controller
{
    public function __construct(private readonly Translator $translator)
    {
    }

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function handle(Session $session, LoginRequest $request, LoginUseCaseInterface $interactor): RedirectResponse
    {
        if ($interactor->handle($request)->isSucceeded) {
            $session->regenerate();

            return redirect()->route(JourneyLogRouteMap::List);
        }

        return redirect()
            ->route(AuthRouteMap::ShowLoginForm)
            ->withErrors([
                'message' => $this->translator->get('auth.failed'),
            ]);
    }
}
