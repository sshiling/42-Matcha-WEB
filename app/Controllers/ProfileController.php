<?php

namespace App\Controllers;

// I 'll get information about user for user page from DB (from User Model)
use App\Models\User;
use App\Models\Notif;
use App\Models\History;

class ProfileController extends Controller
{
    // return registration page to user
    public function getProfileData($request, $response) {
        $data['who'] = $_SESSION['user'];
        $data['user'] = User::getUserByOneParam('id', $_SESSION['user'], $this->container['conn']);
        $data['user']['tags_list'] = explode(',', $data['user']['tags']);

        $data['notif'] = Notif::getNotifList($this->container['conn']);

        return $this->container->view->render($response, 'profile.twig', $data);
//        return $this->container->view->render($response, 'msg.twig', array('msg' => "Msg: test"));
    }

}
