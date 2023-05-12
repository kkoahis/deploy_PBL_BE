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
        Schema::create('hotel', function (Blueprint $table) {
            $table->id();
            // create by user
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->longText('description')->nullable();
            $table->string('address')->nullable();
            $table->string('hotline')->nullable();
            $table->integer('room_total')->nullable();
            $table->unsignedInteger('parking_slot')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->float('rating')->nullable();
            $table->timestamps();
            // soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel');
    }
};
