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
use Hyperf\Cache\Cache;
use Hyperf\Database\Model\Builder;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class SearchRequest extends FormRequest
{
    public const SCENE_SEARCH = 'search';

    public const SCENE_GET_HOT_SEARCH = 'get_hot_search';

    public array $scenes = [
        self::SCENE_SEARCH => ['keyword', 'type', 'page'],
        self::SCENE_GET_HOT_SEARCH => [],
    ];

    #[Inject]
    protected Cache $cache;

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
            'keyword' => ['required', 'string', 'max:12'],
            'type' => ['required', Rule::in(['supply', 'purchase'])],
            'page' => ['required', 'int'],
        ];
    }

    public function messages(): array
    {
        return [];
    }

    /**
     * 搜索.
     */
    public function search()
    {
        $validateData = $this->validated();
        $keyword = $validateData['keyword'];
        if ($validateData['type'] == 'supply') {
            $query = Supply::query()->where([['push_status', Supply::PUSH_STATUS_ENABLE], ['deleted_at', null]]);
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('product_name', 'like', "%{$keyword}%")
                    ->orWhereRaw('MATCH (title,product_name) AGAINST (?)', [$keyword]);
            });
            $query->orderBy('updated_at', 'DESC');
            return $query->paginate(20);
        }
        if ($validateData['type'] == 'purchase') {
            $query = Purchase::query()->where([['push_status', Purchase::PUSH_STATUS_ENABLE], ['deleted_at', null]]);
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('product_name', 'like', "%{$keyword}%")
                    ->orWhereRaw('MATCH (title,product_name) AGAINST (?)', [$keyword]);
            });
            $query->orderBy('expire_at', 'DESC');
            return $query->paginate(20);
        }
        throw new BusinessException(500, '查询失败');
    }

    /**
     * 获取热搜词.
     */
    public function getHotSearch()
    {
        return ['皂角', '紫薇', '桂花', '龟甲冬青'];
    }
}
