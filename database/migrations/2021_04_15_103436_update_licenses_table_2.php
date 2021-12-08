<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLicensesTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->timestamp('activation_time_from');
            $table->timestamp('activation_time_to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('activation_time_from');
            $table->dropColumn('activation_time_to');
        });
    }
}
