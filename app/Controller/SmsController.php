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
use App\Service\SmsService;
use Hyperf\Cache\Cache;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Request;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Rule;
use Phper666\JWTAuth\JWT;

#[Controller]
#[Middlewares([JwtAuthMiddleware::class])]
class SmsController extends AbstractController
{
    public const TYPE_BIND_PHONE = 'bindPhone';

    public const EXPIRE_TIME = 600;

    #[Inject]
    protected JWT $jwt;

    #[Inject]
    protected Cache $cache;

    #[Inject]
    protected SmsService $smsService;

    #[Inject]
    protected ValidatorFactoryInterface $validatorFactory;

    #[PostMapping(path: 'getCode')]
    public function getCode(Request $request)
    {
        $validator = $this->validatorFactory->make(
            $request->all(),
            [
                'type' => ['required', Rule::in([self::TYPE_BIND_PHONE])],
                'phone' => ['required', 'regex:/^1[34578]\d{9}$/', 'bail', function ($attr, $value, $fail) use ($request) {
                    $codeCache = $this->cache->get(SmsService::getCodeCacheKey($value, $request->input('type')));
                    $phoneCount = $this->cache->get(SmsService::getCodeCountKey($value));
                    if ($phoneCount && (($phoneCount + 1) > SmsService::SMS_LIMIT_DAY)) {
                        $fail('验证码已达每日上线，请24小时后再试');
                    }
                    if ($codeCache && key_exists('nextSendTime', $codeCache) && $codeCache['nextSendTime'] > time()) {
                        // throw new BusinessException(ErrorCode::SERVER_ERROR, '发送验证码频繁，请稍后再试');
                        $fail('发送验证码频繁，请稍后再试');
                    }
                }],
            ],
            ['phone.required' => '请填写手机号', 'phone.regex' => '手机号格式不正确']
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            return $this->failed($errorMessage, 422);
        }
        $phone = $request->input('phone');
        $code = $this->smsService->sendBindPhone($phone);
        // 存入redis
        $this->cache->set(SmsService::getCodeCacheKey($phone, $request->input('type')), ['code' => $code, 'nextSendTime' => time() + 60], self::EXPIRE_TIME);
        return $this->success([]);
    }
}
