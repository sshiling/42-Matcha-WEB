<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\ChatSQL;

class ChatController extends Controller
{
    // return registration page to user
    public function openChat($request, $response) {
        $data['who'] = $_SESSION['user'];
//        $data['whom'] = User::getUserByOneParam('id', $args['id'], $this->container['conn'])['id'];

        $data['contacts'] = User::getConnectionList($this->container['conn']);

        if (User::checkIfBlocked($data['whom'], $data['who'], $this->container['conn']) || $data['who'] == $data['whom'])
            return $this->container->view->render($response, 'home.twig');

        return $this->container->view->render($response, 'chat.twig', $data);
    }

    public function getMsgList($request, $response) {

        $id = $request->getParams()['whom'];
        $data['msg_list'] = ChatSQL::getMsgList($id, $this->container['conn']);

        $response = $response->withJson( $data );

        return $response;
    }
}
