<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CraeteEnterpriseAuthTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enterprise_auth', function (Blueprint $table) {
            $table->comment('企业认证表');
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->default(0)->comment('用户id');
            $table->string('title')->default('')->comment('企业名称');
            $table->json('medias')->nullable()->comment('企业资质图片');
            $table->tinyInteger('enable')->default(0)->comment('0:审核中，1：通过，-1：不通过');
            $table->string('contact')->default('')->comment('联系人');
            $table->string('phone')->default('')->comment('联系人手机号');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enterprise_auth');
    }
}
