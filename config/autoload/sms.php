<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
return [
    // HTTP 请求的超时时间（秒）
    'timeout' => 5.0,
    // 默认发送配置
    'default' => [
        // 网关调用策略，默认：顺序调用
        'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,
        // 默认可用的发送网关
        'gateways' => [
            'aliyun',
        ],
    ],
    // 可用的网关配置
    'gateways' => [
        'errorlog' => [
            'file' => 'runtime/logs/sms-error.log',
        ],
        'aliyun' => [
            'access_key_id' => 'LTAI5tPAE4aMoePRYiHyefPf', // hckj:LTAI4GJRbgibb3JZC1PohnWH
            'access_key_secret' => 'Hde82GaX6xSVnm0jkRr6BFkaIPEomP', // hckj:gwH2SK2hWLJoNbLXdCltYm2Tt0eplt
            'sign_name' => '小强购物',
            'template_codes' => [
                'login' => 'SMS_212640438',
            ],
        ],

        // ...
    ],
];
