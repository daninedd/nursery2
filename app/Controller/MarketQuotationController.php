<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Middleware\JwtAuthMiddleware;
use App\Model\MarketQuotation;
use App\Request\FeedbackRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use function Swoole\Coroutine\Http\get;

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
            ->select(['year','month', 'title', 'publish_time', 'term', 'publish_department'])
            ->where('year', $belong)
            ->groupBy(['year', 'month'])->orderByDesc('month')->get();
        return $this->success($data);
    }

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
        if ($keyword){
            $query->where(['format_name', 'like', "%{$keyword}%"]);
        }
        return $this->success($query->paginate($perPage));
    }
}
