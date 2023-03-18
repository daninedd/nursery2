<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Command;

use App\Model\Product;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\DbConnection\Db;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class MarketQuotationCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('market:update');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Add Market Quotation Data');
    }

    public function handle()
    {
        $map = [
            '大红紫薇' => '紫薇',
            '二红紫薇' => '紫薇',
            '地被月季' => '月季',
            '绣球' => '绣球小苗',
            '中东海藻' => '中东海枣',
            '红叶石楠' => '红叶石楠小苗',
            '桅子花' => '栀子花',
            '菖莆' => '菖蒲',
        ];
        // 从 $input 获取 name 参数
        $path = $this->input->getArgument('path') ?? '';
        $dir = 'data/marketQuotation';
        if ($path) {
            $files = [$path];
        } else {
            $files = scandir($dir);
        }

        foreach ($files as $file) {
            if (str_contains($file, 'xlsx')) {
                $insert = [];
                Db::beginTransaction();
                $this->line('开始处理:' . $file);
                $filename = $dir . '/' . $file;
                $inputFileType = IOFactory::identify($filename);
                $reader = IOFactory::createReader($inputFileType);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($filename);

                $title = $spreadsheet->getActiveSheet()->getCell([1, 1])->getValue();
                $title = preg_replace('/ | |( | )/', '', $title);
                $department = $spreadsheet->getActiveSheet()->getCell([1, 3])->getValue();
                $department = preg_replace('/ | |( | )/', '', $department);
                $publish_time = $spreadsheet->getActiveSheet()->getCell([1, 4])->getValue();
                $publish_time = trim($publish_time);
                $publish_time = preg_replace('/^ /', '', $publish_time);
                $belong = $spreadsheet->getActiveSheet()->getCell([1, 2])->getValue();
                $belong = preg_replace('/ | |( | )/', '', $belong);
                $belong = preg_replace('/期/', '期,', $belong);

                $belong = explode(',', $belong);
                $term = $belong[0] ?? '';
                $belong = $belong[1] ?? '';
                $date = (date_parse_from_format('Y-m',$belong));
                $year = $date['year'];
                $month = $date['month'];

                // 从第8行开始遍历
                $i = 8;
                $current_product = $spreadsheet->getActiveSheet()->getCell([3, $i])->getValue();
                $current_no = 1;
                while ($current_product) {
                    $current_product = $spreadsheet->getActiveSheet()->getCell([3, $i])->getValue();
                    if (empty($current_product)) {
                        break;
                    }
                    $current_product = preg_replace('/ | |( | )/', '', $current_product);

                    $no = $spreadsheet->getActiveSheet()->getCell([2, $i])->getValue();
                    if ($no) {
                        $current_no = $no;
                    } else {
                        $no = $current_no;
                    }
                    $format_name = $current_product;
                    $meter_diameter = $spreadsheet->getActiveSheet()->getCell([4, $i])->getValue();
                    if (in_array($current_product, array_keys($map))) {
                        if ($current_product == '红叶石楠' && $meter_diameter){
                            $product = Product::query()->where('name', $current_product)->where(['category_id' => 8])->first();
                        }else{
                            $current_product = $map[$current_product];
                        }
                    }else{
                        $product = Product::query()->where('name', $current_product)->first();
                    }
                    if (empty($product)) {
                        $this->error("未找到产品:【{$current_product}】");
                        Db::rollBack();
                        return;
                    }
                    $ground_diameter = $spreadsheet->getActiveSheet()->getCell([5, $i])->getValue();
                    $height = $spreadsheet->getActiveSheet()->getCell([6, $i])->getValue();
                    $crown = $spreadsheet->getActiveSheet()->getCell([7, $i])->getValue();
                    $unit_text = $spreadsheet->getActiveSheet()->getCell([8, $i])->getValue();
                    $price = $spreadsheet->getActiveSheet()->getCell([9, $i])->getValue();
                    $last_price = $spreadsheet->getActiveSheet()->getCell([10, $i])->getValue();

                    $insert[] = [
                        'title' => $title,
                        'no' => $no,
                        'product_id' => $product->id,
                        'product_snapshot' => $product,
                        'category_id' => $product->category_id,
                        'category_snapshot' => $product->category,
                        'format_name' => $format_name ?: '',
                        'meter_diameter' => $meter_diameter ?: '',
                        'ground_diameter' => $ground_diameter ?: '',
                        'height' => $height ?: '',
                        'crown' => $crown ?: '',
                        'unit' => $unit_text ?: '',
                        'price' => $price ?: '',
                        'last_price' => $last_price ?: '暂无',
                        'year' => $year ?: '',
                        'month' => $month ?: '',
                        'term' => $term ?: '',
                        'publish_department' => $department ?: '',
                        'publish_link' => '',
                        'publish_time' => $publish_time ?: null,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    ++$i;
                }
                $ok = \Hyperf\DbConnection\Db::table('market_quotations')->insert($insert);
                if ($ok) {
                    Db::commit();
                    $this->line($file . ' 导入【成功】!');
                } else {
                    Db::rollBack();
                    $this->error($file . ' 导入【失败】');
                }
            }
        }
    }

    protected function getArguments()
    {
        return [
            ['path', InputArgument::OPTIONAL, '文件位置，放在data里'],
        ];
    }
}
