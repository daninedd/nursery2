<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\Database\Seeders\Seeder;

class Address extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $addr = file_get_contents('./seeders/ns_address.json');
        $addr = json_decode($addr, true);
        $insert = [];
        foreach ($addr as $value) {
            $insert[] = [
                'id' => $value['id'],
                'code' => $value['code'],
                'parent_id' => $value['parent_id'],
                'name' => $value['name'],
                'order' => $value['order'],
                'level' => $value['level'],
                'status' => $value['status'],
            ];
        }
        \Hyperf\DbConnection\Db::table('address')->insert($insert);
    }
}
