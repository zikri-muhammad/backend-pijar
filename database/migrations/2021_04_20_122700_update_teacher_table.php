<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTeacherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->integer('mst_subject_id')->unsigned()->references('id')->on('mst_subjects')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->integer('mst_subject_id')->unsigned()->references('id')->on('mst_subjects')->change();
        });
    }
}
