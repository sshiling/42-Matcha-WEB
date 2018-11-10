<?php

namespace App\Middleware;

use App\Models\User;

class AuthMiddleware extends Middleware
{
    public function __invoke($request, $response, $next) {

        if (!User::user_is_authorized()) {
            return $response->withRedirect($this->container->router->pathFor('auth.signin'));
        }
        $response = $next($request, $response);
        return $response;
    }
}