<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Middleware\JwtAuthMiddleware;
use App\Request\FeedbackRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller]
#[Middlewares([JwtAuthMiddleware::class])]
class FeedbackController extends AbstractController
{
    /**
     *问题反馈.
     */
    #[PostMapping(path: 'feedback')]
    public function feedback()
    {
        $request = $this->container->get(FeedbackRequest::class);
        $request->scene(FeedbackRequest::SCENE_FEEDBACK);
        $request->validateResolved();
        $data = $request->feedback();
        return $this->success($data);
    }
}
