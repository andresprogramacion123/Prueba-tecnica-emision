<?php

use App\Enums\EstadoCaso;
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
        Schema::create('casos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_expediente', 50)->unique();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->date('fecha_inicio');
            $table->date('fecha_archivo')->nullable();
            $table->enum('estado', array_column(EstadoCaso::cases(), 'value'))
                ->default(EstadoCaso::EnTramite->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('casos');
    }
};
