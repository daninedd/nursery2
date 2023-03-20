<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Middleware\JwtAuthMiddleware;
use App\Model\MarketQuotation;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;

#[Controller]
#[Middlewares([JwtAuthMiddleware::class])]
class MarketQuotationController extends AbstractController
{
    /**
     *市场行情列表.
     */
    #[GetMapping(path: 'getList')]
    public function getList()
    {
        $belong = $this->request->query('year');
        $data = MarketQuotation::query()
            ->select(['year', 'month', 'title', 'publish_time', 'term', 'publish_department'])
            ->where('year', $belong)
            ->groupBy(['year', 'month', 'title', 'publish_time', 'term', 'publish_department'])->orderByDesc('month')->get();
        return $this->success($data);
    }
    //todo 去掉product_id搜索

    /**
     *市场行情详情.
     */
    #[GetMapping(path: 'getDetail')]
    public function getDetail()
    {
        $year = $this->request->query('year');
        $month = $this->request->query('month');
        $perPage = intval($this->request->query('per_page', 20));
        $keyword = $this->request->query('keyword', '');
        $query = MarketQuotation::query()
            ->where([['year', $year], ['month', $month]]);
        if ($keyword) {
            $query->where(['format_name', 'like', "%{$keyword}%"]);
        }
        return $this->success($query->paginate($perPage));
    }
}
