<?php

use App\Support\Colores;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rubros', fn (Blueprint $t) => $t->string('color', 20)->nullable()->after('nombre'));
        Schema::table('tareas', fn (Blueprint $t) => $t->string('color', 20)->nullable()->after('nombre'));

        // Colores iniciales para los rubros existentes, rotando la paleta.
        $paleta = array_keys(Colores::PALETA);
        DB::table('rubros')->orderBy('nombre')->pluck('id')->each(function ($id, $i) use ($paleta) {
            DB::table('rubros')->where('id', $id)->update(['color' => $paleta[$i % count($paleta)]]);
        });
    }

    public function down(): void
    {
        Schema::table('tareas', fn (Blueprint $t) => $t->dropColumn('color'));
        Schema::table('rubros', fn (Blueprint $t) => $t->dropColumn('color'));
    }
};
