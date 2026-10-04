<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->string('tipo'); // permiso de obra, demolición, conexión de servicios...
            $table->string('organismo')->nullable();
            $table->string('numero_expediente', 50)->nullable();
            $table->string('estado', 20)->default('a_presentar'); // a_presentar | presentado | observado | aprobado | vencido
            $table->date('fecha_presentacion')->nullable();
            $table->date('fecha_aprobacion')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('fecha_vencimiento');
        });

        Schema::create('seguros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contacto_id')->constrained('contactos')->cascadeOnDelete();
            $table->string('tipo', 20); // art | ap | rc | otro
            $table->string('aseguradora');
            $table->string('numero_poliza', 50)->nullable();
            $table->date('vigencia_desde');
            $table->date('vigencia_hasta');
            $table->decimal('suma_asegurada', 14, 2)->nullable();
            $table->char('moneda', 3)->default('ARS');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('vigencia_hasta');
        });

        // En qué obras está cubierto cada seguro.
        Schema::create('obra_seguro', function (Blueprint $table) {
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->foreignId('seguro_id')->constrained('seguros')->cascadeOnDelete();
            $table->primary(['obra_id', 'seguro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obra_seguro');
        Schema::dropIfExists('seguros');
        Schema::dropIfExists('permisos');
    }
};
