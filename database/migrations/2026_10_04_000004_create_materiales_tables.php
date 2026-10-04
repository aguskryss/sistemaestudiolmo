<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materiales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('unidad', 20); // u, m2, m3, kg, bolsa, ml...
            $table->foreignId('rubro_id')->nullable()->constrained('rubros')->nullOnDelete();
            $table->timestamps();

            $table->unique(['nombre', 'unidad']);
        });

        // Un renglón por material en cada obra. Las cantidades permiten pedidos y entregas parciales;
        // el estado se recalcula a partir de ellas (necesito -> pedido -> entregado_parcial -> entregado).
        Schema::create('obra_materiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materiales')->restrictOnDelete();
            $table->foreignId('proveedor_id')->nullable()->constrained('contactos')->nullOnDelete();
            $table->decimal('cantidad_necesaria', 12, 2);
            $table->decimal('cantidad_pedida', 12, 2)->default(0);
            $table->decimal('cantidad_entregada', 12, 2)->default(0);
            $table->string('estado', 20)->default('necesito');
            $table->date('fecha_necesaria')->nullable();
            $table->date('fecha_entrega_estimada')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['obra_id', 'estado']);
        });

        // Historial: responde "¿dónde quedó?" con fecha, quién y remito.
        Schema::create('obra_material_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_material_id')->constrained('obra_materiales')->cascadeOnDelete();
            $table->string('tipo', 20); // pedido | entrega | ajuste
            $table->decimal('cantidad', 12, 2);
            $table->date('fecha');
            $table->string('remito', 50)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obra_material_movimientos');
        Schema::dropIfExists('obra_materiales');
        Schema::dropIfExists('materiales');
    }
};
