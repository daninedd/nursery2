<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Factory;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Logger\LoggerFactory;

class StdoutLoggerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return $container->get(LoggerFactory::class)->get('log', 'app');
    }
}
