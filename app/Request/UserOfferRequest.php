<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Request;

use App\Exception\BusinessException;
use App\Model\Purchase;
use App\Model\User;
use App\Model\UserOffer;
use Hyperf\Database\Query\Builder;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class UserOfferRequest extends FormRequest
{
    public const SCENE_LIST = 'list';

    public const SCENE_MY_OFFER = 'my_offer';

    public const SCENE_MODIFY_OFFER = 'modify_offer';

    public const SCENE_ACCEPT_OFFER = 'accept_offer';

    public array $scenes = [
        self::SCENE_LIST => ['purchase_id'],
        self::SCENE_ACCEPT_OFFER => ['purchase_id', 'offer_id'],
        self::SCENE_MODIFY_OFFER => ['id', 'offer_phone', 'offer_media', 'offer_price', 'offer_address', 'remark'],
        self::SCENE_MY_OFFER => ['has_accept'],
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
            'purchase_id' => ['required', Rule::exists('purchases', 'id')->where(function (Builder $query) use ($userId) {
                $query->where('push_status', Purchase::PUSH_STATUS_ENABLE);
                $query->where('user_id', $userId);
            })],
            'offer_id' => ['required', Rule::exists('user_offer', 'id')->where(function (Builder $query) use ($userId) {
                $query->where([['purchase_id', $this->input('purchase_id')],
                    ['id', $this->input('offer_id')],
                    ['purchase_user_id', $userId],
                    ['accept', UserOffer::NOT_ACCEPT],
                ]);
            }), function ($attr, $value, $fail) {
                if (UserOffer::where([['purchase_id', $this->input('purchase_id')], ['accept', UserOffer::ACCEPT]])->exists()) {
                    $fail('该笔采购已经接受了报价');
                }
            }],
            'id' => ['required', Rule::exists('user_offer', 'id')->where(function (Builder $query) use ($userId) {
                $query->where([
                    ['id', $this->input('id')],
                    ['user_id', $userId],
                ]);
            })],
            'offer_phone' => ['required', 'regex:/^1[3456789]\d{9}$/'],
            'remark' => 'present|max:140',
            'has_accept' => Rule::in(['all', UserOffer::ACCEPT, UserOffer::NOT_ACCEPT]),
        ];

        if ($this->getScene() == self::SCENE_MODIFY_OFFER) {
            $id = $this->post('id');
            $offer = UserOffer::findFromCache($id);
            $must_have_price = $offer->purchase->must_have_price;
            $must_have_addr = $offer->purchase->must_have_addr;
            $must_have_image = $offer->purchase->must_have_image;
            $rules['offer_price'] = ['numeric', 'gt:0', Rule::requiredIf(boolval($must_have_price))];
            $rules['offer_address'] = ['array', 'size:3', Rule::requiredIf(boolval($must_have_addr))];
            $rules['offer_media'] = ['array', Rule::requiredIf(boolval($must_have_image))];
            return $rules;
        }
        return $rules;
    }

    public function attributes(): array
    {
        return [
            'offer_phone' => '联系电话',
            'offer_price' => '报价价格',
            'offer_address' => '报价地址',
            'remark' => '备注信息',
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }

    /**
     * 查看报价列表.
     */
    public function getOffers()
    {
        $validatedData = $this->validated();
        $userId = $this->getRequest()->getAttribute('userId');
        $purchase_id = $validatedData['purchase_id'];
        $query = UserOffer::query()->where([['purchase_id', $purchase_id], ['purchase_user_id', $userId]]);
        $query->with(['user']);
        return $query->paginate(10);
    }

    /**
     * 接受报价.
     */
    public function acceptOffer()
    {
        $validatedData = $this->validated();
        $offer_id = $validatedData['offer_id'];
        return UserOffer::find($offer_id)->setAttribute('accept', UserOffer::ACCEPT)->save();
    }

    /**
     * 我的报价.
     */
    public function myOffer()
    {
        $validateData = $this->validated();
        $userId = $this->getRequest()->getAttribute('userId');
        $query = UserOffer::query()->where([['user_id', $userId]]);
        $query->with(['user', 'purchase:id,expire_at,address,num,unit,title,product_name,category_name,medias,specs,must_have_price,must_have_image,must_have_addr']);
        if ($validateData['has_accept'] === UserOffer::ACCEPT) {
            $query->where([['accept', UserOffer::ACCEPT]]);
        }
        return $query->paginate(10);
    }

    /**
     * 修改报价.
     */
    public function modifyOffer()
    {
        $validatedData = $this->validated();
        $userId = $this->getRequest()->getAttribute('userId');
        $offer = UserOffer::findFromCache($validatedData['id']);
        $offer->offer_phone = $validatedData['offer_phone'];
        $offer->offer_media = $this->formatMedia($validatedData['offer_media']);
        $offer->offer_price = $validatedData['offer_price'];
        $offer->offer_address = $validatedData['offer_address'];
        $offer->remark = $validatedData['remark'];
        if ($offer->save()) {
            return $offer->id;
        }
        throw new BusinessException(500, '修改报价失败');
    }

    protected function formatMedia($media): array
    {
        $re = [];
        foreach ($media as $media_d) {
            $re[] = ['path' => $media_d['path'], 'type' => $media_d['type']];
        }
        return $re;
    }
}
