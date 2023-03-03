<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\Database\Seeders\Seeder;

class Categories extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $datas = file_get_contents('./seeders/categories.json');
        $datas = json_decode($datas, true);
        $insert = [];
        foreach ($datas as $value) {
            $insert[] = [
                'id' => $value['id'],
                'name' => $value['name'],
                'parent_id' => $value['parent_id'],
                'icon' => $value['icon'],
                'sort' => $value['sort'],
            ];
        }
        \Hyperf\DbConnection\Db::table('categories')->insert($insert);
    }
}
