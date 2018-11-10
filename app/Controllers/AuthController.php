<?php

namespace App\Controllers;

use App\Models\User;

class AuthController extends Controller
{

    // return registration page to user
    public function getSignUp($request, $response) {
        return $this->container->view->render($response, 'auth/registration.twig');
    }


    public function postSignUp($request, $response) {
        //  here will be all our validation
        $validation = $this->container->validator->validate($request, $response, $this->container['conn']);

        if ($validation) {
            $response = $response->withJson($validation);
            return $response;
        }
        User::createUser($request, $response, $this->container['conn']);
        $response = $response->withJson(array("st" => 0, "msg" => "Success"));
        return $response;
    }





    // return login page to user
    public function getSignIn($request, $response) {
        return $this->container->view->render($response, 'auth/login.twig');
    }


    public function postSignIn($request, $response) {
        $data = User::login($request, $this->container['conn']);
        $response = $response->withJson($data);
        return $response;
    }





    public function getRestore($request, $response) {
        return $this->container->view->render($response, 'auth/restore.twig');
    }


    public function postRestore($request, $response) {
        $data = User::restorePasswordEmail($request->getParams()['email'], $this->container['conn']);
        $response = $response->withJson($data);
        return $response;
    }


    public function getSignOut($request, $response) {
        $this->container->auth->logout();
        return $response->withRedirect($this->container->router->pathFor('home'));
    }

    public function getNewPassword($request, $response) {

        $user_info['email'] = $_GET['email'];
        $user_info['hash'] = $_GET['hash'];
        if (!User::checkEmailAndHash($user_info['email'], $user_info['hash'], $this->container['conn']))
            return $this->container->view->render($response, 'msg.twig', array('msg' => "Invalid data"));
        return $this->container->view->render($response, 'auth/new_password.twig', $user_info);
    }

    public function setNewPassword($request, $response) {

        $password = $_POST['password'];
        $password_confirmation = $_POST['password_confirmation'];

        $validation = $this->container->validator->validatePassword($password, $password_confirmation);
        if (!$validation) {
            User::updatePassword($request, $this->container['conn']);
            return $response->withRedirect($this->container->router->pathFor('auth.signin'));
        }

        return $this->container->view->render($response, 'msg.twig', array('msg' => "Invalid password!"));
    }



    public function getEmailSentPage($request, $response) {
        if (strpos($_SERVER['HTTP_REFERER'], '/restore') || strpos($_SERVER['HTTP_REFERER'], '/registration'))
            return $this->container->view->render($response, 'auth/email_sent.twig');
        return $response->withRedirect($this->container->router->pathFor('home'));
    }


    public function getEmailSent($request, $response) {
        return $this->container->view->render($response, 'auth/email_sent.twig');
    }
}



