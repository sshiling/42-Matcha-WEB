<?php

namespace App\Controllers;

use App\Models\RegConfirm;
use App\Models\User;

class RegConfirmController extends Controller
{
    public function checkNewUser($request, $response) {
        if ($test = RegConfirm::checkNewUser($request, $response, $this->container['conn'])) {
            return $response->withRedirect($this->container->router->pathFor('edit'));
        } else {
            return $this->container->view->render($response, 'msg.twig', array('msg' => "Oops hash is invalid."));
        }
    }
}