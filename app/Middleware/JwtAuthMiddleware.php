<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Middleware;

use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Phper666\JWTAuth\JWT;
use Phper666\JWTAuth\Util\JWTUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JwtAuthMiddleware implements MiddlewareInterface
{
    protected HttpResponse $response;

    protected string $prefix = 'Nursery';

    protected JWT $jwt;

    public function __construct(HttpResponse $response, JWT $jwt)
    {
        $this->response = $response;
        $this->jwt = $jwt;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 判断是否为noCheckRoute
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        if ($this->jwt->matchRoute(null, $method, $path)) {
            return $handler->handle($request);
        }
        $isValidToken = false;
        $token = $request->getHeader('Authorization')[0] ?? '';
        if (strlen($token) > 0) {
            $token = ucfirst($token);
            $arr = explode($this->prefix . ' ', $token);
            $token = $arr[1] ?? '';
            try {
                if (strlen($token) > 0 && $this->jwt->verifyToken($token)) {
                    $isValidToken = true;
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
                var_dump($e->getTraceAsString());
                $data = [
                    'code' => 401,
                    'msg' => '对不起，token验证没有通过',
                    'data' => [],
                ];
                return $this->response->json($data);
            }
        }
        if ($isValidToken) {
            $jwtData = JWTUtil::getParser()->parse($token)->claims()->all();

            // 更改上下文，写入用户信息
            // User模型自行创建
            $request = Context::get(ServerRequestInterface::class);
            $request = $request->withAttribute('userId', $jwtData['user_id']);
            Context::set(ServerRequestInterface::class, $request);
            return $handler->handle($request);
        }
        $data = [
            'code' => 401,
            'msg' => '对不起，token验证没有通过',
            'data' => [],
        ];
        return $this->response->json($data);
    }
}
