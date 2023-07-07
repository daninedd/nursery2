<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Job;

use App\Model\Purchase;
use App\Model\Supply;
use App\Model\User;
use Hyperf\AsyncQueue\Job;

class LoginInfoJob extends Job
{

    public $userId;
    public $ip;

    /**
     * 任务执行失败后的重试次数，即最大执行次数为 $maxAttempts+1 次
     */
    protected int $maxAttempts = 2;

    public function __construct($userId, $ip)
    {
        // 这里最好是普通数据，不要使用携带 IO 的对象，比如 PDO 对象
        $this->userId = $userId;
        $this->ip = $ip;
    }

    public function handle()
    {
        $user = User::findFromCache($this->userId);
        if ($user){
            $user->last_visit_at = date('Y-m-d H:i:s');
            $user->last_login_ip = $this->ip;
            $user->save();
        }
    }
}
