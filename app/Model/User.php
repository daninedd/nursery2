<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Model;

use Carbon\Carbon;
use Hyperf\Snowflake\Concern\Snowflake;
use Qbhy\HyperfAuth\Authenticatable;

/**
 * @property string $id
 * @property string $name
 * @property string $phone
 * @property string $open_id
 * @property string $avatar
 * @property string $id_card
 * @property int $gender
 * @property int $vip_level
 * @property string $last_visit_at
 * @property int $profile_complete
 * @property string $member_status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property mixed $join_days
 * @property string $full_avatar
 */
class User extends Model implements Authenticatable
{
    use Snowflake;

    public const GUEST = 'guest';

    // 游客
    public const MEMBER = 'member';

    // 绑定了手机号
    public const VIP = 'vip';

    // 手机号和昵称头像全部绑定
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['name', 'avatar', 'open_id', 'member_status'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'string', 'gender' => 'integer', 'vip_level' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'profile_complete' => 'integer'];

    protected array $appends = ['join_days', 'full_avatar'];

    protected array $hidden = ['open_id', 'last_visit_at', 'id_card', 'phone', 'vip_level', 'updated_at', 'deleted_at'];

    public function getId()
    {
        return $this->id;
    }

    public static function retrieveById($key): ?Authenticatable
    {
        return self::find($key);
    }

    public function getJoinDaysAttribute()
    {
        $start = Carbon::parse($this->created_at);
        $end = Carbon::parse('now');
        return $start->diffInDays($end);
    }

    public function getFullAvatarAttribute()
    {
        if ($this->avatar) {
            $prefix = config('file.storage.oss.prefix');
            return $prefix . $this->avatar;
        }
        return env('STATIC_PREFIX') . '/static/images/defaultAvatar.png';
    }
}
