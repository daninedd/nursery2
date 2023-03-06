<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Middleware\JwtAuthMiddleware;
use App\Request\CategoryRequest;
use App\Request\SupplyRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller]
#[Middlewares([JwtAuthMiddleware::class])]
class SupplyController extends AbstractController
{
    #[PostMapping(path: 'add')]
    public function add()
    {
        $request = $this->container->get(SupplyRequest::class);
        $request->scene(SupplyRequest::SCENE_ADD);
        $request->validateResolved();
        $data = $request->addSupply();
        return $this->success($data);
    }

    #[PostMapping(path: 'edit')]
    public function edit()
    {
        $request = $this->container->get(SupplyRequest::class);
        $request->scene(SupplyRequest::SCENE_EDIT);
        $request->validateResolved();
        $data = $request->editSupply();
        return $this->success($data);
    }

    #[GetMapping(path: 'detail')]
    public function detail()
    {
        $request = $this->container->get(SupplyRequest::class);
        $request->scene(SupplyRequest::SCENE_DETAIL);
        $request->validateResolved();
        $data = $request->detail();
        return $this->success($data);
    }

    #[GetMapping(path: 'searchCategory')]
    public function searchCategory()
    {
        $request = $this->container->get(CategoryRequest::class);
        $request->scene(CategoryRequest::SCENE_SEARCH_LIST);
        $request->validateResolved();
        $data = $request->searchList();
        return $this->success($data);
    }

    #[GetMapping(path: 'getList')]
    public function getList()
    {
        $request = $this->container->get(SupplyRequest::class);
        $request->scene(SupplyRequest::SCENE_LIST);
        $request->validateResolved();
        $data = $request->getList();
        return $this->success($data);
    }

    /**
     * 用户的供应列表.
     */
    #[GetMapping(path: 'getUserSupplyList')]
    public function getUserSupplyList()
    {
        $request = $this->container->get(SupplyRequest::class);
        $request->scene(SupplyRequest::SCENE_USER_SUPPLY_LIST);
        $request->validateResolved();
        $data = $request->getUserSupplyList();
        return $this->success($data);
    }

    /**
     * 刷新供应列表.
     */
    #[PostMapping(path: 'refreshUserSupply')]
    public function refreshUserSupply()
    {
        $request = $this->container->get(SupplyRequest::class);
        $request->scene(SupplyRequest::SCENE_REFRESH_SUPPLY);
        $request->validateResolved();
        $data = $request->refreshUserSupply();
        return $this->success($data);
    }

    /**
     * 下架供应.
     */
    #[PostMapping(path: 'downSupply')]
    public function downSupply()
    {
        $request = $this->container->get(SupplyRequest::class);
        $request->scene(SupplyRequest::SCENE_REFRESH_SUPPLY);
        $request->validateResolved();
        $data = $request->downSupply();
        return $this->success($data);
    }

    /**
     * 推荐供应列表.
     */
    #[GetMapping(path: 'getRecommendList')]
    public function getRecommendList()
    {
        $request = $this->container->get(SupplyRequest::class);
        $request->scene(SupplyRequest::SCENE_RECOMMEND_LIST);
        $request->validateResolved();
        $data = $request->getRecommedList();
        return $this->success($data);
    }
}
