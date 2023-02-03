<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 32)->default('')->comment('用户昵称');
            $table->string('phone', 11)->default('')->comment('手机号');
            $table->string('open_id')->default('')->comment('openid');
            $table->string('avatar')->default('')->comment('头像');
            $table->string('id_card', 18)->default('')->comment('身份证');
            $table->unsignedTinyInteger('gender')->default(3)->comment('1:男，2：女，3：未知');
            $table->unsignedTinyInteger('vip_level')->default(0)->comment('0:普通用户');
            $table->dateTime('last_visit_at')->nullable()->comment('上次登录时间');
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
        Schema::dropIfExists('users');
    }
}
