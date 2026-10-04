<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\Cors;
use App\Http\Middleware\EnsureEcoleActive;
use App\Http\Middleware\EnsurePremium;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->append(Cors::class);

        $middleware->web(append: [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
            SubstituteBindings::class,
            EnsureEcoleActive::class,
        ]);

        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.session' => AuthenticateSession::class,
            'guest' => RedirectIfAuthenticated::class,
            'verified' => EnsureEmailIsVerified::class,
            'role' => RoleMiddleware::class,
            'premium' => EnsurePremium::class,
        ]);

        // L'IPN PayDunya est un POST serveur à serveur, sans jeton CSRF
        $middleware->validateCsrfTokens(except: ['paydunya/webhook']);

        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
