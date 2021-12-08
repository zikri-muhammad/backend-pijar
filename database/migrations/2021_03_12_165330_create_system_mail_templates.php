<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemMailTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('subject');
            $table->string('description')->nullable();
            $table->string('content');
            $table->string('layout_id')->nullable();
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
        Schema::dropIfExists('system_mail_templates');
    }
}
