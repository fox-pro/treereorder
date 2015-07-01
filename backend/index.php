<?php

require_once('config.php');

use DI\DependencyInjection;
use Router\Router;
use Tree\Tree;

// autoload used classes
function __autoload($class_name) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php';
}

// DependecyInjection service
$di = new DependencyInjection();

// register router with some routes
$di->add('router', function() {
    $router = new Router();

    $router->addRoute('get', [new Tree(), 'get']);
    $router->addRoute('get?parent=(\d+)', [new Tree(), 'get']);
    $router->addRoute('update', [new Tree(), 'update']);

    return $router;
});

// register pdo db connection
$di->add('pdo', function() use ($config) {
    $dsn = "mysql:host={$config['database']['host']};port={$config['database']['port']};dbname={$config['database']['dbname']};charset=utf8";
    $opt = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );

    return new PDO($dsn, $config['database']['username'], $config['database']['password'], $opt);
});

$di->get('router')->execute();