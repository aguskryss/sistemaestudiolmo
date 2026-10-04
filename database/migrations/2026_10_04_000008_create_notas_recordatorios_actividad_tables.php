<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('obra_id')->nullable()->constrained('obras')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('contenido');
            $table->boolean('fijada')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Recordatorios por email. Pueden atarse a cualquier entidad (obra, permiso, seguro, cotización...).
        // Los automáticos (vencimientos) los genera el sistema y se marcan con automatico = true.
        Schema::create('recordatorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // destinatario
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableMorphs('recordable');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_hora');
            $table->string('repeticion', 20)->nullable(); // diaria | semanal | mensual | anual
            $table->boolean('automatico')->default(false);
            $table->timestamp('enviado_en')->nullable();
            $table->timestamp('completado_en')->nullable();
            $table->timestamps();

            $table->index(['fecha_hora', 'enviado_en']);
        });

        Schema::create('actividad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 30); // creado | actualizado | eliminado | descargado | login...
            $table->nullableMorphs('sujeto');
            $table->json('cambios')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad');
        Schema::dropIfExists('recordatorios');
        Schema::dropIfExists('notas');
    }
};
