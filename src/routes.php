<?php

$settings = require __DIR__ . '/settings.php';
$app = new \Slim\App($settings);

$container = $app->getContainer();

// Twig
$container['view'] = function ($container) {
    return new \Slim\Views\Twig(
        __DIR__ . '/../templates', [
        __DIR__ . '/../cache',
        'auto_reload' => true
        ]);
    };
    
// DB Connetcion
$container['db'] = function ($c) {
        $db = $c['settings']['db'];
        $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'],
            $db['user'], $db['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    };

require __DIR__ . '/dependencies.php';
require __DIR__ . '/middleware.php';

// Service Routing
$app->get('/', FormController::class . ':index');
$app->get('/form[/]', FormController::class . ':regist');
$app->get('/form/confirm[/]', FormController::class . ':confirm');
$app->get('/form/complete[/]', FormController::class . ':complete');

// Admin Routing
$app->get('/admin[/]', AdminController::class . ':getIndex');   // Loged in
$app->get('/admin/login[/]', AdminController::class . ':getLogin');

$app->get('/admin/logout[/]', AdminController::class . ':getLogout');
$app->post('/admin/login[/]', AdminController::class . ':postLogin');

$app->get('/admin/manage[/]', AdminController::class . ':getManage');   // Loged in
$app->get('/admin/regist[/]', AdminController::class . ':getRegist');   // Loged in
$app->post('/admin/regist[/]', AdminController::class . ':postRegist');   // Loged in
$app->get('/admin/regist/complete[/]', AdminController::class . ':getRegistComplete');   // Loged in
$app->get('/admin/forms[/]', AdminController::class . ':getForms');   // Loged in
$app->post('/admin/forms[/]', AdminController::class . ':postForms');   // Loged in
$app->get('/admin/logs[/]', AdminController::class . ':getLogs');   // Loged in

$app->run();