<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Job;

use App\Model\Purchase;
use App\Model\Supply;
use Hyperf\AsyncQueue\Job;

class CounterVisitJob extends Job
{
    public const TYPE_SUPPLY = 'supply';

    public const TYPE_PURCHASE = 'purchase';

    public $type;

    public $id;

    /**
     * 任务执行失败后的重试次数，即最大执行次数为 $maxAttempts+1 次
     */
    protected int $maxAttempts = 2;

    public function __construct($type, $id)
    {
        // 这里最好是普通数据，不要使用携带 IO 的对象，比如 PDO 对象
        $this->type = $type;
        $this->id = $id;
    }

    public function handle()
    {
        if ($this->type == self::TYPE_SUPPLY) {
            $supply = Supply::findFromCache($this->id);
            $supply->timestamps = false;
            $supply->increment('visit_count');
        } elseif ($this->type == self::TYPE_PURCHASE) {
            $purchase = Purchase::findFromCache($this->id);
            $purchase->timestamps = false;
            $purchase->increment('visit_count');
        }
    }
}
