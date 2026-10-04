<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Valores iniciales de las listas configurables. */
    private const OPCIONES = [
        'tipo_obra' => ['Obra nueva', 'Reforma integral', 'Reforma parcial'],
        'tipo_permiso' => ['Permiso de obra', 'Registro de planos', 'Permiso de demolición', 'Aviso de obra', 'Conexión de agua', 'Conexión de cloacas', 'Conexión eléctrica', 'Conexión de gas', 'Final de obra', 'Habilitación'],
        'unidad' => ['u', 'bolsa', 'kg', 'tn', 'm', 'm²', 'm³', 'l', 'barra', 'rollo', 'caja', 'pallet', 'gl'],
    ];

    public function up(): void
    {
        // 1. Listas configurables (ABM) para los selects simples.
        Schema::create('opciones', function (Blueprint $table) {
            $table->id();
            $table->string('grupo', 30);
            $table->string('nombre', 100);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['grupo', 'nombre']);
        });

        $ahora = now();
        foreach (self::OPCIONES as $grupo => $nombres) {
            foreach ($nombres as $i => $nombre) {
                DB::table('opciones')->insert(['grupo' => $grupo, 'nombre' => $nombre, 'orden' => $i, 'created_at' => $ahora, 'updated_at' => $ahora]);
            }
        }

        Schema::table('rubros', fn (Blueprint $t) => $t->boolean('activo')->default(true)->after('nombre'));
        Schema::table('materiales', fn (Blueprint $t) => $t->boolean('activo')->default(true)->after('rubro_id'));

        // 2. Varios contactos por estudio.
        Schema::create('estudio_contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudio_id')->constrained('estudios')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('cargo', 100)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        DB::table('estudios')->whereNotNull('contacto_nombre')->orderBy('id')->each(function ($e) use ($ahora) {
            DB::table('estudio_contactos')->insert([
                'estudio_id' => $e->id, 'nombre' => $e->contacto_nombre, 'telefono' => $e->contacto_telefono,
                'email' => $e->contacto_email, 'created_at' => $ahora, 'updated_at' => $ahora,
            ]);
        });

        Schema::table('estudios', fn (Blueprint $t) => $t->dropColumn(['contacto_nombre', 'contacto_telefono', 'contacto_email']));

        // 3. Obras: cliente opcional, tipo desde la lista, contacto del estudio para la obra.
        Schema::table('obras', function (Blueprint $table) {
            $table->foreignId('cliente_id')->nullable()->change();
            $table->foreignId('tipo_obra_id')->nullable()->after('tipo')->constrained('opciones')->nullOnDelete();
            $table->foreignId('estudio_contacto_id')->nullable()->after('codigo_estudio')->constrained('estudio_contactos')->nullOnDelete();
        });
        $this->pasarTextoAOpcion('obras', 'tipo', 'tipo_obra_id', 'tipo_obra');
        Schema::table('obras', fn (Blueprint $t) => $t->dropColumn('tipo'));

        // 4. Permisos: tipo desde la lista.
        Schema::table('permisos', function (Blueprint $table) {
            $table->foreignId('tipo_permiso_id')->nullable()->after('obra_id')->constrained('opciones')->nullOnDelete();
        });
        $this->pasarTextoAOpcion('permisos', 'tipo', 'tipo_permiso_id', 'tipo_permiso');
        Schema::table('permisos', fn (Blueprint $t) => $t->dropColumn('tipo'));

        // 5. Notas de obras sin cliente.
        Schema::table('notas', fn (Blueprint $t) => $t->foreignId('cliente_id')->nullable()->change());

        // 6. Las tareas del Gantt ya no dependen unas de otras (pueden ser simultáneas).
        Schema::dropIfExists('tarea_dependencias');

        // 7. Agenda: tareas de cada arquitecto.
        Schema::create('agenda_tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // a quién está asignada
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('obra_id')->nullable()->constrained('obras')->nullOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->date('fecha_fin')->nullable(); // para tareas de varios días
            $table->time('hora')->nullable();
            $table->timestamp('completada_en')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_tareas');

        Schema::create('tarea_dependencias', function (Blueprint $table) {
            $table->foreignId('tarea_id')->constrained('tareas')->cascadeOnDelete();
            $table->foreignId('depende_de_id')->constrained('tareas')->cascadeOnDelete();
            $table->primary(['tarea_id', 'depende_de_id']);
        });

        Schema::table('permisos', fn (Blueprint $t) => $t->string('tipo')->default('')->after('obra_id'));
        Schema::table('permisos', fn (Blueprint $t) => $t->dropConstrainedForeignId('tipo_permiso_id'));

        Schema::table('obras', function (Blueprint $t) {
            $t->string('tipo', 50)->nullable();
            $t->dropConstrainedForeignId('tipo_obra_id');
            $t->dropConstrainedForeignId('estudio_contacto_id');
        });

        Schema::table('estudios', function (Blueprint $t) {
            $t->string('contacto_nombre')->nullable();
            $t->string('contacto_telefono', 50)->nullable();
            $t->string('contacto_email')->nullable();
        });
        Schema::dropIfExists('estudio_contactos');

        Schema::table('materiales', fn (Blueprint $t) => $t->dropColumn('activo'));
        Schema::table('rubros', fn (Blueprint $t) => $t->dropColumn('activo'));
        Schema::dropIfExists('opciones');
    }

    /** Convierte los textos libres existentes en opciones de la lista (creándolas si hace falta). */
    private function pasarTextoAOpcion(string $tabla, string $columna, string $fk, string $grupo): void
    {
        DB::table($tabla)->whereNotNull($columna)->where($columna, '!=', '')->orderBy('id')->each(function ($fila) use ($tabla, $columna, $fk, $grupo) {
            $nombre = trim($fila->{$columna});
            $id = DB::table('opciones')->where('grupo', $grupo)->where('nombre', $nombre)->value('id')
                ?? DB::table('opciones')->insertGetId(['grupo' => $grupo, 'nombre' => $nombre, 'orden' => 99, 'created_at' => now(), 'updated_at' => now()]);
            DB::table($tabla)->where('id', $fila->id)->update([$fk => $id]);
        });
    }
};
