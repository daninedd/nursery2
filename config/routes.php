<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\HttpServer\Router\Router;

Router::get('/favicon.ico', function () {
    return '';
});

Router::addServer('socket-io', function () {
    Router::get('/', 'App\Controller\WebSocketController');
});
