<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\NoEmptyFieldsMiddleware;

// check middleware if user is signed in
$app->group('', function() use ($app) {
    $app->get('/', 'HomeController:index')->setName('home')->add(new NoEmptyFieldsMiddleware($app->getContainer()));

    $app->get('/chat42', 'ChatController:openChat')->setName('chat')->add(new NoEmptyFieldsMiddleware($app->getContainer()));
    $app->post('/getchatmsg', 'ChatController:getMsgList')->setName('getchatmsg');

    $app->get('/user/{id}', 'UserPageController:getUserData')->setName('user')->add(new NoEmptyFieldsMiddleware($app->getContainer()));

    $app->get('/logout', 'AuthController:getSignOut')->setName('auth.signout');


    $app->get('/profile', 'ProfileController:getProfileData')->setName('profile');

    $app->get('/edit', 'EditProfileController:getEditProfile')->setName('edit');
    $app->post('/edit', 'EditProfileController:setEditProfile');

    $app->post('/load_photo', 'EditProfileController:loadPhoto');
    $app->post('/setavatar', 'EditProfileController:setAvatar')->setName('setava');

    $app->post('/like', 'LikeController:setLikes')->setName('like');
    $app->post('/block', 'BlockController:blockUser')->setName('block');
    $app->post('/report', 'ReportController:reportUser')->setName('report');

    $app->post('/getntf', 'HomeController:getntf')->setName('getntf');

    $app->get('/map', 'HomeController:map')->setName('map')->add(new NoEmptyFieldsMiddleware($app->getContainer()));;
    $app->post('/getmap', 'HomeController:getmap')->setName('getmap');


})->add(new AuthMiddleware($container));


// check middleware if user is NOT signed in
$app->group('', function() use ($app) {
    $app->get('/email_sent', 'AuthController:getEmailSent')->setName('email_sent');

    $app->get('/reg_confirm', 'RegConfirmController:checkNewUser')->setName('reg_confirm');

    $app->get('/registration', 'AuthController:getSignUp')->setName('auth.signup');
    $app->post('/registration', 'AuthController:postSignUp');

    $app->get('/login', 'AuthController:getSignIn')->setName('auth.signin');
    $app->post('/login', 'AuthController:postSignIn');

    $app->get('/restore', 'AuthController:getRestore')->setName('restore');
    $app->post('/restore', 'AuthController:postRestore');

    $app->get('/new_password', 'AuthController:getNewPassword')->setName('new_password');
    $app->post('/new_password', 'AuthController:setNewPassword');
})->add(new GuestMiddleware($container));
