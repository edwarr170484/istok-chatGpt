<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ValidateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $requestToken = explode(" ", $request->header("Authorization"));

        if($user && $user->token && (strval($user->token) === last($requestToken))) {
            $parsedToken = JWTAuth::setToken($user->token);    

            try {
                $payload = $parsedToken->getPayload();

                $expirationTime = $payload->get('exp');

                $currentTime = time();

                if ($currentTime < $expirationTime) {
                    return $next($request);
                }
            } catch (TokenExpiredException $e) {
                $user->token = null;
                $user->save();
            }
        }

        return response()->json(['success' => null, 'errors' => 'Не авторизован'], 401);
    }
}
