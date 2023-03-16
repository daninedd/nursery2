<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $nick_name
 * @property string $sku
 * @property string $images
 * @property string $tags
 * @property int $category_id
 * @property int $show_status
 * @property int $sort
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property Category $category
 */
class Product extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'products';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'category_id' => 'integer', 'show_status' => 'integer', 'sort' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
}
