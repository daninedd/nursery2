<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Model;

/**
 * @property int $id
 * @property string $title 标题
 * @property int $no 编号
 * @property int $product_id 产品id
 * @property string $product_snapshot 产品快照
 * @property int $category_id 品类id
 * @property array $category_snapshot 品类快照
 * @property array $format_name 转成nursery的名字
 * @property string $meter_diameter 米径
 * @property string $ground_diameter 地径
 * @property string $height 高度
 * @property string $crown 冠幅
 * @property string $unit 计价单位
 * @property string $price 本月装车价
 * @property string $last_price 上月装车价
 * @property string $belong 所属年月份
 * @property string $term 期数
 * @property string $publish_department 发布单位
 * @property string $publish_link 发布连接
 * @property string $publish_time 发布时间
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class MarketQuotation extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'market_quotations';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'no' => 'integer', 'product_id' => 'integer',
        'category_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}
