<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->comment('问题反馈表');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->default(0)->comment('用户id');
            $table->text('content')->nullable()->comment('反馈的问题');
            $table->text('reply_content')->nullable()->comment('回复的内容');
            $table->unsignedBigInteger('reply_user')->default(0)->comment('回复人');
            $table->dateTime('reply_at')->nullable()->comment('回复时间');
            $table->tinyInteger('is_adopt')->default(0)->comment('是否采用');
            $table->json('question_medias')->nullable()->comment('问题截图');
            $table->json('reply_medias')->nullable()->comment('回复图片');
            $table->string('phone', 12)->nullable()->comment('联系方式');
            $table->string('type', 32)->default('')->comment('信息错误/缺失:info_miss 功能建议:advice 程序问题:program 其他问题:other');

            // 索引
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
}
