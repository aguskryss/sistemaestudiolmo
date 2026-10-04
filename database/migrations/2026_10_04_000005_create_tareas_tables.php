<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tareas del diagrama de Gantt de cada obra.
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->foreignId('rubro_id')->nullable()->constrained('rubros')->nullOnDelete();
            $table->foreignId('contacto_id')->nullable()->constrained('contactos')->nullOnDelete();
            $table->string('nombre');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->unsignedTinyInteger('avance')->default(0); // 0 a 100
            $table->boolean('es_hito')->default(false);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['obra_id', 'fecha_inicio']);
        });

        Schema::create('tarea_dependencias', function (Blueprint $table) {
            $table->foreignId('tarea_id')->constrained('tareas')->cascadeOnDelete();
            $table->foreignId('depende_de_id')->constrained('tareas')->cascadeOnDelete();
            $table->primary(['tarea_id', 'depende_de_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarea_dependencias');
        Schema::dropIfExists('tareas');
    }
};
