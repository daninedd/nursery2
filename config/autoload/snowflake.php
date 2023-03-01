<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\Snowflake\MetaGenerator\RedisMetaGenerator;
use Hyperf\Snowflake\MetaGenerator\RedisMilliSecondMetaGenerator;
use Hyperf\Snowflake\MetaGenerator\RedisSecondMetaGenerator;
use Hyperf\Snowflake\MetaGeneratorInterface;

return [
    'begin_second' => MetaGeneratorInterface::DEFAULT_BEGIN_SECOND,
    RedisMilliSecondMetaGenerator::class => [
        'pool' => 'default',
        'key' => RedisMetaGenerator::DEFAULT_REDIS_KEY,
    ],
    RedisSecondMetaGenerator::class => [
        'pool' => 'default',
        'key' => RedisMetaGenerator::DEFAULT_REDIS_KEY,
    ],
];
