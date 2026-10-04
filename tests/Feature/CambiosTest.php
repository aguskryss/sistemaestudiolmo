<?php

namespace Tests\Feature;

use App\Enums\Rol;
use App\Models\AgendaTarea;
use App\Models\Estudio;
use App\Models\Material;
use App\Models\Obra;
use App\Models\Opcion;
use App\Models\Rubro;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Pedidos de cambio: obras sin cliente/estudio, varios contactos por estudio, ABM de listas, agenda. */
class CambiosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('rol', Rol::Admin)->firstOrFail();
        $this->actingAs($this->admin);
    }

    public function test_obra_sin_cliente_ni_estudio_en_cotizacion(): void
    {
        $tipo = Opcion::where('grupo', 'tipo_obra')->where('nombre', 'Reforma integral')->value('id');

        $this->post('/obras', ['nombre' => 'Reforma sin cliente', 'estado' => 'en_cotizacion', 'tipo_obra_id' => $tipo])->assertRedirect();
        $obra = Obra::firstOrFail();

        $this->assertNull($obra->cliente_id);
        $this->assertNull($obra->estudio_id);
        $this->get("/obras/{$obra->id}")->assertOk()->assertSee('En cotización')->assertSee('Reforma integral');
        $this->get('/obras?estado=en_cotizacion')->assertOk()->assertSee('Reforma sin cliente');

        // Notas en una obra sin cliente
        $this->post("/obras/{$obra->id}/notas", ['contenido' => 'Esperando planos'])->assertRedirect();
        $this->get("/obras/{$obra->id}")->assertSee('Esperando planos');

        foreach (['/archivos', '/materiales', '/calendario', '/cotizaciones', '/permisos', '/seguros', '/edit'] as $tab) {
            $this->get("/obras/{$obra->id}{$tab}")->assertOk();
        }
    }

    public function test_tipos_de_obra_iniciales(): void
    {
        $this->assertEqualsCanonicalizing(
            ['Obra nueva', 'Reforma integral', 'Reforma parcial'],
            Opcion::where('grupo', 'tipo_obra')->pluck('nombre')->all()
        );
    }

    public function test_estudio_con_varios_contactos_y_referente_por_obra(): void
    {
        $this->post('/estudios', ['nombre' => 'Estudio Sur', 'contactos' => [
            ['nombre' => 'Ana', 'cargo' => 'Socia', 'email' => 'ana@sur.com'],
            ['nombre' => 'Beto', 'telefono' => '11 2222'],
            ['nombre' => ''], // fila vacía: se ignora
        ]])->assertRedirect();

        $estudio = Estudio::firstOrFail();
        $this->assertSame(['Ana', 'Beto'], $estudio->contactos->pluck('nombre')->all());
        $beto = $estudio->contactos->firstWhere('nombre', 'Beto');

        $this->post('/obras', ['nombre' => 'Casa', 'estado' => 'proyecto', 'estudio_id' => $estudio->id, 'estudio_contacto_id' => $beto->id])->assertRedirect();
        $this->get('/obras/'.Obra::first()->id)->assertSee('Beto');

        // Un contacto de otro estudio no se acepta
        $otro = Estudio::create(['nombre' => 'Otro']);
        $this->post('/obras', ['nombre' => 'X', 'estado' => 'proyecto', 'estudio_id' => $otro->id, 'estudio_contacto_id' => $beto->id])->assertSessionHasErrors('estudio_contacto_id');

        // Editar: se quita a Ana, se cambia a Beto, se agrega Carla
        $ana = $estudio->contactos->firstWhere('nombre', 'Ana');
        $this->put("/estudios/{$estudio->id}", ['nombre' => 'Estudio Sur', 'contactos' => [
            ['id' => $beto->id, 'nombre' => 'Beto Gómez'],
            ['nombre' => 'Carla'],
        ]])->assertRedirect();
        $this->assertSame(['Beto Gómez', 'Carla'], $estudio->contactos()->pluck('nombre')->all());
        $this->assertModelMissing($ana);
        $this->assertSame($beto->id, Obra::first()->estudio_contacto_id);
    }

    public function test_abm_de_listas_desactiva_lo_que_esta_en_uso(): void
    {
        $this->get('/configuracion')->assertOk()->assertSee('Obra nueva');

        $this->post('/configuracion/tipo_obra', ['nombre' => 'Ampliación'])->assertRedirect();
        $ampliacion = Opcion::where('nombre', 'Ampliación')->firstOrFail();
        $this->post('/configuracion/tipo_obra', ['nombre' => 'Ampliación'])->assertSessionHasErrors('nombre');

        $this->post('/obras', ['nombre' => 'Casa', 'estado' => 'proyecto', 'tipo_obra_id' => $ampliacion->id]);

        // En uso → se desactiva y deja de ofrecerse
        $this->delete("/configuracion/tipo_obra/{$ampliacion->id}")->assertSessionHas('status', fn ($s) => str_contains($s, 'desactivó'));
        $this->assertFalse($ampliacion->fresh()->activo);
        $this->assertFalse(Opcion::lista('tipo_obra')->has($ampliacion->id));
        // ...pero la obra que lo usa lo sigue mostrando al editar
        $this->assertTrue(Opcion::lista('tipo_obra', $ampliacion->id)->has($ampliacion->id));

        // Sin uso → se borra
        $libre = Opcion::where('nombre', 'Reforma parcial')->firstOrFail();
        $this->delete("/configuracion/tipo_obra/{$libre->id}");
        $this->assertModelMissing($libre);

        // Renombrar una unidad la actualiza en el catálogo
        Material::create(['nombre' => 'Cemento', 'unidad' => 'bolsa']);
        $bolsa = Opcion::where('grupo', 'unidad')->where('nombre', 'bolsa')->firstOrFail();
        $this->put("/configuracion/unidad/{$bolsa->id}", ['nombre' => 'bolsa 50kg', 'activo' => 1])->assertRedirect();
        $this->assertSame('bolsa 50kg', Material::first()->unidad);

        // Rubros, materiales y checklist
        $this->post('/configuracion/rubros', ['nombre' => 'Durlock'])->assertRedirect();
        $this->assertTrue(Rubro::where('nombre', 'Durlock')->exists());
        $this->post('/configuracion/materiales', ['nombre' => 'Arena', 'unidad' => 'm³'])->assertRedirect();
        $this->post('/configuracion/checklist', ['descripcion' => 'Obrador instalado'])->assertRedirect();
        foreach (array_keys(\App\Http\Controllers\ConfiguracionController::SECCIONES) as $seccion) {
            $this->get("/configuracion/{$seccion}")->assertOk();
        }
        $this->get('/configuracion/inventada')->assertNotFound();
    }

    public function test_material_del_catalogo_o_nuevo_y_sin_proveedor(): void
    {
        $this->post('/obras', ['nombre' => 'Casa', 'estado' => 'en_obra']);
        $obra = Obra::firstOrFail();
        $arena = Material::create(['nombre' => 'Arena', 'unidad' => 'm³']);

        $this->post("/obras/{$obra->id}/materiales", ['material_id' => $arena->id, 'cantidad_necesaria' => 5])->assertSessionHasNoErrors();
        $this->post("/obras/{$obra->id}/materiales", ['nombre' => 'Cal', 'unidad' => 'bolsa', 'cantidad_necesaria' => 10])->assertSessionHasNoErrors();
        $this->post("/obras/{$obra->id}/materiales", ['nombre' => 'Raro', 'unidad' => 'inventada', 'cantidad_necesaria' => 1])->assertSessionHasErrors('unidad');

        $this->assertSame(2, $obra->materiales()->whereNull('proveedor_id')->count());
        $this->assertTrue(Material::where('nombre', 'Cal')->exists());
        $this->get("/obras/{$obra->id}/materiales")->assertOk()->assertSee('Sin proveedor');
    }

    public function test_colores_de_tareas_por_rubro_o_propios(): void
    {
        $this->post('/obras', ['nombre' => 'Casa', 'estado' => 'en_obra']);
        $obra = Obra::firstOrFail();
        $electrica = Rubro::where('nombre', 'Instalación eléctrica')->firstOrFail();
        $this->assertNotNull($electrica->color, 'los rubros existentes reciben un color inicial');

        $base = ['fecha_inicio' => '2026-10-10', 'fecha_fin' => '2026-10-20'];
        $this->post("/obras/{$obra->id}/tareas", $base + ['nombre' => 'Cableado', 'rubro_id' => $electrica->id])->assertSessionHasNoErrors();
        $this->post("/obras/{$obra->id}/tareas", $base + ['nombre' => 'Especial', 'rubro_id' => $electrica->id, 'color' => 'rosa'])->assertSessionHasNoErrors();
        $this->post("/obras/{$obra->id}/tareas", $base + ['nombre' => 'Mala', 'color' => 'fucsia-fluo'])->assertSessionHasErrors('color');

        $this->assertSame($electrica->color, $obra->tareas()->where('nombre', 'Cableado')->first()->colorEfectivo());
        $this->assertSame('rosa', $obra->tareas()->where('nombre', 'Especial')->first()->colorEfectivo());

        $this->get("/obras/{$obra->id}/calendario")->assertOk()
            ->assertSee('color-'.$electrica->color)
            ->assertSee('color-rosa')
            ->assertSee('Instalación eléctrica'); // en la leyenda

        // Ninguna directiva Blade tiene que llegar sin procesar al navegador
        foreach (["/obras/{$obra->id}/calendario", "/obras/{$obra->id}/permisos", '/', '/agenda'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('@js(', false);
        }

        // Cambiar el color del rubro desde Configuración
        $this->put("/configuracion/rubros/{$electrica->id}", ['nombre' => $electrica->nombre, 'color' => 'azul', 'activo' => 1])->assertSessionHasNoErrors();
        $this->assertSame('azul', $obra->tareas()->where('nombre', 'Cableado')->first()->colorEfectivo());
    }

    public function test_agenda_admin_asigna_y_miembro_solo_a_si_mismo(): void
    {
        $miembro = User::factory()->create(['name' => 'Juli']);

        // El admin le asigna una tarea a Juli
        $this->post('/agenda', ['titulo' => 'Medir terreno', 'fecha' => today()->format('Y-m-d'), 'hora' => '10:00', 'user_id' => $miembro->id])->assertRedirect();
        $tarea = AgendaTarea::firstOrFail();
        $this->assertSame($miembro->id, $tarea->user_id);
        $this->assertSame($this->admin->id, $tarea->creado_por);

        // Juli la ve en su inicio y la completa
        $this->actingAs($miembro->fresh())->get('/')->assertOk()->assertSee('Mi semana')->assertSee('Medir terreno');
        $this->patch("/agenda/{$tarea->id}/completar")->assertRedirect();
        $this->assertNotNull($tarea->fresh()->completada_en);

        // Juli intenta asignarle algo al admin: queda para ella misma
        $this->post('/agenda', ['titulo' => 'Para el jefe', 'fecha' => today()->format('Y-m-d'), 'user_id' => $this->admin->id]);
        $this->assertSame($miembro->id, AgendaTarea::where('titulo', 'Para el jefe')->value('user_id'));

        // Juli no puede borrar tareas ajenas
        $delAdmin = new AgendaTarea(['titulo' => 'Privada', 'fecha' => today()]);
        $delAdmin->user_id = $delAdmin->creado_por = $this->admin->id;
        $delAdmin->save();
        $this->delete("/agenda/{$delAdmin->id}")->assertForbidden();

        // Tarea de varios días aparece en cada día de la semana
        $this->post('/agenda', ['titulo' => 'Viaje a obra', 'fecha' => today()->startOfWeek()->format('Y-m-d'), 'fecha_fin' => today()->startOfWeek()->addDays(2)->format('Y-m-d')]);
        $this->get('/agenda')->assertOk()->assertSee('Viaje a obra');
        $this->get('/agenda?semana='.today()->addWeek()->format('Y-m-d'))->assertOk()->assertDontSee('Viaje a obra');
        $this->get('/agenda?semana=basura')->assertOk();
    }
}
