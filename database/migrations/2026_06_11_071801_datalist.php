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
        Schema::create('datalist', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('ip_address', 15);
            $table->string('sato_ip', 15);
            $table->string('model', 20);
            $table->string('fixed_value', 10);
            $table->unsignedTinyInteger('quantity');
            $table->string('lot', 15);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datalist');
    }
};
