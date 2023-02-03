<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Middleware;

use Hyperf\Cache\Cache;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Phper666\JWTAuth\JWT;
use Phper666\JWTAuth\Util\JWTUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SocketJwtAuthMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected Cache $cache;

    protected HttpResponse $response;

    protected JWT $jwt;

    public function __construct(HttpResponse $response, JWT $jwt)
    {
        $this->response = $response;
        $this->jwt = $jwt;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getQueryParams();
        $token = $params['token'] ?? '';
        $isValidToken = false;
        if (strlen($token) > 0) {
            try {
                if ($this->jwt->verifyToken($token)) {
                    $isValidToken = true;
                }
            } catch (\Exception $e) {
                var_dump($e->getTraceAsString());
                return $this->response->raw('forbidden');
            }
        }
        if ($isValidToken) {
            // $jwtData = JWTUtil::getParser()->parse($token)->claims()->all();
            return $handler->handle($request);
        }
        return $this->response->raw('forbidden');
    }
}
