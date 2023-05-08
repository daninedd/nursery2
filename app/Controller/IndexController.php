<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Middleware\JwtAuthMiddleware;
use App\Request\SearchRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;

#[Controller]
#[Middlewares([JwtAuthMiddleware::class])]
class IndexController extends AbstractController
{
    #[GetMapping(path: 'banner')]
    public function banner()
    {
        $data = [
            ['id' => '', 'src' => 'url1', 'url' => env('STATIC_PREFIX') . '/static/images/banner/banner1.jpg'],
            ['id' => '492734051128455169', 'src' => 'url2', 'url' => env('STATIC_PREFIX') . '/static/images/banner/banner2.jpg'],
            ['id' => '496473714611785728', 'src' => 'url3', 'url' => env('STATIC_PREFIX') . '/static/images/banner/banner3.jpg'],
            ['id' => '496623158590246912', 'src' => 'url4', 'url' => env('STATIC_PREFIX') . '/static/images/banner/banner4.jpg'],
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

    protected function getUUid()
    {
        $chars = md5(uniqid((string) mt_rand(), true));
        return substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);
    }
}
