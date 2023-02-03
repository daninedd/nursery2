<?php

declare (strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Model;

use Hyperf\Snowflake\Concern\Snowflake;
/**
 * @property string $id
 * @property string $user_id
 * @property string $purchase_id
 * @property string $offer_phone
 * @property string $offer_price
 * @property array $offer_media
 * @property array $offer_address
 * @property string $purchase_user_id
 * @property int $accept
 * @property string $remark
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserOffer extends Model
{
    use Snowflake;
    public const ACCEPT = '1';
    public const NOT_ACCEPT = '0';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'user_offer';
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
    protected array $casts = ['id' => 'string', 'user_id' => 'string', 'purchase_id' => 'string', 'offer_media' => 'array', 'offer_address' => 'array', 'purchase_user_id' => 'string', 'accept' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    public function getOfferMediaAttribute($value)
    {
        $value = json_decode($value, true);
        $prefix = config('file.storage.oss.prefix');
        foreach ($value as $k => $item) {
            $value[$k]['url'] = $prefix . $item['path'];
        }
        return $value;
    }
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function purchase()
    {
        return $this->hasOne(Purchase::class, 'id', 'purchase_id');
    }
}