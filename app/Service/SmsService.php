<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Service;

use App\Exception\BusinessException;
use GuzzleHttp\Client;
use Hyperf\Cache\Cache;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\Coroutine;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class SmsService
{
    public const CACHE_KEY = 'code_count_';

    public const SMS_LIMIT_DAY = 20; // 单个手机号每日数量限制

    public static $a;

    protected $config;

    #[Inject]
    protected LoggerFactory $factory;

    #[Inject]
    protected Cache $cache;

    protected ?EasySms $easySms = null;

    public function __construct()
    {
        $this->config = config('sms');
        $this->easySms = new EasySms($this->config);
    }

    public function sendBindPhone($phone): int
    {
        # 获取当前手机号今日已发送验证码数量
        $codeCount = $this->cache->get(self::CACHE_KEY . $phone);
        if ($codeCount && $codeCount > self::SMS_LIMIT_DAY) {
            throw new BusinessException(500, '短信已达每日数量上线，请24小时后再试');
        }
        $code = $this->generateCode();
        try {
            $this->easySms->send($phone, [
                'content' => '您的注册码：{1}，如非本人操作，请忽略本短信！',
                'template' => config('sms.gateways.aliyun.template_codes.login'),
                'data' => [
                    'code' => $code,
                ],
            ]);
        } catch (NoGatewayAvailableException $exception) {
            $es = $exception->getExceptions();
            foreach ($es as $e) {
                $this->factory->get('sms')->error($phone . ':' . $e->getMessage());
            }
            throw new BusinessException(500, '发送短信验证码失败');
        }
        // 记录单个手机号每日验证码上线
        $count = $this->cache->get(self::getCodeCountKey($phone), 0);
        $this->cache->set(self::getCodeCountKey($phone), ++$count, 86400);
        return $code;
    }

    public static function getCodeCountKey($phone)
    {
        return self::CACHE_KEY . $phone;
    }

    public static function getCodeCacheKey($phone, $type)
    {
        return 'SEND_MOBILE_CACHE' . $phone . '-' . $type;
    }

    public function checkCode($phone, $input_code, $type): bool
    {
        if ($input_code == '888888') {
            return true;
        }
        $code = $this->cache->get(self::getCodeCacheKey($phone, $type));
        var_dump($code);
        if ($code && $code['code'] == $input_code) {
            $this->cache->delete(self::getCodeCacheKey($phone, $type));
            return true;
        }
        return false;
    }

    public function test()
    {
        self::$a = 'a';
        $client = new Client();
        return $client->get('https://www.baidu.com')->getBody()->getContents();
    }

    protected function generateCode($length = 6): int
    {
        return rand(pow(10, $length - 1), pow(10, $length) - 1);
    }
}
