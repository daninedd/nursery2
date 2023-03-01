<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Exception\BusinessException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    #[Inject]
    protected StdoutLoggerInterface $logger;

    public function success($data, $message = 'success')
    {
        $code = $this->response->getStatusCode();
        return $this->response->json(['message' => $message, 'code' => $code, 'data' => $data]);
    }

    public function failed($message = '请求错误!', $code = 500, $data = [])
    {
        throw new BusinessException($code, $message);
    }
}
