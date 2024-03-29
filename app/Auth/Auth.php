<?php

namespace App\Auth;

use App\Models\User;

class Auth
{
    public function user() {
        return $_SESSION['user'];
    }

    public function check() {
        return isset($_SESSION['user']);
    }

//    public function attempt($request) {
//        if (User::read($request)) {
//            return true;
//        } else {
//            return false;
//        }
//    }

    public function logout(){
        unset($_SESSION['user']);
    }

}


//    public function attempt($email, $password) {
//
//        // grab the user by email
//        $user = User::read('email', $email);
//
//        // if !user return false
//        if (!$user) {
//            return false;
//        }
//
//        // verify password for that user
//        if (password_verify($password, $user->password)) {
//            // set into session
//            $_SESSION['user'] = $user->id;
//            return true;
//        }
//
//        return false;
//    }