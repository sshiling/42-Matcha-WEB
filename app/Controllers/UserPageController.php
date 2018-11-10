<?php

namespace App\Controllers;

// I 'll get information about user for user page from DB (from User Model)
use App\Models\User;
use App\Email\Email;

class UserPageController extends Controller
{
    // return registration page to user
    public function getUserData($request, $response, $args) {

        $user_info['user'] = User::getUserByOneParam('id', $args['id'], $this->container['conn']);
        $user_info['user']['tags_list'] = explode(',', $user_info['user']['tags']);

        $user_info['who'] = $_SESSION['user'];
        $user_info['whom'] = $user_info['user']['id'];

        if (User::checkIfBlocked($user_info['whom'], $user_info['who'], $this->container['conn']) || $user_info['who'] == $user_info['whom'])
            return $response->withRedirect($this->container->router->pathFor('home'));

        $user_info['like'] = User::checkIfLiked($user_info['who'], $user_info['whom'], $this->container['conn']);
        $user_info['connect'] = User::checkIfConnected($user_info['who'], $user_info['whom'], $this->container['conn']);

        return $this->container->view->render($response, 'user_page.twig', $user_info);
    }
}