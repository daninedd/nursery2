<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateAddressTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('address', function (Blueprint $table) {
            $table->comment('地区表');
            $table->bigIncrements('id');
            $table->string('code', 50)->default(0)->comment('区域代码');
            $table->string('name', 40)->default('')->comment('名称');
            $table->unsignedInteger('parent_id')->default(0)->comment('父级id');
            $table->unsignedInteger('order')->default(0)->comment('排序');
            $table->unsignedTinyInteger('level')->default(1)->comment('等级');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address');
    }
}
