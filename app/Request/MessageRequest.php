<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Request;

use App\Exception\BusinessException;
use App\Model\Purchase;
use App\Model\Supply;
use App\Model\User;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class MessageRequest extends FormRequest
{
    public const SCENE_CREATE_CONVERSATION = 'create_conversation';

    public array $scenes = [
        'create_conversation' => ['contact', 'item_id', 'type'],
        'bindUserinfo' => ['nickname', 'avatarUrl'],
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['purchase', 'supply'])],
            'contact' => ['required', Rule::exists('users', 'id')],
            'item_id' => ['required', function ($attr, $value, $fail) {
                if ($this->validationData()['type'] == 'supply') {
                    if (! Supply::findFromCache($value)->exists) {
                        $fail('产品不存在');
                    }
                } elseif ($this->validationData()['type'] == 'purchase') {
                    if (! Purchase::findFromCache($value)->exists) {
                        $fail('供应信息不存在');
                    }
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }

    /**
     * 创建|获取会话信息.
     */
    public function createConversation()
    {
        $data = $this->validated();
        /** @var User $user */
        $user = User::findFromCache($this->getRequest()->getAttribute('userId'));
        $user->name = $data['nickname'];
        $user->avatar = $data['avatarUrl'];
        $user->member_status = User::VIP;
        if ($user->save()) {
            return [
                'user_id' => $user->id,
                'username' => $user->name,
                'member_status' => $user->member_status,
                'avatar_url' => $user->full_avatar,
            ];
        }
        throw new BusinessException(500, '更新个人信息失败');
    }
}
