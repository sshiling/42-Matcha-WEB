<?php

session_start();

require __DIR__ . '/../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = 'localhost';
$config['db']['user']   = 'root';
$config['db']['pass']   = '123456';
$config['db']['dbname'] = 'matcha';


$app = new \Slim\App(['settings' => $config]);

// Получаем наш контейнер
$container = $app->getContainer();

$container['notFoundHandler'] = function ($container) {
//    return function ($request, $response) use ($container) {
//        return $container['response']
//            ->withStatus(404)
//            ->withHeader('Content-Type', 'text/html')
//            ->view->render($response, 'home.twig', array('name' => 'Bob'));
//    };
    return function ($request, $response) use ($container) {
        return $container['view']->render($response->withStatus(404), '404.twig', ["myMagic" => "Let's roll"]);
    };
};

$container['conn'] = function($container) {
    $db = new \App\Config\DB;
    return ($db->connect());
};

$container['auth'] = function ($container) {
    return new \App\Auth\Auth;
};

$container['email'] = function($container) {
    return new \App\Email\Email;
};

// Привязываем вьюшки к нашему контейнеру аппликейшна
$container['view'] = function($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [ // каждый раз, когда мы обращаемся к вьюшке нам не нужно будет указывать полный путь
        'cache' => false,       // отключаем кеширование на этапе разработки. Изменить для продакшна.
    ]);


    $view->addExtension(new \Slim\Views\TwigExtension(          // пока что неопнятно зачем добавляем extension к нашей вьюшке
        $container->router,             // для того чтобы генерировать ссылки для наших вьюшек
        $container->request->getUri()   // получаем текущий URI из request
    ));


    $view->getEnvironment()->addGlobal('auth', [
        'check' => $container->auth->check(),
        'user' => $container->auth->user(),
    ]);


    return $view;
};

// here will be all our validation methods
$container['validator'] = function($container) {
    return new App\Validation\Validator;
};

// Привязываем наш HomeController к контейнеру
$container['HomeController'] = function ($container) {
    return new \App\Controllers\HomeController($container); // создаем новый объект класса HomeController
};

$container['AuthController'] = function ($container) {
    return new \App\Controllers\AuthController($container);
};

$container['ProfileController'] = function ($container) {
    return new \App\Controllers\ProfileController($container);
};

$container['UserPageController'] = function ($container) {
    return new \App\Controllers\UserPageController($container);
};

$container['RegConfirmController'] = function($container) {
    return new \App\Controllers\RegConfirmController($container);
};

$container['EditProfileController'] = function($container) {
    return new \App\Controllers\EditProfileController($container);
};

$container['LikeController'] = function($container) {
    return new \App\Controllers\LikeController($container);
};

$container['BlockController'] = function($container) {
    return new \App\Controllers\BlockController($container);
};

$container['ReportController'] = function($container) {
    return new \App\Controllers\ReportController($container);
};

$container['ChatController'] = function($container) {
    return new \App\Controllers\ChatController($container);
};

//$container['ChatController'] = function($container) {
//    return new \App\Controllers\TestController($container);
//};

//$app->add(new \App\Middleware\AuthMiddleware($container));

require __DIR__ . '/../app/routes.php';