<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateMarketQuotationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('market_quotations', function (Blueprint $table) {
            $table->comment('花木行情表');
            $table->bigIncrements('id');
            $table->string('title')->default('')->comment('标题');
            $table->unsignedSmallInteger('no')->default(1)->comment('编号');
            $table->unsignedBigInteger('product_id')->comment('产品id');
            $table->json('product_snapshot')->nullable()->comment('产品快照');
            $table->unsignedBigInteger('category_id')->comment('品类id');
            $table->json('category_snapshot')->nullable()->comment('品类快照');
            $table->string('format_name', 32)->comment('转成nursery的名字');
            $table->string('meter_diameter')->default('')->comment('米径');
            $table->string('ground_diameter')->default('')->comment('地径');
            $table->string('height')->default('')->comment('高度');
            $table->string('crown')->default('')->comment('冠幅');
            $table->string('unit')->default('株')->comment('计价单位');
            $table->string('price')->default('')->comment('本月装车价');
            $table->string('last_price')->default('')->comment('上月装车价');
            $table->string('belong')->default('')->comment('所属年月份');
            $table->string('term')->default('')->comment('期数');
            $table->string('publish_department')->nullable()->comment('发布单位');
            $table->string('publish_link')->default('')->comment('发布连接');
            $table->string('publish_time')->default('')->comment('发布时间');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_quotations');
    }
}
