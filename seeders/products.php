<?php

declare(strict_types=1);

use Hyperf\Database\Seeders\Seeder;

class Products extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = file_get_contents('./seeders/products.json');
        $datas = json_decode($datas,true);
        $insert = [];
        foreach ($datas as $value){
            $insert []= [
                'name' => $value['name'],
                'nick_name' => $value['nick_name'],
                'sku' => $value['sku'],
                'category_id' => $value['category_id'],
                'show_status' => $value['show_status'],
                'sort' => $value['sort'],
                'description' => $value['description'],
            ];
        }
        \Hyperf\DbConnection\Db::table('products')->insert($insert);
    }
}
