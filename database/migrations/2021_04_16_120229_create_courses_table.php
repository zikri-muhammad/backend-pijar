<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_subjects', function (Blueprint $table) {
            $table->id();
            $table->integer('mst_class_id')->unsigned()->references('id')->on('mst_classes');
            $table->string('name');
            $table->string('code');
            $table->text('description');
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
        Schema::dropIfExists('mst_subjects');
    }
}
