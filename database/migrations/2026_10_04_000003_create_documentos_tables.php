<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carpetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('carpetas')->cascadeOnDelete();
            $table->string('nombre');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        // Un documento es el "archivo lógico" (ej: "Planta baja"); cada subida crea una versión.
        // La versión vigente es siempre la de número más alto.
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->foreignId('carpeta_id')->constrained('carpetas')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['carpeta_id', 'nombre']);
        });

        Schema::create('documento_versiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')->constrained('documentos')->cascadeOnDelete();
            $table->unsignedInteger('numero');
            $table->string('ruta'); // relativa al disco privado, fuera de public_html
            $table->string('nombre_original');
            $table->string('mime', 150);
            $table->unsignedBigInteger('tamano');
            $table->char('hash', 64);
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('comentario')->nullable();
            $table->timestamps();

            $table->unique(['documento_id', 'numero']);
        });

        // Adjuntos sueltos para permisos, seguros, cotizaciones, etc. (polimórfico).
        Schema::create('adjuntos', function (Blueprint $table) {
            $table->id();
            $table->morphs('adjuntable');
            $table->string('ruta');
            $table->string('nombre_original');
            $table->string('mime', 150);
            $table->unsignedBigInteger('tamano');
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjuntos');
        Schema::dropIfExists('documento_versiones');
        Schema::dropIfExists('documentos');
        Schema::dropIfExists('carpetas');
    }
};
