<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Middleware\JwtAuthMiddleware;
use App\Request\AddressRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;

#[Controller]
#[Middlewares([JwtAuthMiddleware::class])]
class AddressController extends AbstractController
{
    /**
     *区域列表.
     */
    #[GetMapping(path: 'list')]
    public function list()
    {
        $request = $this->container->get(AddressRequest::class);
        return $this->success($request->list());
    }

    /**
     *添加时选择的区域列表.
     */
    #[GetMapping(path: 'addList')]
    public function addList()
    {
        $request = $this->container->get(AddressRequest::class);
        return $this->success($request->addList());
    }
}
