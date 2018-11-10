<?php
namespace App\Middleware;
use App\Models\User;
class NoPhotosMiddleware extends Middleware
{
    public function __invoke($request, $response, $next) {
        if (User::hasPhotos($_SESSION['user'], $this->container['conn'])) {
            $response = $next($request, $response);
            return $response;
        }
        return $response->withRedirect($this->container->router->pathFor('profile'));
    }
}
