<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tipo = recibida (de un gremio/proveedor -> contacto_id)
        //      | emitida  (del estudio a un cliente -> cliente_id)
        // obra_id es opcional: una cotización emitida puede existir antes de que la obra se concrete.
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20);
            $table->string('numero', 30)->nullable();
            $table->string('titulo');
            $table->foreignId('obra_id')->nullable()->constrained('obras')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('contacto_id')->nullable()->constrained('contactos')->nullOnDelete();
            $table->foreignId('rubro_id')->nullable()->constrained('rubros')->nullOnDelete();
            $table->date('fecha');
            $table->date('valida_hasta')->nullable();
            $table->char('moneda', 3)->default('ARS'); // ARS | USD
            $table->decimal('tipo_cambio', 12, 4)->nullable(); // cotización del dólar usada, si aplica
            $table->decimal('total', 14, 2)->default(0);
            $table->string('estado', 20)->default('borrador'); // borrador | pendiente | aceptada | rechazada | vencida
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tipo', 'estado']);
            $table->index(['obra_id', 'rubro_id']);
        });

        Schema::create('cotizacion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->cascadeOnDelete();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->string('descripcion');
            $table->string('unidad', 20)->nullable();
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->decimal('precio_unitario', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_items');
        Schema::dropIfExists('cotizaciones');
    }
};
