<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateEnshrineTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enshrines', function (Blueprint $table) {
            $table->comment('收藏记录表');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedTinyInteger('type')->default(1)->comment('1：供应，2：采购');
            $table->unsignedBigInteger('item_id')->default(0)->comment('收藏项目id');
            $table->json('item_snapshot')->nullable()->comment('收藏快照');
            $table->dateTime('created_at')->useCurrent()->comment('收藏时间');
            $table->dateTime('updated_at');

            $table->index(['user_id', 'item_id', 'type'], 'idx_user_id_item_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enshrines');
    }
}
