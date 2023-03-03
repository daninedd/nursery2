<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Request;

use App\Model\Address;
use App\Model\User;
use Hyperf\Validation\Request\FormRequest;

class AddressRequest extends FormRequest
{
    public const SCENE_LIST = 'list';

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
        ];
    }

    public function attributes(): array
    {
        return [
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
    public function list()
    {
        $address = Address::query()->with(['children.children'])->where(['parent_id' => 0])->get();
        $res = [['name' => '全部地区', 'value' => 0]];
        foreach ($address as $k => $addr) {
            $province = ['name' => $addr->name, 'value' => $addr->id, 'submenu' => [['name' => '全部城市', 'value' => $addr->id]]];
            foreach ($addr->children as $cities) {
                $city = ['name' => $cities->name, 'value' => $cities->id, 'submenu' => [['name' => '全部区县', 'value' => $cities->id]]];
                $province['submenu'][] = $city;
                $last_arr_index = count($province['submenu']) - 1;
                // $province['submenu'][$last_arr_index]['submenu'][] = [];
                foreach ($cities->children as $district) {
                    $province['submenu'][$last_arr_index]['submenu'][] = ['name' => $district->name, 'value' => $district->id];
                }
            }
            $res[] = $province;
        }
        return $res;
    }

    public function addList()
    {
        $address = Address::query()->with(['children.children'])->where(['parent_id' => 0])->get();
        $res = [];
        foreach ($address as $k => $addr) {
            $province = ['text' => $addr->name, 'value' => $addr->id, 'children' => []];
            foreach ($addr->children as $cities) {
                $city = ['text' => $cities->name, 'value' => $cities->id, 'children' => []];
                $province['children'][] = $city;
                $last_arr_index = count($province['children']) - 1;
                // $province['submenu'][$last_arr_index]['submenu'][] = [];
                foreach ($cities->children as $district) {
                    $province['children'][$last_arr_index]['children'][] = ['text' => $district->name, 'value' => $district->id];
                }
            }
            $res[] = $province;
        }
        return $res;
    }
}
