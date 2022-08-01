<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableForMaterial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
         // Create table for storing rotations
         Schema::create('production_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('batch_number')->nullable();
            $table->string('comments')->nullable();
            $table->date('start')->nullable();
            $table->date('end')->nullable();
            $table->timestamps();
        });
        // Create table for storing roles
        Schema::create('measurements', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->unsignedBigInteger('conversion_id');
            $table->string('name')->unique();
            $table->string('symbol')->nullable();
            $table->string('type')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        Schema::create('conversions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('measurement_id');
            $table->foreign('measurement_id')->references('id')->on('measurements')
            ->onUpdate('cascade')->onDelete('cascade');
                $table->string('value')->nullable();
            $table->timestamps();
        });

        Schema::create('material_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });

        // Create table for storing roles
        Schema::create('materials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('material_category_id');
            $table->unsignedBigInteger('measurement_id');
            $table->string('name');
            $table->foreign('material_category_id')->references('id')->on('material_categories')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('measurement_id')->references('id')->on('measurements')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->integer('unit_cost')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
        // Create table for storing rotations
        Schema::create('into_store', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('material_id');
            $table->unsignedBigInteger('measurement_id');
            $table->foreign('material_id')->references('id')->on('materials')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->integer('qty')->nullable();
            $table->integer('comments')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
       
          // Create table for storing rotations
          Schema::create('out_store', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('material_id');
            $table->unsignedBigInteger('measurement_id');
            $table->unsignedBigInteger('production_session_id');
            $table->foreign('material_id')->references('id')->on('materials')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('measurement_id')->references('id')->on('measurements')
                ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('production_session_id')->references('id')->on('production_sessions')
                    ->onUpdate('cascade')->onDelete('cascade');
            $table->integer('qty')->nullable();
            $table->integer('comments')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('measurements');
        Schema::dropIfExists('conversions');
        Schema::dropIfExists('material_categories');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('into_store');
        Schema::dropIfExists('production_session_info');
        Schema::dropIfExists('out_store');
    }
}
