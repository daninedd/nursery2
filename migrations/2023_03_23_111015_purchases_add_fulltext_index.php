<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class PurchasesAddFulltextIndex extends Migration
{

    protected $index_name = 'title_product_name_fulltext';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $prefix = \Hyperf\DbConnection\Db::getTablePrefix();
            \Hyperf\DbConnection\Db::statement("ALTER TABLE {$prefix}{$table->getTable()}  ADD FULLTEXT INDEX {$this->index_name} (title, product_name) with parser ngram");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex($this->index_name);
        });
    }
}
