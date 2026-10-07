<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitchens', function (Blueprint $table) {
            $table->id();

            $table->string('id', 30)->unique();

            $table->string('name', 150);

            $table->text('address')->nullable();

            $table->string('person_in_charge', 100)->nullable();

            $table->string('phone', 30)->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchens');
    }
};