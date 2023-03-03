<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->comment('产品表');
            $table->bigIncrements('id');
            $table->string('name', 32)->default('')->comment('产品名称');
            $table->string('nick_name', 32)->default('')->comment('产品简称');
            $table->string('sku', 64)->default('')->comment('SKU');
            $table->json('images')->nullable()->comment('图片集合');
            $table->json('tags')->nullable()->comment('产品标签');
            $table->unsignedBigInteger('category_id')->default(0)->comment('分类id');
            $table->unsignedTinyInteger('show_status')->default(1)->comment('展示状态');
            $table->unsignedInteger('sort')->default(0)->comment('排序');
            $table->text('description')->nullable()->comment('描述');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
            $table->softDeletes();

            $table->unique('sku');
            $table->index([\Hyperf\DbConnection\Db::raw('name(5)')]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
}
