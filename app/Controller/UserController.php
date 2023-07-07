<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Middleware\JwtAuthMiddleware;
use App\Model\User;
use App\Request\UserRequest;
use App\Utils\Util;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Cache\Cache;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;
use Phper666\JWTAuth\JWT;

#[Controller]
#[Middlewares([JwtAuthMiddleware::class])]
class UserController extends AbstractController
{
    #[Inject]
    protected JWT $jwt;

    #[Inject]
    protected Cache $cache;

    #[PostMapping(path: 'wxLogin')]
    public function wxLogin()
    {
        $code = $this->request->post('code');
        if (empty($code)) {
            throw new BusinessException(ErrorCode::VALIDATE_ERROR);
        }
//        $user = User::find('531451833013690370');
//
//        $userProfile = [
//            'user_id' => $user->id,
//            'username' => $user->name,
//            'member_status' => $user->member_status,
//            'avatar_url' => $user->full_avatar,
//        ];
//        $token = $this->jwt->getToken('default', $userProfile);
//        return $token->toString();
        $appId = env('APP_ID');
        $appSecret = env('APP_SECRET');
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appId}&secret={$appSecret}&js_code={$code}&grant_type=authorization_code";
        try {
            $client = new Client();
            $response = $client->get($url);
            $result = $response->getBody()->getContents();
            $result = json_decode($result, true);
            if ($result && key_exists('openid', $result) && key_exists('session_key', $result)) {
                $openid = $result['openid'];
                $session_key = $result['session_key'];
                $realIp = Util::getRealIp();
                $user = User::firstOrCreate(['open_id' => $openid], [
                    'name' => '微信用户',
                    'member_status' => User::GUEST,
                    'register_ip' => $realIp,
                    'last_Login_ip' => $realIp,
                    'last_visit_at' => date('Y-m-d H:i:s'),
                ]);
                $userProfile = ['user_id' => $user->id, 'username' => $user->name, 'member_status' => $user->member_status, 'avatar_url' => $user->full_avatar];
                $token = $this->jwt->getToken('default', $userProfile);
                // session_key存缓存
                $this->cache->set('session_key:' . $user->id, $session_key, 86400);
                $returnData = ['token' => $token->toString(), 'expire' => $this->jwt->getTTL($token->toString()), 'userProfile' => $userProfile];
                return $this->success($returnData, '登录成功');
            }
            return $this->failed('invalid_code', ErrorCode::VALIDATE_ERROR);
        } catch (GuzzleException $e) {
            $this->logger->error($e->getTraceAsString());
            return $this->failed();
        }
    }

    /**
     *绑定手机号.
     */
    #[PostMapping(path: 'bindPhone')]
    public function bindPhone()
    {
        $request = $this->container->get(UserRequest::class);
        $request->scene(UserRequest::SCENE_BIND_PHONE);
        $request->validateResolved();
        $data = $request->bindPhone();
        return $this->success($data);
    }

    /**
     *绑定用户信息.
     */
    #[PostMapping(path: 'bindUserinfo')]
    public function bindUserinfo()
    {
        $request = $this->container->get(UserRequest::class);
        $request->scene(UserRequest::SCENE_BIND_USERINFO);
        $request->validateResolved();
        $data = $request->bindUserinfo();
        return $this->success($data);
    }

    /**
     *获取用户头像和昵称.
     */
    #[GetMapping(path: 'getUserinfo')]
    public function getUserinfo(): \Psr\Http\Message\ResponseInterface
    {
        $userId = $this->request->getAttribute('userId');
        $user = User::findFromCache($userId);
        $data = ['avatar' => $user->avatar, 'nickname' => $user->name, 'full_avatar' => $user->full_avatar];
        return $this->success($data);
    }

    /**
     *获取用户虚拟号码
     */
    #[GetMapping(path: 'getPhoneNumber')]
    public function getPhoneNumber(): \Psr\Http\Message\ResponseInterface
    {
        $userId = $this->request->query('user_id');
        $selfUserId = $this->request->getAttribute('userId');
        $self = User::findFromCache($selfUserId);
        if ($self->member_status != User::VIP) {
            throw new BusinessException(ErrorCode::PROFILE_ERROR, '请先完善资料~');
        }
        if ($userId) {
            $user = User::findFromCache($userId);
            if ($user->phone) {
                return $this->success(['v_phone' => $user->phone]);
            }
            throw new BusinessException(400, '用户还未绑定手机号');
        } else {
            throw new BusinessException(400, '用户id不正确');
        }
    }
}
