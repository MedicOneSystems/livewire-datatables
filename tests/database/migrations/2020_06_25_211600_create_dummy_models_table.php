<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDummyModelsTable extends Migration
{
    public function up()
    {
        Schema::create('dummy_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('relation_id')->index();
            $table->string('subject', 64);
            $table->string('category', 16);
            $table->text('body');
            $table->boolean('flag')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dummy_models');
    }
}
