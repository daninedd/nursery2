<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Middleware\JwtAuthMiddleware;
use App\Request\PurchaseRequest;
use App\Request\UserOfferRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller]
#[Middlewares([JwtAuthMiddleware::class])]
class OfferController extends AbstractController
{
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
     * 用户查看报价.
     */
    #[GetMapping(path: 'getOffers')]
    public function getOffers(): \Psr\Http\Message\ResponseInterface
    {
        $request = $this->container->get(UserOfferRequest::class);
        $request->scene(UserOfferRequest::SCENE_LIST);
        $request->validateResolved();
        $data = $request->getOffers();
        return $this->success($data);
    }

    /**
     * 用户接受报价.
     */
    #[PostMapping(path: 'acceptOffer')]
    public function acceptOffer(): \Psr\Http\Message\ResponseInterface
    {
        $request = $this->container->get(UserOfferRequest::class);
        $request->scene(UserOfferRequest::SCENE_ACCEPT_OFFER);
        $request->validateResolved();
        $data = $request->acceptOffer();
        return $this->success($data);
    }

    /**
     * 我的报价.
     */
    #[GetMapping(path: 'myOffer')]
    public function myOffer(): \Psr\Http\Message\ResponseInterface
    {
        $request = $this->container->get(UserOfferRequest::class);
        $request->scene(UserOfferRequest::SCENE_MY_OFFER);
        $data = $request->myOffer();
        return $this->success($data);
    }

    /**
     * 修改报价.
     */
    #[PostMapping(path: 'modifyOffer')]
    public function modifyOffer(): \Psr\Http\Message\ResponseInterface
    {
        $request = $this->container->get(UserOfferRequest::class);
        $request->scene(UserOfferRequest::SCENE_MODIFY_OFFER);
        $request->validateResolved();
        $data = $request->modifyOffer();
        return $this->success($data);
    }
}
