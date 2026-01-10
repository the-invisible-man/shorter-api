<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyTurnstileToken
{
    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->input('turnstile_token');

        if (!$token) {
            return response()->json(['message' => 'Turnstile validation failed.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $turnstileResponse = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret'),
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        if (!($turnstileResponse->json('success') ?? false)) {
            return response()->json(['message' => 'Turnstile validation failed.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $next($request);
    }
}
