<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateUserOfferTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_offer', function (Blueprint $table) {
            $table->comment('用户报价表');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->default(0)->comment('报价用户id');
            $table->unsignedBigInteger('purchase_id')->default(0)->comment('求购id');
            $table->string('offer_phone', 12)->default('')->comment('报价电话');
            $table->unsignedDecimal('offer_price')->default(0.00)->comment('报价价格');
            $table->json('offer_media')->nullable()->comment('报价媒体文件');
            $table->json('offer_address')->nullable()->comment('报价地址');
            $table->unsignedBigInteger('purchase_user_id')->default(0)->comment('求购人的id');
            $table->tinyInteger('accept')->default(0)->comment('1:接受，2：未接受');
            $table->text('remark')->nullable()->comment('报价备注');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at');
            $table->softDeletes();

            // 索引
            $table->index('created_at');
            $table->index('purchase_id');
            $table->index('purchase_user_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_offer');
    }
}
