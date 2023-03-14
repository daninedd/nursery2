<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Model;

use Carbon\Carbon;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Snowflake\Concern\Snowflake;

/**
 * @property string $id
 * @property string $title 求购标题
 * @property string $product_name 产品名称
 * @property string $product_nickname 产品简称
 * @property int $product_id 产品id
 * @property string $product_snapshot 产品快照
 * @property int $category_id 品类id
 * @property string $category_name 品类名称
 * @property array $category_snapshot 品类快照
 * @property string $user_id 用户id
 * @property string $contact 联系电话
 * @property array $specs 产品参数
 * @property string $target_price 心理预期价格
 * @property int $show_target_price 是否显示心理价位
 * @property int $push_status 发布状态
 * @property int $recommend_status 推荐状态
 * @property int $verify_status 审核状态
 * @property int $sort 排序
 * @property string $remark 求购描述
 * @property int $offer_count 报价条数
 * @property int $must_have_price 报价是否必填价格
 * @property int $must_have_image 报价是否必填图片
 * @property int $must_have_addr 报价是否必填地址
 * @property int $unit 单位 1:株,2:颗,3:丛,4:斤,5:吨,6:芽,7:个,8:两
 * @property int $price_type 1-上车价,2-到货价
 * @property int $num 求购数量
 * @property string $address 求购地址
 * @property int $access_offer 0:未接受报价,1:接受报价
 * @property int $visit_count 访问数量
 * @property string $expire_at 截止日期
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property array $medias 媒体文档集合
 * @property mixed $skus
 * @property User $user
 */
class Purchase extends Model
{
    use Snowflake;
    use SoftDeletes;

    public const PUSH_STATUS_ENABLE = 1;

    public const PUSH_STATUS_DISABLE = 0;

    public const IS_EXPIRED = 1;

    public const NOT_EXPIRED = 0;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'purchases';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'string', 'product_id' => 'integer', 'category_id' => 'integer', 'user_id' => 'string', 'show_target_price' => 'integer', 'push_status' => 'integer', 'recommend_status' => 'integer', 'verify_status' => 'integer', 'sort' => 'integer', 'offer_count' => 'integer', 'must_have_price' => 'integer', 'must_have_image' => 'integer', 'must_have_addr' => 'integer', 'unit' => 'integer', 'price_type' => 'integer', 'product_snapshot' => 'array', 'category_snapshot' => 'array', 'specs' => 'array', 'address' => 'array', 'medias' => 'array', 'num' => 'integer', 'access_offer' => 'integer', 'visit_count' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    protected array $hidden = ['product_id', 'push_status', 'recommend_status', 'verify_status', 'sort', 'access_offer', 'target_price', 'show_target_price', 'offer_count', 'product_snapshot', 'category_snapshot', 'ambiguous_price', 'deleted_at'];

    protected array $appends = ['skus', 'is_expired'];

    public function asJson($value): string|false
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getMediasAttribute($value)
    {
        $value = json_decode($value, true);
        $prefix = config('file.storage.oss.prefix');
        foreach ($value as $k => $item) {
            $value[$k]['url'] = $prefix . '/' . $item['path'];
        }
        return $value;
    }

    public function getProgressAttribute(): float|int
    {
        $show = $this->specs['show'] ?? [];
        $hiddens = $this->specs['hiddens'] ?? [];
        $all = array_merge($show, $hiddens);
        $has_value = array_filter(array_column($all, 'has_value'));
        $progress = count($has_value) / count($all);
        $progress = round($progress, 2);
        return 100 * $progress;
    }

    public function getSkusAttribute(): array
    {
        $re = [];
        foreach ($this->specs['show'] as $spec) {
            if ($spec['has_value']) {
                $re[] = ['key' => $spec['label'], 'value' => $spec['value_text']];
            }
        }
        return $re;
    }

    public function getIsExpiredAttribute(): bool
    {
        return Carbon::now()->gt($this->expire_at);
    }

    public function getDefaultUrlAttribute()
    {
        return $this->category_snapshot['icon'] ?: env('STATIC_PREFIX') . '/static/images/123.jpg';
    }

    public function getTargetPriceAttribute($value): float
    {
        return floatval($value);
    }

    public function getContactAttribute($value): array|string
    {
        return substr_replace($value, '*******', 4, 7);
    }

    public function getHasEnshrineAttribute()
    {
        $user = $this->getContainer()->get(RequestInterface::class)->getAttribute('userId');
        return Enshrine::where([['user_id', $user], ['type', Enshrine::TYPE_PURCHASE], ['item_id', $this->id]])->value('id');
    }

    /** 是否报过价 */
    public function getHasOfferAttribute()
    {
        $user = $this->getContainer()->get(RequestInterface::class)->getAttribute('userId');
        return UserOffer::where([['user_id', $user], ['purchase_id', $this->id]])->value('id');
    }

    public function getMustHaveAttribute($value): array
    {
        $r = [];
        if ($this->must_have_image) {
            $r[] = 'must_have_image';
        }
        if ($this->must_have_addr) {
            $r[] = 'must_have_addr';
        }
        if ($this->must_have_price) {
            $r[] = 'must_have_price';
        }
        return $r;
    }

    public function user(): \Hyperf\Database\Model\Relations\HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function offers(): \Hyperf\Database\Model\Relations\HasMany
    {
        return $this->hasMany(UserOffer::class, 'purchase_id', 'id');
    }
}
