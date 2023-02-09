<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $parent_id
 * @property int $order
 * @property int $level
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 */
class Address extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'address';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'parent_id' => 'integer', 'order' => 'integer', 'level' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    protected array $hidden = ['order', 'level', 'status', 'created_at', 'updated_at', 'deleted_at'];

    public function children(): \Hyperf\Database\Model\Relations\HasMany
    {
        return $this->hasMany(Address::class, 'parent_id', 'id');
    }
}
