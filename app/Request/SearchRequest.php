<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Request;

use App\Exception\BusinessException;
use App\Model\Address;
use App\Model\Category;
use App\Model\Product;
use App\Model\Supply;
use App\Model\User;
use Carbon\Carbon;
use Hyperf\Cache\Cache;
use Hyperf\Database\Model\Builder;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class SupplyRequest extends FormRequest
{
    public const SCENE_ADD = 'add';

    public const SCENE_EDIT = 'edit';

    public const SCENE_DETAIL = 'detail';

    public const SCENE_LIST = 'list';

    public const SCENE_USER_SUPPLY_LIST = 'user_supply_list';

    public const SCENE_REFRESH_SUPPLY = 'refresh_supply';

    public array $scenes = [
        'add' => ['title', 'productId', 'categoryId', 'price1', 'price2', 'specs', 'unit',
            'price_type', 'address', 'media', 'remark', 'num', ],
        'edit' => ['id', 'title', 'price1', 'price2', 'specs', 'unit',
            'price_type', 'address', 'media', 'remark', 'num', ],
        'detail' => ['id'],
        'list' => ['keyword', 'order1', 'order2', 'order3', 'areas', 'category', 'crown', 'diameter', 'height', 'order'], // order1:供应状态,order2:浏览次数,3:发布时间
        'user_supply_list' => ['push_status'],
        'refresh_supply' => ['supply_id'],
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
        $userId = $this->getRequest()->getAttribute('userId');
        return [
            'id' => ['required', Rule::exists('supplies')->where(function (\Hyperf\Database\Query\Builder $query) use ($userId) {
                $query->where('push_status', Supply::PUSH_STATUS_ENABLE);
                if ($this->getScene() == self::SCENE_EDIT) {
                    $query->where('user_id', $userId);
                }
            })],
            'supply_id' => ['required',
                function ($attr, $value, $fail) {
                    if ($this->cache->has(Supply::genRefreshCacheKey($value))) {
                        $fail('今天已经刷新过了');
                    }
                },
                Rule::exists('supplies', 'id')->where(function (\Hyperf\Database\Query\Builder $query) use ($userId) {
                    $query->where('push_status', Supply::PUSH_STATUS_ENABLE)
                        ->where('user_id', $userId);
                }),
            ],
            'title' => 'required|max:32',
            'productId' => ['present', 'int', function ($attr, $value, $fail) {
                if ($value > 0 && ! Product::where('id', $value)->exists()) {
                    $fail("The {$attr} is invalid");
                }
            }],
            'categoryId' => ['required_if:productId,0', 'int', Rule::exists('categories', 'id')],
            'price1' => 'required|numeric|min:0',
            'price2' => 'nullable|numeric|min:0',
            'specs' => ['required', 'array:show,hiddens'],
            'specs.show' => ['present', 'array'],
            'specs.hiddens' => ['present', 'array'],
            'unit' => ['required', Rule::in([1, 2, 3, 4, 5, 6, 7, 8, 9])],
            'price_type' => ['required', Rule::in([1, 2])],
            'address' => 'required|max:32',
            'media' => 'array|max:9',
            'remark' => 'present|max:140',
            'num' => 'required|numeric|min:0',
            'keyword' => 'present|max:10',
            'push_status' => ['required', Rule::in([Supply::PUSH_STATUS_ENABLE, Supply::PUSH_STATUS_DISABLE])],
            'order1' => [Rule::in(['asc', 'desc'])],
            'order2' => [Rule::in(['asc', 'desc'])],
            'order3' => [Rule::in(['asc', 'desc'])],
            // 列表里的筛选项
            'areas' => ['string'],
            'category' => ['string'],
            'crown' => ['string'],
            'diameter' => ['string'],
            'height' => ['string'],
            'order' => [Rule::in(['default', 'visit', 'publish_time'])],
        ];
    }

    public function messages(): array
    {
        return ['id.exists' => '供应详情不存在'];
    }

    /**
     * 新增供应.
     */
    public function addSupply()
    {
        $userId = $this->getRequest()->getAttribute('userId');
        $supply = new Supply();
        $data = $this->validated();
        $product = Product::findFromCache($data['productId']);
        $supply->title = $data['title'];
        $supply->product_name = $product ? $product->name : '';
        $supply->product_id = $product ? $product->id : 0;
        $supply->product_snapshot = $product ?: null;
        $supply->category_id = $product ? $product->category_id : $data['categoryId'];
        $supply->category_snapshot = $product ? $product->category : Category::findFromCache($data['categoryId']);
        $supply->medias = $this->formatMedia($data['media']);
        $supply->user_id = $userId;
        $supply->contact = User::findFromCache($userId)->phone;
        $supply->specs = $this->formatSpecs($data['specs']);
        $supply->lowest_price = $data['price1'];
        $supply->highest_price = $data['price2'] ?: $data['price1'];
        $supply->ambiguous_price = 0;
        $supply->price_type = $data['price_type'];
        $supply->unit = $data['unit'];
        $supply->push_status = 1;
        $supply->recommend_status = 0;
        $supply->verify_status = 1;
        $supply->description = $data['remark'];
        $supply->num = $data['num'];
        $supply->address = $data['address'];
        $supply->expire_at = (new Carbon())->addDays(3)->format('Y-m-d H:i:s');
        if ($supply->save()) {
            return $supply->id;
        }
        throw new BusinessException(500, '创建供应失败');
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
        $supply->specs = $this->formatSpecs($data['specs']);
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
        return Supply::findFromCache($data['id'])->load('user')->append(['category', 'has_enshrine'])->makeVisible('category_id');
    }

    public function getList()
    {
        $validatedData = $this->validated();
        $keyword = $validatedData['keyword'];
        $areas = $validatedData['areas'] ?? '';
        $category = $validatedData['category'] ?? '';
        $crown = $validatedData['crown'] ?? '';
        $diameter = $validatedData['diameter'] ?? '';
        $height = $validatedData['height'] ?? '';
        $order = $validatedData['order'] ?? '';
        $query = Supply::query()->with('user:id,name,avatar')
            ->where([['push_status', Supply::PUSH_STATUS_ENABLE], ['deleted_at', null]]);
        if ($keyword) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('product_name', 'like', "%{$keyword}%");
            });
        }
        if ($areas) {
            $addr = Address::findFromCache($areas);
            $query->whereJsonContains('address', $addr->name);
        }
        if ($category) {
            $query->whereIn('category_id', explode(',', $category));
        }

        if ($crown || $diameter || $height) {
            // 组装筛选条件
            $crown_value = $crown ? explode(',', $crown) : [];
            $diameter_value = $diameter ? explode(',', $diameter) : [];
            $height_value = $height ? explode(',', $height) : [];
            $sql = '';
            if ($crown_value) {
                $crown_value[0] = intval($crown_value[0]) ?: '';
                $crown_value[1] = intval($crown_value[1]) ?: '';
                if ($crown_value[0] && $crown_value[1]) {
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '冠幅')), 'label',
                                       'value1')) as UNSIGNED) between {$crown_value[0]} and {$crown_value[1]}";
                } elseif ($crown_value[0] && ! $crown_value[1]) {
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '冠幅')), 'label',
                                       'value1')) as UNSIGNED) >= {$crown_value[0]}";
                } elseif (! $crown_value[0] and $crown_value[1]) {
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '冠幅')), 'label',
                                       'value1')) as UNSIGNED) <= {$crown_value[1]}";
                }
                if ($sql) {
                    $query->whereRaw($sql);
                }
            }

            if ($diameter_value) {
                $diameter_value[0] = intval($diameter_value[0]) ?: '';
                $diameter_value[1] = intval($diameter_value[1]) ?: '';
                if ($diameter_value[0] && $diameter_value[1]) {
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '杆径')), 'label',
                                       'value1')) as UNSIGNED) between {$diameter_value[0]} and {$diameter_value[1]}";
                } elseif ($diameter_value[0] && ! $diameter_value[1]) {
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '杆径')), 'label',
                                       'value1')) as UNSIGNED) >= {$diameter_value[0]}";
                } elseif (! $diameter_value[0] and $diameter_value[1]) {
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '杆径')), 'label',
                                       'value1')) as UNSIGNED) <= {$diameter_value[1]}";
                }
                if ($sql) {
                    $query->whereRaw($sql);
                }
            }

            if ($height_value) {
                $height_value[0] = intval($height_value[0]) ?: '';
                $height_value[1] = intval($height_value[1]) ?: '';
                if ($height_value[0] && $height_value[1]) {
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '高度')), 'label',
                                       'value1')) as UNSIGNED) between {$height_value[0]} and {$height_value[1]}";
                } elseif ($height_value[0] && ! $height_value[1]) {
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '高度')), 'label',
                                       'value1')) as UNSIGNED) >= {$height_value[0]}";
                } elseif (! $height_value[0] and $height_value[1]) {
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '高度')), 'label',
                                       'value1')) as UNSIGNED) <= {$height_value[1]}";
                }
                if ($sql) {
                    $query->whereRaw($sql);
                }
            }
        }

        if ($order) {
            if ($order == 'visit') {
                $query->orderBy('visit_count', 'desc');
            } elseif ($order == 'publish_time') {
                $query->orderBy('updated_at', 'desc');
            }
        } else {
            if (isset($validatedData['order1']) && $validatedData['order1']) {
                $query->orderBy('push_status', $validatedData['order1']);
            }
            if (isset($validatedData['order2']) && $validatedData['order2']) {
                $query->orderBy('visit_count', $validatedData['order2']);
            }
            if (isset($validatedData['order3']) && $validatedData['order3']) {
                $query->orderBy('created_at', $validatedData['order3']);
            }
        }
        $query->orderByRaw('sort desc, created_at desc');
        return $query->paginate(20);
    }

    public function getUserSupplyList()
    {
        $validatedData = $this->validated();
        $userId = $this->getRequest()->getAttribute('userId');
        $pushStatus = $validatedData['push_status'];
        $query = Supply::query()->where([['user_id', $userId], ['push_status', $pushStatus], ['deleted_at', null]]);
        $query->orderBy('id', 'DESC');
        /** @var LengthAwarePaginator $results */
        $results = $query->paginate(10);
        $results->each(function ($supply) {
            $supply->append(['progress', 'hasRefresh']);
        });
        $results->data = $results->makeHidden('specs');
        $results->data = $results->makeVisible('updated_at');
        return $results;
    }

    // 刷新供应
    public function refreshUserSupply()
    {
        $validatedData = $this->validated();
        $id = $validatedData['supply_id'];
        $supply = Supply::findFromCache($id);
        $supply->updated_at = (new Carbon())->toDateTimeString();
        $this->cache->set(Supply::genRefreshCacheKey($id), true, Carbon::now()->setHour(23)->setMinute(23)->setSecond(60));
        return $supply->save();
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
                $specs['show'][$k]['value_text'] = $valueText ? array_values($valueText)[0]['text'] : '';
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
