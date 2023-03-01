<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Model;

use Hyperf\Snowflake\Concern\Snowflake;

/**
 * @property string $id
 * @property string $user_id
 * @property int $type
 * @property string $item_id
 * @property array $item_snapshot
 * @property \Carbon\Carbon $created_at
 */
class Enshrine extends Model
{
    use Snowflake;

    public const TYPE_SUPPLY = 1;

    public const TYPE_PURCHASE = 2;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'enshrines';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'string', 'user_id' => 'string', 'type' => 'integer', 'item_id' => 'string', 'item_snapshot' => 'array', 'created_at' => 'datetime'];

    protected array $hidden = ['item_snapshot'];

    public function getShowItemAttribute()
    {
        return ['id' => $this->item_snapshot['id'] ?? null, 'num' => $this->item_snapshot['num'] ?? '', 'skus' => $this->item_snapshot['skus'] ?? '', 'title' => $this->item_snapshot['title'] ?? '', 'medias' => $this->item_snapshot['medias'] ?? '', 'address' => $this->item_snapshot['address'] ?? '', 'visit_count' => $this->item_snapshot['visit_count'] ?? '', 'product_name' => $this->item_snapshot['product_name'] ?? '', 'created_at' => $this->item_snapshot['created_at'] ?? '', 'expire_at' => $this->item_snapshot['expire_at'] ?? null];
    }

    public function asJson($value): string|false
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getDefaultUrlAttribute(): string
    {
        return env('STATIC_PREFIX') . '/static/images/123.jpg';
    }
}
