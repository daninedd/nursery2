<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Request;

use App\Exception\BusinessException;
use App\Model\Enshrine;
use App\Model\Purchase;
use App\Model\Supply;
use App\Model\User;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class EnshrineRequest extends FormRequest
{
    public const SCENE_LIST = 'list';

    public const SCENE_ENSHRINE = 'enshrine';

    public const SCENE_DELETE = 'delete';

    public array $scenes = [
        'enshrine' => ['type', 'item_id'],
        'list' => ['type'],
        'delete' => ['delete_id'],
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
        $rules = [
            'type' => ['required', Rule::in([Enshrine::TYPE_SUPPLY, Enshrine::TYPE_PURCHASE])],
            'item_id' => ['required', function ($attr, $value, $fail) use ($userId) {
                if (Enshrine::where([['user_id', $userId], ['type', $this->input('type')], ['item_id', $value]])->exists()) {
                    $fail('已经收藏了该项目');
                }
                if ($this->input('type') == Enshrine::TYPE_SUPPLY && Supply::where([
                    ['id', $value],
                    ['push_status', Supply::PUSH_STATUS_ENABLE],
                ])
                    ->doesntExist()) {
                    $fail('供应信息不存在');
                } elseif ($this->input('type') == Enshrine::TYPE_PURCHASE && Purchase::where(
                    [
                        ['id', $value],
                        ['push_status', Supply::PUSH_STATUS_ENABLE],
                        ['expire_at', '>=', date('Y-m-d')],
                    ]
                )->doesntExist()) {
                    $fail('采购信息不存在');
                }
            }],
            'delete_id' => [
                'required', Rule::exists('enshrines', 'id')->where('user_id', $userId),
            ],
        ];
        return $rules;
    }

    public function attributes(): array
    {
        return [
            'user_id' => '收藏用户',
            'type' => '收藏类型',
            'item_id' => '收藏产品',
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }

    /**
     * 收藏.
     */
    public function enshrine()
    {
        $validatedData = $this->validated();
        $userId = $this->getRequest()->getAttribute('userId');
        $enshrine = new Enshrine();
        $enshrine->user_id = $userId;
        $enshrine->type = $validatedData['type'];
        $enshrine->item_id = $validatedData['item_id'];
        $enshrine->item_snapshot = $validatedData['type'] == Enshrine::TYPE_SUPPLY ? Supply::findFromCache($validatedData['item_id']) : Purchase::findFromCache($validatedData['item_id']);
        if ($enshrine->save()) {
            return $enshrine->id;
        }
        throw new BusinessException(500, '收藏失败');
    }

    /**
     * 取消收藏.
     */
    public function deleteEnshrine()
    {
        $validatedData = $this->validated();
        $delete_id = $validatedData['delete_id'];
        if (Enshrine::destroy($delete_id)) {
            return $delete_id;
        }
        throw new BusinessException(500, '取消收藏失败');
    }

    /**
     * 收藏列表.
     */
    public function getList()
    {
        $userId = $this->getRequest()->getAttribute('userId');
        $query = Enshrine::where([['user_id', $userId], ['type', $this->validationData()['type']]])->orderBy('id', 'desc');
        $results = $query->paginate(10);
        $results->each(function ($enshrine) {
            $enshrine->append(['show_item', 'default_url']);
        });
        return $results;
    }
}
