<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('room', function (Blueprint $table) {
            $table->id();
            $table->foreignid('category_id')->constrained('category')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name');
            $table->float('size');
            $table->string('bed');
            $table->longText('bathroom_facilities');
            $table->longText('amenities');
            $table->longText('directions_view');
            $table->string('description');
            $table->double('price');
            $table->integer('max_people');
            // room status :true is available, false is not available
            $table->boolean('status')->default(true);
            $table->boolean('is_smoking')->default(false);
            $table->timestamps();
            // soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room');
    }
};
