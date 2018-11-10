<?php

namespace App\Middleware;

use App\Models\User;

class GuestMiddleware extends Middleware
{
    public function __invoke($request, $response, $next) {

        // check if the user is not signed in
        if (User::user_is_authorized()) {
//            $this->container->flash->addMessage('error', 'Please sign in before doing that.');
            return $response->withRedirect($this->container->router->pathFor('home'));
        }

        $response = $next($request, $response);
        return $response;
    }
}