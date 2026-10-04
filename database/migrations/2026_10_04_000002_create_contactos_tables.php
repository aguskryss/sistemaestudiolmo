<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rubros', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        Schema::create('contactos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20)->default('gremio'); // gremio | proveedor | profesional | otro
            $table->string('nombre');
            $table->string('empresa')->nullable();
            $table->string('cuit', 20)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();
            $table->unsignedTinyInteger('calificacion')->nullable(); // 1 a 5, uso interno
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('nombre');
        });

        Schema::create('contacto_rubro', function (Blueprint $table) {
            $table->foreignId('contacto_id')->constrained('contactos')->cascadeOnDelete();
            $table->foreignId('rubro_id')->constrained('rubros')->cascadeOnDelete();
            $table->primary(['contacto_id', 'rubro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacto_rubro');
        Schema::dropIfExists('contactos');
        Schema::dropIfExists('rubros');
    }
};
