<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDummyHasManyModelsTable extends Migration
{
    public function up()
    {
        Schema::create('dummy_has_many_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('dummy_model_id');
            $table->string('name', 64);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dummy_has_many_models');
    }
}
