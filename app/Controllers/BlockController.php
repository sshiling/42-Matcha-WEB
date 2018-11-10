<?php

namespace App\Controllers;

use App\Models\Block;

class BlockController extends Controller {

    public function blockUser($request, $response) {
        $response = $response->withJson(Block::blockUser($request, $response, $this->container['conn']));
        return $response;
    }
}