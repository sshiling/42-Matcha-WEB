<?php

namespace App\Controllers;

use App\Models\Like;

class LikeController extends Controller {

    public function setLikes($request, $response) {
        $response = $response->withJson(Like::updateLikes($request, $response, $this->container['conn']));
        return $response;
    }

}