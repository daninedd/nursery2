<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Middleware\JwtAuthMiddleware;
use App\Request\PurchaseRequest;
use App\Request\SearchRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;

#[Controller]
#[Middlewares([JwtAuthMiddleware::class])]
class IndexController extends AbstractController
{
    public const IP = 'http://192.168.31.48:9501';

    #[GetMapping(path: 'banner')]
    public function banner()
    {
        $data = [
            ['id' => 1, 'src' => 'url1', 'url' => self::IP . '/static/images/1.jpg'],
            ['id' => 2, 'src' => 'url2', 'url' => self::IP . '/static/images/2.jpg'],
            ['id' => 3, 'src' => 'url3', 'url' => self::IP . '/static/images/3.jpg'],
            ['id' => 4, 'src' => 'url4', 'url' => self::IP . '/static/images/4.jpg'],
        ];
        return $this->success($data);
    }

    #[GetMapping(path: 'getSpecs')]
    public function getSpecs()
    {
        $data = file_get_contents('./public/specs.json');
        $data = json_decode($data, true);
        return $this->success($data);
    }


    /**
     *搜索接口.
     */
    #[GetMapping(path: 'search')]
    public function search()
    {
        $request = $this->container->get(SearchRequest::class);
        $request->scene(SearchRequest::SCENE_SEARCH);
        $request->validateResolved();
        $data = $request->search();
        return $this->success($data);
    }

    /**
     *获取热搜词.
     */
    #[GetMapping(path: 'getHotSearch')]
    public function getHotSearch()
    {
        $request = $this->container->get(SearchRequest::class);
        $request->scene(SearchRequest::SCENE_GET_HOT_SEARCH);
        $request->validateResolved();
        $data = $request->getHotSearch();
        return $this->success($data);
    }
    public function supplyDetail()
    {
        $id = $this->request->input('id');
        return [
            'code' => 200,
            'message' => 'success',
            'data' => [
                'id' => $id,
                'title' => '龟甲冬青大杯苗',
                'image' => [
                    ['id' => 5, 'src' => self::IP . '/static/images/c.jpeg'],
                    ['id' => 6, 'src' => self::IP . '/static/images/d.jpeg'],
                    ['id' => 7, 'src' => self::IP . '/static/images/c.jpeg'],
                    ['id' => 8, 'src' => self::IP . '/static/images/d.jpeg'],
                ],
                'price' => [
                    'minPrice' => '120',
                    'maxPrice' => '150',
                ],
                'skus' => [
                    ['key' => '冠幅', 'value' => '120-150', 'unit' => 'cm'], ['key' => '高度', 'value' => '15', 'unit' => 'cm'],
                ],
                'num' => ['num' => rand(0, 999999), 'unit' => '颗'],
                'address' => '成都市温江区寿安镇',
                'owner' => [
                    'id' => 1,
                    'nickname' => '时间似深海',
                    'tags' => ['优质客户', '诚信保证'],
                    'join_days' => 888,
                    'avatar' => [['id' => 6, 'src' => self::IP . '/static/images/123.jpg']],
                    'phone' => 13668264587,
                ],
                'sku_detail' => [
                    ['key' => 'product_name', 'value' => '龟甲冬青', 'key_name' => '产品名称'],
                    ['key' => 'diameter', 'value' => '17-17公分', 'key_name' => '地径'],
                    ['key' => 'height', 'value' => '100-1700厘米', 'key_name' => '高度'],
                    ['key' => 'width', 'value' => '100-140厘米', 'key_name' => '冠幅'],
                    ['key' => 'plant_status', 'value' => '容器苗', 'key_name' => '种植状态'],
                    ['key' => 'tree_width', 'value' => '全冠', 'key_name' => '树冠'],
                    ['key' => 'quality', 'value' => 'A-精品', 'key_name' => '品质'],
                    ['key' => 'soil_quality', 'value' => '黑土', 'key_name' => '土质'],
                    ['key' => 'tree_shape', 'value' => '自然形', 'key_name' => '树形'],
                    ['key' => 'branch_point', 'value' => '30-60厘米', 'key_name' => '分枝点'],
                ],
                'address_detail' => '成都市温江区寿安镇喻庙社区17组40号',
                'description' => '7分货，1.5米分枝点',
                'visit' => '122.3w',
            ],
        ];
    }

    public function categoryList()
    {
        $get = $this->request->query();
        $data = [
            [
                'id' => 555,
                'title' => '常绿乔木桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 593,
                'title' => '常绿乔木->丹桂桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 1022,
                'title' => '常绿灌木->桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2110,
                'title' => '常绿乔木->桂花桩景',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2320,
                'title' => '常绿乔木->桂花小苗',
                'value' => '桂花',
                'categoryId' => 9,
            ],
            [
                'id' => 2320,
                'title' => '常绿乔木->丛生桂花（多杆桂花）',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2320,
                'title' => '色块小苗->多杆山桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2320,
                'title' => '多年生->荷包山桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2320,
                'title' => '卵叶荷包山桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2320,
                'title' => '常绿乔木->状元红桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2320,
                'title' => '常绿乔木->五彩桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2320,
                'title' => '常绿乔木->四季红桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2320,
                'title' => '常绿乔木->山桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2320,
                'title' => '常绿乔木->洋桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2320,
                'title' => '常绿乔木->珍珠彩叶桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2320,
                'title' => '常绿乔木->珍珠彩叶桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2320,
                'title' => '常绿乔木->珍珠彩叶桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
            [
                'id' => 2320,
                'title' => '常绿乔木->珍珠彩叶桂花',
                'value' => '桂花',
                'categoryId' => 8,
            ],
        ];
        return [
            'method' => '',
            'message' => 'aaaa',
            'data' => $data,
        ];
    }
}
