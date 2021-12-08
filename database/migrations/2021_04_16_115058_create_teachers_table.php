<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id')->unsigned()->references('id')->on('clients');
            $table->string('nik')->nullable();
            $table->string('nip')->nullable();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->integer('mst_subject_id')->unsigned()->references('id')->on('mst_subjects');
            $table->date('dob')->nullable()->default(null);
            $table->boolean('is_activated')->default(false);
            $table->timestamp('activated_at')->nullable()->default(null);
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
        Schema::dropIfExists('teachers');
    }
}
