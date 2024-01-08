<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToPackagingTreatmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packaging_treatments', function (Blueprint $table) {
            $table->enum('is_featured', [1, 0])->default(0)->after('meta_keyword');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packaging_treatments', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });
    }
}
