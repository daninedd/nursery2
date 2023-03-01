<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;

class WxService
{
    protected $appId;

    protected $appSecret;

    protected $tokenFile = 'access_token.json';

    #[Inject]
    protected StdoutLoggerInterface $logger;

    public function __construct()
    {
        $this->appId = env('APP_ID');
        $this->appSecret = env('APP_SECRET');
    }

    public function getAccessToken()
    {
        // 获取url
        if (! file_exists($this->tokenFile)) {
            $token = $this->getOnlineToken();
        } else {
            $fileInfo = explode(',', file_get_contents($this->tokenFile));
            if (! empty(trim($fileInfo[0])) && $fileInfo[1] >= time()) {
                $token = $fileInfo[0];
            } else {
                $token = $this->getOnlineToken();
            }
        }
        return $token;
    }

    public function getPhoneNumber($code)
    {
        $client = new Client([
            'timeout' => 60,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $access_token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token={$access_token}";
        try {
            $result = $client->request('POST', $url, [
                'json' => ['code' => $code],
                'headers' => ['Accept' => 'application/json'],
            ]);
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
        $response = $result->getBody()->getContents();
        $response = $response ?: [];
        $return = json_decode($response, true);
        if ($return && isset($return['phone_info']['purePhoneNumber']) && $return['phone_info']['purePhoneNumber']) {
            return $return['phone_info']['purePhoneNumber'];
        }
        return false;
    }

    /**
     * 获取token.
     */
    private function getOnlineToken()
    {
        $client = new Client([
            'timeout' => 60,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $url = "https://api.weixin.qq.com/cgi-bin/token?appid={$this->appId}&secret={$this->appSecret}&grant_type=client_credential";
        try {
            $result = $client->request('GET', $url);
            $result = $result->getBody()->getContents();
            if ($result) {
                $token = $this->jsonDecode($result, 'access_token');
                $text = $token . ',' . (time() + 7000);

                // 写入文件中
                $file = $this->tokenFile;

                if (! file_exists($file)) {
                    fopen($file, 'wb');
                }
                // 把值存入文件中
                $myfile = fopen($file, 'w');
                fwrite($myfile, $text); // 写入文件
                fclose($myfile); // 关闭文件
                return $token;
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getTraceAsString());
        }
        return null;
    }

    /**
     * json数据处理.
     * @param mixed $data
     * @param mixed $key
     */
    private function jsonDecode($data, $key)
    {
        $new_data = json_decode($data, true);

        if (array_key_exists($key, $new_data)) {
            return $new_data[$key];
        }
        return $data;
    }
}
