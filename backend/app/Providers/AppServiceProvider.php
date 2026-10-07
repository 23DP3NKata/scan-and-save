<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        JsonResource::withoutWrapping();
        RateLimiter::for('login', fn (Request $request): array => [Limit::perMinute(10)->by('login-ip:'.$request->ip()), Limit::perMinute(5)->by('login:'.strtolower((string) $request->input('email')).'|'.$request->ip())]);
        RateLimiter::for('registration', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
    }
}
