<?php

namespace App\Middleware;

use App\Models\User;

class NoEmptyFieldsMiddleware extends Middleware
{
    public function __invoke($request, $response, $next) {

        $user = User::getUserByOneParam('id', $_SESSION['user'], $this->container['conn']);
        if (User::hasEmptyFields($user)) {
            return $response->withRedirect($this->container->router->pathFor('edit'));
        }
        $response = $next($request, $response);
        return $response;
    }
}