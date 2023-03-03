<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Request;

use App\Exception\BusinessException;
use App\Model\Feedback;
use App\Model\User;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class FeedbackRequest extends FormRequest
{
    public const SCENE_LIST = 'list';

    public const SCENE_FEEDBACK = 'feedback';

    public const SCENE_DELETE = 'delete';

    public array $scenes = [
        'feedback' => ['content', 'question_medias', 'phone', 'type'],
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
        $userId = $this->getRequest()->getAttribute('userId');
        return [
            'content' => ['required', 'max:300'],
            'question_medias' => ['array'],
            'phone' => ['nullable', 'regex:/^1[34578]\d{9}$/'],
            'type' => ['required', Rule::in([Feedback::FEEDBACK_TYPE_ADVICE, Feedback::FEEDBACK_TYPE_OTHER, Feedback::FEEDBACK_TYPE_PROGRAM, Feedback::FEEDBACK_TYPE_INFO_MISS])],
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => '反馈用户',
            'content' => '反馈的问题',
            'question_medias' => '反馈的问题媒体',
            'phone' => '手机号',
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }

    /**
     * 问题反馈.
     */
    public function feedback()
    {
        $validatedData = $this->validated();
        $userId = $this->getRequest()->getAttribute('userId');
        $feedback = new Feedback();
        $feedback->user_id = $userId;
        $feedback->type = $validatedData['type'];
        $feedback->phone = $validatedData['phone'];
        $feedback->content = $validatedData['content'];
        $feedback->question_medias = $validatedData['question_medias'];
        if ($feedback->save()) {
            return $feedback->id;
        }
        throw new BusinessException(500, '反馈问题失败');
    }
}
