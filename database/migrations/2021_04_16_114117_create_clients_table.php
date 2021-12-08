<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->integer('license_id')->unsigned()->references('id')->on('licenses');
            $table->string('npsn')->nullable();
            $table->string('school_name', 100)->nullable();
            $table->integer('mst_province_id')->unsigned()->references('id')->on('mst_provinces');
            $table->integer('mst_regency_id')->unsigned()->references('id')->on('mst_regencies');
            $table->integer('mst_district_id')->unsigned()->references('id')->on('mst_districts');
            $table->integer('mst_village_id')->unsigned()->references('id')->on('mst_villages');
            $table->string('address', 500)->nullable();
            $table->integer('postal_code')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
