<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth\UseCases\Login\LoginRequest;
use Auth\UseCases\Login\LoginUseCaseInterface;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Translation\Translator;
use Shared\Route\RouteMap;

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

            return redirect()->route(RouteMap::LIST_JOURNEY_LOGS);
        }

        return redirect()
            ->route(RouteMap::SHOW_LOGIN_FORM)
            ->withErrors([
                'message' => $this->translator->get('auth.failed'),
            ]);
    }
}
