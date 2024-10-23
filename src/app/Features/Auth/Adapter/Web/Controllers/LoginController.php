<?php

declare(strict_types=1);

namespace App\Features\Auth\Adapter\Web\Controllers;

use App\Features\Auth\Adapter\Web\Requests\LoginRequest as WebLoginRequest;
use App\Features\Auth\UseCases\Login\LoginInteractor;
use App\Features\Auth\UseCases\Login\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Translation\Translator;

class LoginController extends Controller
{
    public function __construct(private readonly Translator $translator)
    {
    }

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function handle(WebLoginRequest $request, LoginInteractor $interactor): RedirectResponse
    {
        if ($interactor->handle(new LoginRequest($request->validated()))->isSucceeded) {
            $request->session()->regenerate();

            return redirect()->route('journey-logs.index');
        }

        return back()->withErrors([
            'message' => $this->translator->get('auth.failed'),
        ]);
    }
}
