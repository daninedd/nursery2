<?php

declare (strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Model;

use Hyperf\Cache\Cache;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\ModelCache\Cacheable;
use Hyperf\Snowflake\Concern\Snowflake;
/**
 * @property string $id
 * @property string $title
 * @property string $product_name
 * @property int $product_id
 * @property int $category_id
 * @property array $category_snapshot
 * @property array $product_snapshot
 * @property string $user_id
 * @property string $contact
 * @property array $specs
 * @property string $lowest_price
 * @property string $highest_price
 * @property int $ambiguous_price
 * @property int $price_type
 * @property int $push_status
 * @property int $recommend_status
 * @property int $verify_status
 * @property int $sort
 * @property string $description
 * @property int $num
 * @property int $visit_count
 * @property array $address
 * @property int $unit 1:株,2:颗,3:丛,4:斤,5:吨,6:芽,7:个,8:两
 * @property string $expire_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property mixed $medias
 * @property mixed $skus
 * @property User $user
 */
class Supply extends Model
{
    use Snowflake;
    use Cacheable;
    public const PUSH_STATUS_ENABLE = 1;
    public const PUSH_STATUS_DISABLE = 0;
    #[Inject]
    protected Cache $cache;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'supplies';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = ['id' => 'string', 'product_id' => 'integer', 'category_id' => 'integer', 'user_id' => 'string', 'ambiguous_price' => 'integer', 'price_type' => 'integer', 'product_snapshot' => 'array', 'category_snapshot' => 'array', 'specs' => 'array', 'address' => 'array', 'medias' => 'array', 'push_status' => 'integer', 'recommend_status' => 'integer', 'verify_status' => 'integer', 'sort' => 'integer', 'num' => 'integer', 'visit_count' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    protected array $hidden = ['product_id', 'category_id', 'push_status', 'recommend_status', 'verify_status', 'expire_at', 'updated_at', 'deleted_at', 'product_snapshot', 'category_snapshot', 'ambiguous_price'];
    protected array $appends = ['skus'];
    public function asJson($value):string|false
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
    public function getMediasAttribute($value)
    {
        $value = json_decode($value, true);
        $prefix = config('file.storage.oss.prefix');
        foreach ($value as $k => $item) {
            $value[$k]['url'] = $prefix . $item['path'];
        }
        return $value;
    }
    public function getSkusAttribute()
    {
        $re = [];
        foreach ($this->specs['show'] as $spec) {
            if ($spec['has_value']) {
                $re[] = ['key' => $spec['label'], 'value' => $spec['value_text']];
            }
        }
        return $re;
    }
    public function getProgressAttribute() : float|int
    {
        $show = $this->specs['show'] ?? [];
        $hiddens = $this->specs['hiddens'] ?? [];
        $all = array_merge($show, $hiddens);
        $has_value = array_filter(array_column($all, 'has_value'));
        $progress = count($has_value) / count($all);
        $progress = round($progress, 2);
        return 100 * $progress;
    }
    public function getCategoryAttribute()
    {
        return $this->product_name ?: $this->category_snapshot['name'];
    }
    public function getLowestPriceAttribute($value)
    {
        return floatval($value);
    }
    public function getHighestPriceAttribute($value)
    {
        return floatval($value);
    }
    public function getHasRefreshAttribute()
    {
        return $this->cache->has(self::genRefreshCacheKey($this->id));
    }
    public function getHasEnshrineAttribute()
    {
        $user = $this->getContainer()->get(RequestInterface::class)->getAttribute('userId');
        return Enshrine::where([['user_id', $user], ['type', Enshrine::TYPE_SUPPLY], ['item_id', $this->id]])->value('id');
    }
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public static function genRefreshCacheKey($supply_id)
    {
        return 'refresh:' . $supply_id;
    }
}