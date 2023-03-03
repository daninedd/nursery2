<?php

declare(strict_types=1);

use Hyperf\Database\Seeders\Seeder;

class Categories extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = file_get_contents('./seeders/categories.json');
        $datas = json_decode($datas,true);
        $insert = [];
        foreach ($datas as $value){
            $insert []= [
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
