<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('students');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id')->unsigned()->references('id')->on('clients');
            $table->string('nis', 20)->nullable();
            $table->string('nisn', 20)->nullable();
            $table->string('phone')->nullable()->default(null);
            $table->string('email')->nullable()->default(null);
            $table->date('dob')->nullable()->default(null);
            $table->boolean('is_activated')->default(false);
            $table->timestamp('activated_at')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);
        });
    }
}
