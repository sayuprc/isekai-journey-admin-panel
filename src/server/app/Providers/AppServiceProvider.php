<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $url = config()->string('app.url');

        URL::forceScheme(str_starts_with($url, 'https') ? 'https' : 'http');

        $this->registerPasskeyRateLimiters();
    }

    /**
     * 認証系エンドポイントのレート制限。
     *
     * BFF 経由だと送信元 IP が単一に集約されるため、IP ではなく
     * メール / ceremony / refresh token といった資源キーで絞る。
     */
    private function registerPasskeyRateLimiters(): void
    {
        RateLimiter::for(
            'passkey-login-start',
            fn (Request $request): Limit => $this->limit('login')->by('login-start:' . $this->emailKey($request)),
        );

        RateLimiter::for(
            'passkey-login-finish',
            fn (Request $request): Limit => $this->limit('login')->by('login-finish:' . $request->string('authCeremonyId')->toString()),
        );

        RateLimiter::for(
            'passkey-register-start',
            fn (Request $request): Limit => $this->limit('register')->by('register-start:' . $this->emailKey($request)),
        );

        RateLimiter::for(
            'passkey-register-finish',
            fn (Request $request): Limit => $this->limit('register')->by('register-finish:' . $request->string('authCeremonyId')->toString()),
        );

        RateLimiter::for(
            'passkey-refresh',
            fn (Request $request): Limit => $this->limit('refresh')->by('refresh:' . $request->string('refreshTokenId')->toString()),
        );
    }

    private function limit(string $name): Limit
    {
        return Limit::perMinute(config()->integer('auth.passkey.rate_limit.' . $name));
    }

    private function emailKey(Request $request): string
    {
        return $request->string('email')->lower()->trim()->toString();
    }
}
