<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Translation\Translator;

class LoginController extends Controller
{
    public function __construct(private readonly AuthManager $authManager, private readonly Translator $translator)
    {
    }

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function handle(LoginRequest $request): RedirectResponse
    {
        if ($this->authManager->guard()->attempt($request->validated())) {
            $request->session()->regenerate();

            return redirect()->route('home');
        }

        return back()->withErrors([
            'message' => $this->translator->get('auth.failed'),
        ]);
    }
}
