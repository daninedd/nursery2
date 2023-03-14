<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Middleware\JwtAuthMiddleware;
use App\Model\Address;
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

    /**
     *添加时选择的区域列表.
     */
    #[GetMapping(path: 'getArea')]
    public function getArea()
    {
        $p_id = $this->request->query('p_id');
        $level = $this->request->query('level');
        if ($level == 0){
            $res = [['name' => '全部地区', 'value' => 0, 'level' => 0]];
        }elseif ($level == 1){
            $res = [['name' => '全部城市', 'value' => $p_id, 'level' => 1]];
        }
        elseif ($level == 2){
            $res = [['name' => '全部区县', 'value' => $p_id, 'level' => 2]];
        }
        $addrs = Address::query()->select(['id', 'code', 'name', 'level'])->where('parent_id', $p_id)->get();
        foreach ($addrs as $addr){
            $res []= ['name' => $addr->name, 'value' => $addr->id, 'level' => $addr->level, 'submenu' => []];
        }
        $request = $this->container->get(AddressRequest::class);
        return $this->success($request->list());
    }
}
