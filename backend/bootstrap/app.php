<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(
            fn (Request $request): ?string => $request->is('api/*') ? null : rtrim(config('app.frontend_url'), '/').'/login',
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->respond(function (Response $response): Response {
            if (! request()->is('api/*') || ! $response instanceof JsonResponse) {
                return $response;
            }
            $status = $response->getStatusCode();
            $data = $response->getData(true);
            $message = match ($status) {
                400 => 'Pieprasījums nav derīgs.',
                401 => 'Lūdzu, pieslēdzies savam kontam.',
                403 => in_array($data['message'] ?? '', [
                    'Šis konts ir bloķēts. Sazinieties ar administratoru.',
                    'Reģistrācija pašlaik ir atspējota.',
                    'Šai darbībai nepieciešamas administratora tiesības.',
                ], true)
                    ? $data['message'] : 'Tev nav tiesību veikt šo darbību.',
                404 => 'Pieprasītais ieraksts nav atrasts.',
                405 => 'Šī darbība nav pieejama.',
                413 => 'Fails ir pārāk liels. Izvēlies failu, kas ir mazāks par 10 MiB.',
                419 => 'Sesija ir beigusies. Lūdzu, mēģini vēlreiz.',
                422 => 'Pārbaudi ievadītos datus.',
                429 => 'Pārāk daudz mēģinājumu. Uzgaidi un mēģini vēlreiz.',
                default => $status >= 500 ? 'Servera kļūda. Lūdzu, mēģini vēlreiz vēlāk.' : null,
            };
            if ($message !== null) {
                $response->setData(['message' => $message] + (isset($data['errors']) ? ['errors' => $data['errors']] : []));
            }

            return $response;
        });
    })->create();
