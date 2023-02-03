<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Middleware\JwtAuthMiddleware;
use App\Request\EnshrineRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller]
#[Middlewares([JwtAuthMiddleware::class])]
class EnshrineController extends AbstractController
{
    /**
     *收藏.
     */
    #[PostMapping(path: 'enshrine')]
    public function enshrine()
    {
        $request = $this->container->get(EnshrineRequest::class);
        $request->scene(EnshrineRequest::SCENE_ENSHRINE);
        $request->validateResolved();
        $data = $request->enshrine();
        return $this->success($data);
    }

    /**
     * 取消收藏.
     */
    #[PostMapping(path: 'deleteEnshrine')]
    public function deleteEnshrine(): \Psr\Http\Message\ResponseInterface
    {
        $request = $this->container->get(EnshrineRequest::class);
        $request->scene(EnshrineRequest::SCENE_DELETE);
        $request->validateResolved();
        $data = $request->deleteEnshrine();
        return $this->success($data);
    }

    /**
     * 收藏列表.
     */
    #[GetMapping(path: 'getList')]
    public function getList(): \Psr\Http\Message\ResponseInterface
    {
        $request = $this->container->get(EnshrineRequest::class);
        $request->scene(EnshrineRequest::SCENE_LIST);
        $request->validateResolved();
        $data = $request->getList();
        return $this->success($data);
    }
}
