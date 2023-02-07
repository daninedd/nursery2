<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Request;

use App\Constants\Constant;
use App\Exception\BusinessException;
use App\Model\Address;
use App\Model\Category;
use App\Model\Product;
use App\Model\Purchase;
use App\Model\User;
use App\Model\UserOffer;
use Hyperf\Database\Query\Builder;
use Hyperf\DbConnection\Db;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class PurchaseRequest extends FormRequest
{
    public const SCENE_ADD = 'add';

    public const SCENE_EDIT = 'edit';

    public const SCENE_DETAIL = 'detail';

    public const SCENE_OFFER = 'offer';

    public const SCENE_LIST = 'list';

    public const SCENE_USER_PURCHASE_LIST = 'user_purchase_list';

    public const SCENE_END_PURCHASE = 'end_purchase';

    protected const MUST_HAVE = ['must_have_price', 'must_have_addr', 'must_have_image'];

    public array $scenes = [
        'add' => ['title', 'productId', 'catedgoryId', 'target_price', 'specs', 'unit',
            'price_type', 'address', 'media', 'remark', 'num', 'must_have', 'expire_at', ],
        'edit' => ['id', 'title', 'target_price', 'specs', 'unit', 'expire_at', 'must_have',
            'price_type', 'address', 'media', 'remark', 'num', ],
        'detail' => ['id'],
        'offer' => ['purchase_id', 'offerPrice', 'offerPhone', 'offerMedia', 'offerAddress', 'remark'],
        self::SCENE_LIST => ['keyword', 'order1', 'order2', 'order3', 'areas', 'category', 'crown', 'diameter', 'height', 'order'],
        'user_purchase_list' => ['push_status'],
        'end_purchase' => ['end_purchase_id'],
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
            'id' => ['required', Rule::exists('purchases')->where(function (Builder $query) use ($userId) {
                $query->where('push_status', Purchase::PUSH_STATUS_ENABLE);
                if ($this->getScene() == self::SCENE_EDIT) {
                    $query->where('user_id', $userId);
                }
            })],
            'end_purchase_id' => ['required', Rule::exists('purchases', 'id')->where(function (Builder $query) use ($userId) {
                $query->where('user_id', $userId);
                $query->where('push_status', Purchase::PUSH_STATUS_ENABLE);
            })],
            'purchase_id' => ['required', Rule::exists('purchases', 'id')->where(function (Builder $query) use ($userId) {
                $query->where('push_status', Purchase::PUSH_STATUS_ENABLE);
                $query->where('user_id', '<>', $userId);
            }),
                //                function ($attr, $value, $fail) use ($userId) {
                //                $offer = UserOffer::where([['purchase_id', $value], ['user_id', $userId]])->exists();
                //                if ($offer) {
                //                    $fail('您已经报过价了');
                //                }}
            ],
            'title' => 'required|max:32',
            'productId' => ['present', 'int', function ($attr, $value, $fail) {
                if ($value > 0 && ! Product::where('id', $value)->exists()) {
                    $fail("The {$attr} is invalid");
                }
            }],
            'categoryId' => ['required_if:productId,0', 'int', Rule::exists('categories', 'id')],
            'target_price' => 'required|numeric|min:0',
            'specs' => ['required', 'array:show,hiddens'],
            'specs.show' => ['present', 'array'],
            'specs.hiddens' => ['present', 'array'],
            'unit' => ['required', Rule::in(Constant::UNITS)],
            'price_type' => ['required', Rule::in([1, 2])],
            'address' => 'required|max:32',
            'media' => 'array|max:9',
            'remark' => 'present|max:140',
            'num' => 'required|numeric|min:0',
            'expire_at' => ['required', 'date', 'after:today'],
            'must_have' => ['array', Rule::in(self::MUST_HAVE)],
            'offerPhone' => ['required', 'regex:/^1[3456789]\d{9}$/'],
            'keyword' => 'present|max:10',
            'push_status' => ['required', Rule::in([Purchase::PUSH_STATUS_DISABLE, Purchase::PUSH_STATUS_ENABLE])],
            'order1' => [Rule::in(['asc', 'desc'])],
            'order2' => [Rule::in(['asc', 'desc'])],
            'order3' => [Rule::in(['asc', 'desc'])],
            //列表里的筛选项
            'areas' => ['string'],
            'category' => ['string'],
            'crown' => ['string'],
            'diameter' => ['string'],
            'height' => ['string'],
            'order' => [Rule::in(['default', 'visit', 'publish_time'])],
        ];
        if ($this->getScene() == 'offer') {
            $id = $this->post('purchase_id');
            $purchase = Purchase::findFromCache($id);
            $must_have_price = $purchase->must_have_price;
            $must_have_addr = $purchase->must_have_addr;
            $must_have_image = $purchase->must_have_image;
            $rules['offerPrice'] = ['numeric', 'gt:0', Rule::requiredIf($must_have_price)];
            $rules['offerAddress'] = ['array', 'size:3', Rule::requiredIf($must_have_addr)];
            $rules['offerMedia'] = ['array', Rule::requiredIf($must_have_image)];
            return $rules;
        }
        return $rules;
    }

    public function attributes(): array
    {
        return [
            'title' => '标题',
            'expire_at' => '截止日期',
            'specs' => '规格参数',
            'product_id' => '产品id',
            'category_id' => '品类id',
            'media' => '媒体文件',
            'price_type' => '报价方式',
            'offerMedia' => '报价图片',
            'offerPrice' => '价格',
            'offerAddress' => '报价地址',
            'offerPhone' => '联系电话',
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => '求购详情不存在',
            'media.required' => '请至少上传一张图片',
            'purchase_id.exists' => '求购详情不存在',
            'expire_at.after' => '截止日期必须大于今天',
        ];
    }

    /**
     * 新增求购.
     */
    public function addPurchase()
    {
        $userId = $this->getRequest()->getAttribute('userId');
        $purchase = new Purchase();
        $data = $this->validated();
        $product = $data['productId'] ? Product::findFromCache($data['productId']) : null;
        $purchase->title = $data['title'];
        $purchase->product_name = $product ? $product->name : '';
        $purchase->product_id = $product ? $product->id : 0;
        $purchase->product_nickname = $product ? $product->nick_name : null;
        $purchase->product_snapshot = $product ?: null;
        $purchase->category_id = $product ? $product->category_id : $data['categoryId'];
        $purchase->category_name = $product ? $product->category->name : Category::findFromCache($data['categoryId'])->name;
        $purchase->category_snapshot = $product ? $product->category : Category::findFromCache($data['categoryId']);
        $purchase->medias = $this->formatMedia($data['media']);
        $purchase->user_id = $userId;
        $purchase->contact = User::findFromCache($userId)->phone;
        $purchase->specs = $this->formatSpecs($data['specs']);
        $purchase->target_price = $data['target_price'];
        $purchase->price_type = $data['price_type'];
        $purchase->unit = $data['unit'];
        $purchase->push_status = 1;
        $purchase->recommend_status = 0;
        $purchase->verify_status = 1;
        $purchase->remark = $data['remark'];
        $purchase->num = $data['num'];
        $purchase->address = $data['address'];
        $purchase->expire_at = $data['expire_at'];
        $purchase->must_have_price = intval(in_array('must_have_price', $data['must_have']));
        $purchase->must_have_addr = intval(in_array('must_have_addr', $data['must_have']));
        $purchase->must_have_image = intval(in_array('must_have_image', $data['must_have']));
        if ($purchase->save()) {
            return $purchase->id;
        }
        throw new BusinessException(500, '创建求购失败');
    }

    /**
     * 编辑供应.
     */
    public function editPurchase()
    {
        $data = $this->validated();
        $purchase = Purchase::findFromCache($data['id']);
        $purchase->title = $data['title'];
        $purchase->medias = $this->formatMedia($data['media']);
        $purchase->specs = $this->formatSpecs($data['specs']);
        $purchase->target_price = $data['target_price'];
        $purchase->price_type = $data['price_type'];
        $purchase->push_status = Purchase::PUSH_STATUS_ENABLE;
        $purchase->remark = $data['remark'];
        $purchase->num = $data['num'];
        $purchase->address = $data['address'];
        $purchase->expire_at = $data['expire_at'];
        $purchase->must_have_price = intval(in_array('must_have_price', $data['must_have']));
        $purchase->must_have_addr = intval(in_array('must_have_addr', $data['must_have']));
        $purchase->must_have_image = intval(in_array('must_have_image', $data['must_have']));
        if ($purchase->save()) {
            return $purchase->id;
        }
        throw new BusinessException(500, '编辑采购失败');
    }

    public function detail()
    {
        $data = $this->validated();
        $data = Purchase::with(['user:id,name,phone,avatar'])->find($data['id']);
        $data->append(['has_enshrine']);
        if ($data->user_id == $this->getRequest()->getAttribute('userId')) {
            $data->makeVisible(['target_price']);
            $data->append(['must_have']);
        }
        return $data;
    }

    public function endPurchase()
    {
        $data = $this->validated();
        $data = Purchase::findFromCache($data['end_purchase_id']);
        $data->expire_at = date('Y-m-d');
        return $data->save();
    }

    /**
     * 报价.
     */
    public function offer()
    {
        $data = $this->validated();
        $userId = $this->getRequest()->getAttribute('userId');
        $purchase = Purchase::findFromCache($data['purchase_id']);
        $offer = new UserOffer();
        $offer->offer_price = $data['offerPrice'];
        $offer->offer_media = $this->formatMedia($data['offerMedia']);
        $offer->offer_address = $data['offerAddress'];
        $offer->offer_phone = $data['offerPhone'];
        $offer->user_id = $userId;
        $offer->purchase_id = $purchase->id;
        $offer->purchase_user_id = $purchase->user_id;
        if ($offer->save()) {
            $purchase->increment('offer_count');
            return $offer->id;
        }
        throw new BusinessException(500, '报价失败');
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
        $query = Purchase::query()->with('user:id,name,avatar')
            ->where([['push_status', Purchase::PUSH_STATUS_ENABLE], ['deleted_at', null]]);
        if ($keyword) {
            $query->where(function (\Hyperf\Database\Model\Builder $q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('product_name', 'like', "%{$keyword}%")
                    ->orWhere('product_nickname', 'like', "%{$keyword}%");
            });
        }
        if ($areas){
            $addr = Address::findFromCache($areas);
            $query->whereJsonContains('address', $addr->name);
        }
        if ($category){
            $query->whereIn('category_id', explode(',', $category));
        }

        if ($crown || $diameter || $height){
            //组装筛选条件
            $crown_value = $crown ? explode(',', $crown) : [];
            $diameter_value = $diameter ? explode(',', $diameter) : [];
            $height_value = $height ? explode(',', $height) : [];
            $sql = '';
            if ($crown_value){
                $crown_value[0] = intval($crown_value[0]) ?: '';
                $crown_value[1] = intval($crown_value[1]) ?: '';
                if ($crown_value[0] && $crown_value[1]){
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '冠幅')), 'label',
                                       'value1')) as UNSIGNED) between $crown_value[0] and $crown_value[1]";
                }elseif ($crown_value[0] && !$crown_value[1]){
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '冠幅')), 'label',
                                       'value1')) as UNSIGNED) >= $crown_value[0]";
                }elseif (!$crown_value[0] and $crown_value[1]){
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '冠幅')), 'label',
                                       'value1')) as UNSIGNED) <= $crown_value[1]";
                }
                if ($sql){
                    $query->whereRaw($sql);
                }
            }

            if ($diameter_value){
                $diameter_value[0] = intval($diameter_value[0]) ?: '';
                $diameter_value[1] = intval($diameter_value[1]) ?: '';
                if ($diameter_value[0] && $diameter_value[1]){
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '杆径')), 'label',
                                       'value1')) as UNSIGNED) between $diameter_value[0] and $diameter_value[1]";
                }elseif ($diameter_value[0] && !$diameter_value[1]){
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '杆径')), 'label',
                                       'value1')) as UNSIGNED) >= $diameter_value[0]";
                }elseif (!$diameter_value[0] and $diameter_value[1]){
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '杆径')), 'label',
                                       'value1')) as UNSIGNED) <= $diameter_value[1]";
                }
                if ($sql){
                    $query->whereRaw($sql);
                }
            }

            if($height_value){
                $height_value[0] = intval($height_value[0]) ?: '';
                $height_value[1] = intval($height_value[1]) ?: '';
                if ($height_value[0] && $height_value[1]){
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '高度')), 'label',
                                       'value1')) as UNSIGNED) between $height_value[0] and $height_value[1]";
                }elseif ($height_value[0] && !$height_value[1]){
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '高度')), 'label',
                                       'value1')) as UNSIGNED) >= $height_value[0]";
                }elseif (!$height_value[0] and $height_value[1]){
                    $sql = " CAST(json_extract(specs, REPLACE(json_unquote(json_search(specs, 'one', '高度')), 'label',
                                       'value1')) as UNSIGNED) <= $height_value[1]";
                }
                if ($sql){
                    $query->whereRaw($sql);
                }
            }
        }
        if ($order){
            if ($order == 'visit'){
                $query->orderBy('visit_count', 'desc');
            }elseif ($order == 'publish_time'){
                $query->orderBy('updated_at', 'desc');
            }
        }else{
            if (isset($validatedData['order1']) && $validatedData['order1']) {
                $query->orderBy('push_status', $validatedData['order1']);
            }
            if (isset($validatedData['order2']) && $validatedData['order2']) {
                $query->orderBy('expire_at', $validatedData['order2']);
            }
            if (isset($validatedData['order3']) && $validatedData['order3']) {
                $query->orderBy('created_at', $validatedData['order3']);
            }
        }
        $query->orderByRaw('sort desc, created_at desc');
        return $query->paginate(10);
    }

    public function getUserPurchaseList(): LengthAwarePaginator
    {
        $validatedData = $this->validated();
        $userId = $this->getRequest()->getAttribute('userId');
        $pushStatus = $validatedData['push_status'];
        $query = Purchase::query()->where([['user_id', $userId], ['deleted_at', null]]);
        $today = date('Y-m-d');
        if ($pushStatus) {
            $query->where('expire_at', '>', $today);
        } else {
            $query->where('expire_at', '<=', $today);
        }
        $query->orderBy('id', 'DESC');
        /** @var LengthAwarePaginator $results */
        $results = $query->paginate(10);
        $results->each(function ($purchase) {
            $purchase->append(['defaultUrl', 'progress']);
        });
        $results->data = $results->makeHidden('specs');
        $results->data = $results->makeVisible(['updated_at', 'offer_count']);
        return $results;
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
