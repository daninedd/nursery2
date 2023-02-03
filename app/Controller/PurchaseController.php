<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Middleware\JwtAuthMiddleware;
use App\Request\CategoryRequest;
use App\Request\PurchaseRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller]
#[Middlewares([JwtAuthMiddleware::class])]
class PurchaseController extends AbstractController
{
    /**
     *添加供应.
     */
    #[PostMapping(path: 'add')]
    public function add()
    {
        $request = $this->container->get(PurchaseRequest::class);
        $request->scene(PurchaseRequest::SCENE_ADD);
        $request->validateResolved();
        $data = $request->addPurchase();
        return $this->success($data);
    }

    /**
     *修改供应.
     */
    #[PostMapping(path: 'edit')]
    public function edit()
    {
        $request = $this->container->get(PurchaseRequest::class);
        $request->scene(PurchaseRequest::SCENE_EDIT);
        $request->validateResolved();
        $data = $request->editPurchase();
        return $this->success($data);
    }

    /**
     *结束供应.
     */
    #[PostMapping(path: 'endPurchase')]
    public function endPurchase()
    {
        $request = $this->container->get(PurchaseRequest::class);
        $request->scene(PurchaseRequest::SCENE_END_PURCHASE);
        $request->validateResolved();
        $data = $request->endPurchase();
        return $this->success($data);
    }

    /**
     *供应详情.
     */
    #[GetMapping(path: 'detail')]
    public function detail()
    {
        $request = $this->container->get(PurchaseRequest::class);
        $request->scene(PurchaseRequest::SCENE_DETAIL)->validateResolved();
        $data = $request->detail();
        return $this->success($data);
    }

    /**
     *搜索分类列表.
     */
    #[GetMapping(path: 'searchCategory')]
    public function searchCategory()
    {
        $request = $this->container->get(CategoryRequest::class);
        $request->scene(CategoryRequest::SCENE_SEARCH_LIST);
        $request->validateResolved();
        $data = $request->searchList();
        return $this->success($data);
    }

    /**
     *报价.
     */
    #[PostMapping(path: 'offer')]
    public function offer()
    {
        $request = $this->container->get(PurchaseRequest::class);
        $request->scene(PurchaseRequest::SCENE_OFFER);
        $request->validateResolved();
        $data = $request->offer();
        return $this->success($data);
    }

    /**
     *供应列表.
     */
    #[GetMapping(path: 'getList')]
    public function getList()
    {
        $request = $this->container->get(PurchaseRequest::class);
        $request->scene(PurchaseRequest::SCENE_LIST);
        $request->validateResolved();
        $data = $request->getList();
        return $this->success($data);
    }

    /**
     * 用户的求购列表.
     */
    #[GetMapping(path: 'getUserPurchaseList')]
    public function getUserPurchaseList(): \Psr\Http\Message\ResponseInterface
    {
        $request = $this->container->get(PurchaseRequest::class);
        $request->scene(PurchaseRequest::SCENE_USER_PURCHASE_LIST);
        $request->validateResolved();
        $data = $request->getUserPurchaseList();
        return $this->success($data);
    }
}
