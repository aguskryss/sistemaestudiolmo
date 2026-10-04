<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20)->default('persona'); // persona | empresa
            $table->string('nombre');
            $table->string('cuit_dni', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('direccion')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('nombre');
        });

        Schema::create('obras', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->unique(); // "Obra N° 014"
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->string('localidad')->nullable();
            $table->string('tipo', 50)->nullable(); // vivienda, comercial, reforma...
            $table->string('estado', 20)->default('proyecto');
            $table->decimal('superficie_m2', 10, 2)->nullable();
            $table->date('fecha_inicio_prevista')->nullable();
            $table->date('fecha_inicio_real')->nullable();
            $table->date('fecha_fin_prevista')->nullable();
            $table->date('fecha_fin_real')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
        });

        // Checklist de inicio de obra: plantilla configurable que se copia a cada obra nueva.
        Schema::create('checklist_plantilla_items', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('obra_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->string('descripcion');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamp('completado_en')->nullable();
            $table->foreignId('completado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obra_checklist_items');
        Schema::dropIfExists('checklist_plantilla_items');
        Schema::dropIfExists('obras');
        Schema::dropIfExists('clientes');
    }
};
