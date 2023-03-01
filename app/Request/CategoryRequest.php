<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Request;

use App\Exception\BusinessException;
use App\Model\Category;
use App\Model\Product;
use App\Model\Supply;
use Carbon\Carbon;
use Hyperf\Database\Query\Builder;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public const SCENE_ADD = 'add';

    public const SCENE_EDIT = 'edit';

    public const SCENE_SEARCH_LIST = 'getSearchList';

    public array $scenes = [
        'getSearchList' => ['keyword'],
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
            'keyword' => ['required', 'max:10'],
            'id' => ['required', Rule::exists('supplies')->where(function (Builder $query) {
                $query->where('push_status', 1);
                if ($this->getScene() == self::SCENE_EDIT) {
                    $query->where('user_id', 1);
                }
            })],
            'title' => 'required|max:32',
            'productId' => 'required|int|exists:products,id',
            'price1' => 'required|numeric|min:0',
            'price2' => 'nullable|numeric|min:0',
            'specs' => ['nullable', 'array', function ($attr, $value, $fail) {
                if (! key_exists('show', $value)
                    || ! key_exists('hiddens', $value)
                    || ! is_array($value['show'])
                    || ! is_array($value['hiddens'])
                ) {
                    $fail("The {$attr} is invalid");
                }
            }],
        ];
    }

    public function messages(): array
    {
        return ['id.exists' => '供应详情不存在'];
    }

    /**
     * 搜索品类列表.
     */
    public function searchList()
    {
        $data = $this->validated();
        $keyword = $data['keyword'];
        $products = Product::select(['id', 'name', 'category_id'])
            ->where('name', 'like', "%{$keyword}%")
            ->orWhere('nick_name', 'like', "%{$keyword}%")
            ->with('category:name,id')
            ->get();
        $result = [];
        if ($products->isEmpty()) {
            $result = $this->getDefaultList();
        } else {
            foreach ($products as $product) {
                $result[] = [
                    'id' => $product->id,
                    'title' => $product->category->name . '->' . $product->name,
                    'value' => $product->name,
                    'categoryId' => $product->category->id,
                ];
            }
        }
        return $result;
    }

    /**
     * 编辑供应.
     */
    public function editSupply()
    {
        $data = $this->validated();
        $supply = Supply::findFromCache($data['id']);
        $supply->title = $data['title'];
        $supply->medias = $this->formatMedia($data['media']);
        $supply->specs = $data['specs'];
        $supply->lowest_price = $data['price1'];
        $supply->highest_price = $data['price2'] ?: $data['price1'];
        $supply->price_type = $data['price_type'];
        $supply->push_status = 1;
        $supply->description = $data['remark'];
        $supply->num = $data['num'];
        $supply->address = $data['address'];
        $supply->expire_at = (new Carbon())->addDays(3)->format('Y-m-d H:i:s');
        if ($supply->save()) {
            return $supply->id;
        }
        throw new BusinessException(500, '更新供应失败');
    }

    public function detail()
    {
        $data = $this->validated();
        return Supply::findFromCache($data['id'])->load('user');
    }

    protected function getDefaultList()
    {
        $list = Category::select(['id', 'name'])->get();
        $re = [];
        foreach ($list as $item) {
            $re[] = [
                'id' => 0,
                'title' => $item->name,
                'value' => $item->name,
                'categoryId' => $item->id,
            ];
        }
        return $re;
    }

    protected function formatMedia($media): array
    {
        $re = [];
        foreach ($media as $media_d) {
            $re[] = ['path' => $media_d['path'], 'type' => $media_d['type']];
        }
        return $re;
    }

    protected function formatSpecs($specs): array
    {
        foreach ($specs['show'] as $k => $specShow) {
            if ($specShow['type'] == 'multi_input' && ! empty($specShow['value1'])) {
                $specs['show'][$k]['has_value'] = true;
                $specs['show'][$k]['value_text'] = $specShow['value1'] .
                    ($specShow['value2'] ? ' - ' . $specShow['value2'] : '') .
                    $specShow['unit'];
            } elseif ($specShow['type'] == 'data_check_box' && ! empty($specShow['value'])) {
                $specs['show'][$k]['has_value'] = true;
                $valueText = array_filter($specShow['values'], function ($v) use ($specShow) {
                    return $v['value'] == $specShow['value'];
                });
                $specs['show'][$k]['value_text'] = $valueText ? $valueText[0]['text'] : '';
            } else {
                $specs['show'][$k]['has_value'] = false;
                $specs['show'][$k]['value_text'] = '';
            }
        }
        foreach ($specs['hiddens'] as $k => $specHidden) {
            if ($specHidden['type'] == 'multi_input' && ! empty($specHidden['value1'])) {
                $specs['hiddens'][$k]['has_value'] = true;
                $specs['hiddens'][$k]['value_text'] = $specHidden['value1'] .
                    ($specHidden['value2'] ? ' - ' . $specHidden['value2'] : '') .
                    $specHidden['unit'];
            } elseif ($specHidden['type'] == 'data_check_box' && ! empty($specHidden['value'])) {
                $specs['hiddens'][$k]['has_value'] = true;
                $valueText = array_filter($specHidden['values'], function ($v) use ($specHidden) {
                    return $v['value'] == $specHidden['value'];
                });
                $specs['hiddens'][$k]['value_text'] = $valueText ? array_values($valueText)[0]['text'] : '';
            } else {
                $specs['hiddens'][$k]['has_value'] = false;
                $specs['hiddens'][$k]['value_text'] = '';
            }
        }
        return $specs;
    }
}
