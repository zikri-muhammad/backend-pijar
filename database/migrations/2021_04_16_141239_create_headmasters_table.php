<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeadmastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('headmasters', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id')->unsigned()->references('id')->on('clients');
            $table->string('nip')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->date('dob')->nullable()->default(null);
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
        Schema::dropIfExists('headmasters');
    }
}
