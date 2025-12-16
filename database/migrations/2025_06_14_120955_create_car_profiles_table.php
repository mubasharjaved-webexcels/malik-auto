<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('car_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('rec_no')->unique();
            $table->string('located_yard')->nullable();
            $table->string('grade')->nullable();
            $table->integer('seats')->nullable();
            $table->string('chassis')->nullable();
            $table->string('shift')->nullable();
            $table->integer('mileage')->nullable();
            $table->integer('engine_cc')->nullable();
            $table->string('dimension')->nullable();
            $table->decimal('m3', 8, 2)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('fuel')->nullable();
            $table->decimal('max_loading', 8, 2)->nullable();
            $table->string('country')->nullable();
            $table->string('car_image')->nullable();
            $table->string('car_video')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_profiles');
    }
};
