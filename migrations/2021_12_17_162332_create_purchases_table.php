<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->comment('求购表');
            $table->bigIncrements('id');
            $table->string('title', 255)->default('')->comment('求购标题');
            $table->string('product_name', 32)->default('')->comment('产品名称');
            $table->string('product_nickname', 32)->default('')->comment('产品简称');
            $table->unsignedBigInteger('product_id')->default(0)->comment('产品id');
            $table->json('product_snapshot')->nullable()->comment('产品快照');
            $table->unsignedInteger('category_id')->default(0)->comment('品类id');
            $table->string('category_name', 64)->default('')->comment('品类名称');
            $table->json('category_snapshot')->nullable()->comment('品类快照');
            $table->json('medias')->nullable()->comment('媒体文档集合');
            $table->unsignedBigInteger('user_id')->default(0)->comment('用户id');
            $table->string('contact', 64)->default('')->comment('联系电话');
            $table->json('specs')->nullable()->comment('产品参数');
            $table->unsignedDecimal('target_price')->default(0.00)->comment('心理预期价格');
            $table->tinyInteger('show_target_price')->default(0)->comment('是否显示心理价位');
            $table->unsignedTinyInteger('push_status')->default(1)->comment('发布状态');
            $table->unsignedTinyInteger('recommend_status')->default(1)->comment('推荐状态');
            $table->unsignedTinyInteger('verify_status')->default(1)->comment('审核状态');
            $table->unsignedInteger('sort')->default(0)->comment('排序');
            $table->text('remark')->nullable()->comment('求购描述');
            $table->unsignedMediumInteger('offer_count')->default(0)->comment('报价条数');
            $table->unsignedTinyInteger('must_have_price')->default(1)->comment('报价是否必填价格');
            $table->unsignedTinyInteger('must_have_image')->default(0)->comment('报价是否必填图片');
            $table->unsignedTinyInteger('must_have_addr')->default(0)->comment('报价是否必填地址');
            $table->unsignedTinyInteger('offer_type')->default(1)->comment('1-上车价,2-地价');
            $table->unsignedInteger('num')->default(1)->comment('求购数量');
            $table->json('address')->nullable()->comment('求购地址');
            $table->unsignedTinyInteger('access_offer')->default(0)->comment('1:已接受报价,2:未接受报价');
            $table->unsignedBigInteger('visit_count')->default(0)->comment('浏览次数');
            $table->date('expire_at')->nullable()->comment('截止日期');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at');
            $table->softDeletes();

            // 索引
            $table->index([\Hyperf\DbConnection\Db::raw('product_name(5)')]);
            $table->index([\Hyperf\DbConnection\Db::raw('title(6)')]);
            $table->index('product_id');
            $table->index('category_id');
            $table->index('user_id');
            $table->index('expire_at');
            $table->index('visit_count');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
}
