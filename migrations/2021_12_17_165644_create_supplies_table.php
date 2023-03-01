<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateSuppliesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 32)->default('')->comment('产品名称');
            $table->string('nick_name', 32)->default('')->comment('产品简称');
            $table->unsignedBigInteger('product_id')->default(0)->comment('产品id');
            $table->unsignedBigInteger('category_id')->default(0)->comment('产品id');
            $table->json('category_snapshot')->nullable()->comment('品类快照');
            $table->json('product_snapshot')->nullable()->comment('产品快照');
            $table->json('medias')->nullable()->comment('媒体文档集合');
            $table->unsignedBigInteger('user_id')->default(0)->comment('用户id');
            $table->string('contact')->default('')->comment('联系人');
            $table->json('params')->nullable()->comment('产品参数');
            $table->unsignedDecimal('lowest_price')->default(0)->comment('最低价');
            $table->unsignedDecimal('highest_price')->default(0)->comment('最高价');
            $table->unsignedTinyInteger('ambiguous_price')->default(0)->comment('是否电仪');
            $table->unsignedTinyInteger('price_type')->default(1)->comment('1-上车价,2-地价');
            $table->unsignedTinyInteger('push_status')->default(1)->comment('发布状态');
            $table->unsignedTinyInteger('recommend_status')->default(1)->comment('推荐状态');
            $table->unsignedTinyInteger('verify_status')->default(1)->comment('审核状态');
            $table->unsignedInteger('sort')->default(0)->comment('排序');
            $table->string('description', 255)->default('')->comment('供应描述');
            $table->unsignedTinyInteger('num')->default(1)->comment('供应数量');
            $table->unsignedTinyInteger('visit_count')->default(1)->comment('曝光数量');
            $table->string('address')->default('')->comment('供应地址');
            $table->dateTime('expire_at')->nullable()->comment('截止日期');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
            $table->softDeletes();

            $table->index([\Hyperf\DbConnection\Db::raw('name(5)')]);
            $table->index('product_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
}
