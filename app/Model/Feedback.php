<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Model;

/**
 * @property string $id
 * @property string $user_id
 * @property string $content 反馈的问题
 * @property string $reply_content 回复内容
 * @property string $reply_user
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at 修改时间
 * @property string $reply_at
 * @property int $is_adopt 是否采用
 * @property string $question_medias 问题截图
 * @property string $reply_medias 回复图片
 * @property string $phone 联系方式
 * @property string $type 信息错误/缺失:info_miss 功能建议:advice 程序问题:program 其他问题:other
 * @property User $getUser
 */
class Feedback extends Model
{
    public const FEEDBACK_TYPE_INFO_MISS = 'info_miss';

    public const FEEDBACK_TYPE_ADVICE = 'advice';

    public const FEEDBACK_TYPE_PROGRAM = 'program';

    public const FEEDBACK_TYPE_OTHER = 'other';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'feedbacks';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'string', 'user_id' => 'string', 'reply_user' => 'string', 'created_at' => 'datetime', 'is_adopt' => 'integer', 'updated_at' => 'datetime', 'question_medias' => 'array', 'reply_medias' => 'array'];

    public function getUser(): \Hyperf\Database\Model\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }
}
