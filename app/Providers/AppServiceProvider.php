<?php

namespace App\Providers;

use App\Contracts\CaptchaVerifier;
use App\Services\Captcha\NullCaptchaVerifier;
use App\Services\Captcha\TurnstileVerifier;
use App\Services\SiteSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SiteSettings::class);

        $this->app->singleton(CaptchaVerifier::class, function (Application $app): CaptchaVerifier {
            $siteKey = (string) config('services.turnstile.site_key');
            $secret = (string) config('services.turnstile.secret_key');

            if (config('enquiries.captcha') === 'turnstile' && $siteKey !== '' && $secret !== '') {
                return new TurnstileVerifier(
                    $app->make(Http::class),
                    $siteKey,
                    $secret,
                    (string) config('services.turnstile.verify_url'),
                    (int) config('services.turnstile.timeout', 5),
                );
            }

            return new NullCaptchaVerifier;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(12)->mixedCase()->numbers()->uncompromised()
            : Password::min(8));

        $proxies = (string) config('app.trusted_proxies');
        if ($proxies !== '') {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        View::composer(['components.layouts.*', 'components.site.*', 'pages.*', 'livewire.*'], function ($view): void {
            $view->with('site', $this->app->make(SiteSettings::class));
        });
    }
}
