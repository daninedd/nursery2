<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');
Router::addRoute(['GET', 'POST', 'HEAD'], '/search', 'App\Controller\IndexController@search');
// Router::addRoute(['GET', 'POST', 'HEAD'], '/getSupplyList', 'App\Controller\IndexController@getSupplyList');
Router::addRoute(['GET', 'POST', 'HEAD'], '/getPurchaseList', 'App\Controller\IndexController@getPurchaseList');
Router::addRoute(['GET', 'POST', 'HEAD'], '/getSupplyDetail', 'App\Controller\IndexController@supplyDetail');
Router::addRoute(['GET', 'POST', 'HEAD'], '/categoryList', 'App\Controller\IndexController@categoryList');

Router::get('/favicon.ico', function () {
    return '';
});

Router::addServer('socket-io', function () {
    Router::get('/', 'App\Controller\WebSocketController');
});
