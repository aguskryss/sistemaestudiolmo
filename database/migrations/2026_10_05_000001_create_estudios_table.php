<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Estudios que nos subcontratan. Una obra puede venir de un estudio (subcontratada)
        // o ser directa (estudio_id null). El cliente es siempre el comitente final.
        Schema::create('estudios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('razon_social')->nullable();
            $table->string('cuit', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('direccion')->nullable();
            $table->string('contacto_nombre')->nullable();
            $table->string('contacto_telefono', 50)->nullable();
            $table->string('contacto_email')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('nombre');
        });

        Schema::table('obras', function (Blueprint $table) {
            $table->foreignId('estudio_id')->nullable()->after('cliente_id')->constrained('estudios')->restrictOnDelete();
            $table->string('codigo_estudio', 50)->nullable()->after('estudio_id'); // cómo identifica la obra el estudio contratante
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->foreignId('estudio_id')->nullable()->after('cliente_id')->constrained('estudios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('estudio_id');
        });
        Schema::table('obras', function (Blueprint $table) {
            $table->dropConstrainedForeignId('estudio_id');
            $table->dropColumn('codigo_estudio');
        });
        Schema::dropIfExists('estudios');
    }
};
