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
        Schema::create('caso_abogado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caso_id')->constrained('casos');
            $table->foreignId('abogado_id')->constrained('abogados');
            $table->timestamps();

            $table->unique(['caso_id', 'abogado_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caso_abogado');
    }
};
