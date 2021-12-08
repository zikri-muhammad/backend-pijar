<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstVillages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_villages', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->references('id')->on('mst_districts');
            $table->string('name', 100);
            $table->string('code', 10);
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
        Schema::dropIfExists('mst_villages');
    }
}
