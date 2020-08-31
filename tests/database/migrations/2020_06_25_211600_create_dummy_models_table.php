<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDummyModelsTable extends Migration
{
    public function up()
    {
        Schema::create('dummy_models', function (Blueprint $table) {
            $table->id();
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
