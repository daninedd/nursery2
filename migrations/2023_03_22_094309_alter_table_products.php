<?php

use App\Model\Supply;
use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AlterTableProducts extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $prefix = \Hyperf\DbConnection\Db::getTablePrefix();
            \Hyperf\DbConnection\Db::statement("ALTER TABLE {$prefix}{$table->getTable()}  ADD FULLTEXT INDEX `name_full_text` (name, nick_name) with parser ngram");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('name_full_text');
        });
    }
}
