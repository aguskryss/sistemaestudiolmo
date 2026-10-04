<?php

namespace Tests\Feature;

use App\Enums\Rol;
use App\Mail\RecordatorioMail;
use App\Models\Actividad;
use App\Models\Cliente;
use App\Models\Contacto;
use App\Models\Cotizacion;
use App\Models\Documento;
use App\Models\Estudio;
use App\Models\Obra;
use App\Models\ObraMaterial;
use App\Models\Recordatorio;
use App\Models\Rubro;
use App\Models\Seguro;
use App\Models\Tarea;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModulosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('rol', Rol::Admin)->firstOrFail();
        $this->actingAs($this->admin);
        Storage::fake('local');
    }

    private function crearObra(array $extra = []): Obra
    {
        $estudio = Estudio::create(['nombre' => 'Estudio Norte', 'contacto_nombre' => 'Ana']);
        $cliente = Cliente::create(['tipo' => 'persona', 'nombre' => 'Familia Gómez']);

        $this->post('/obras', $extra + [
            'nombre' => 'Vivienda Gómez',
            'estudio_id' => $estudio->id,
            'codigo_estudio' => 'EN-22',
            'cliente_id' => $cliente->id,
            'estado' => 'en_obra',
        ])->assertRedirect();

        return Obra::latest('id')->firstOrFail();
    }

    public function test_solo_el_admin_gestiona_usuarios(): void
    {
        $this->post('/usuarios', ['name' => 'Juan', 'email' => 'JUAN@estudio.com', 'rol' => 'miembro'])
            ->assertRedirect('/usuarios')
            ->assertSessionHas('status', fn ($s) => str_contains($s, 'Contraseña temporal'));

        $juan = User::where('email', 'juan@estudio.com')->firstOrFail();
        $this->actingAs($juan)->get('/usuarios')->assertForbidden();
        $this->actingAs($juan)->get('/')->assertOk()->assertDontSee('Usuarios</');
    }

    public function test_no_puede_quedar_sin_administradores(): void
    {
        $this->put("/usuarios/{$this->admin->id}", ['name' => 'A', 'email' => $this->admin->email, 'rol' => 'miembro'])
            ->assertSessionHasErrors('rol');
    }

    public function test_crear_obra_subcontratada_con_checklist_y_carpetas(): void
    {
        $obra = $this->crearObra();

        $this->assertSame(1, $obra->numero);
        $this->assertSame('Obra N° 001', $obra->codigo);
        $this->assertSame('Estudio Norte', $obra->estudio->nombre);
        $this->assertSame(8, $obra->checklist()->count());
        $this->assertSame(4, $obra->carpetas()->count());
        $this->assertTrue(Actividad::where('accion', 'creado')->where('sujeto_type', 'obra')->exists());

        foreach (['', '/archivos', '/materiales', '/calendario', '/cotizaciones', '/permisos', '/seguros', '/edit'] as $tab) {
            $this->get("/obras/{$obra->id}{$tab}")->assertOk()->assertSee('Vivienda Gómez');
        }

        $this->get('/obras')->assertOk()->assertSee('Vivienda Gómez')->assertSee('EN-22');
        $this->get('/obras?estudio=directas')->assertOk()->assertDontSee('Vivienda Gómez');
        $this->get("/estudios/{$obra->estudio_id}")->assertOk()->assertSee('Vivienda Gómez');
        $this->get("/clientes/{$obra->cliente_id}")->assertOk()->assertSee('Vivienda Gómez');
    }

    public function test_obra_directa_sin_estudio(): void
    {
        $cliente = Cliente::create(['tipo' => 'empresa', 'nombre' => 'Local SRL']);
        $this->post('/obras', ['nombre' => 'Local', 'cliente_id' => $cliente->id, 'estado' => 'proyecto'])->assertRedirect();

        $this->get('/obras?estudio=directas')->assertOk()->assertSee('Obra directa');
    }

    public function test_checklist_y_notas(): void
    {
        $obra = $this->crearObra();
        $item = $obra->checklist()->first();

        $this->patch("/checklist/{$item->id}")->assertRedirect();
        $this->assertNotNull($item->fresh()->completado_en);

        $this->post("/clientes/{$obra->cliente_id}/notas", ['contenido' => 'Quiere piso de madera', 'obra_id' => $obra->id])->assertRedirect();
        $this->get("/obras/{$obra->id}")->assertSee('Quiere piso de madera');
    }

    public function test_archivos_con_versiones(): void
    {
        $obra = $this->crearObra();
        $carpeta = $obra->carpetas()->where('nombre', 'Planos')->first();

        $subir = fn (UploadedFile $f) => $this->post("/obras/{$obra->id}/archivos", ['carpeta_id' => $carpeta->id, 'archivos' => [$f]]);

        $subir(UploadedFile::fake()->createWithContent('Planta baja.pdf', 'version uno'))->assertRedirect();
        $subir(UploadedFile::fake()->createWithContent('planta BAJA.pdf', 'version dos'))->assertSessionHas('status', fn ($s) => str_contains($s, 'versión'));
        $subir(UploadedFile::fake()->createWithContent('Planta baja.pdf', 'version dos'))->assertSessionHas('status', fn ($s) => str_contains($s, 'sin cambios'));
        $subir(UploadedFile::fake()->createWithContent('Planta baja.dwg', 'cad'))->assertRedirect();

        $this->assertSame(2, Documento::count());
        $pdf = Documento::where('nombre', 'Planta baja.pdf')->first();
        $this->assertSame(2, $pdf->versiones()->count());
        $this->assertSame(2, $pdf->versionActual->numero);

        $this->get("/versiones/{$pdf->versionActual->id}/descargar")->assertOk()->assertDownload('planta BAJA.pdf');
        $this->get("/obras/{$obra->id}/archivos?carpeta={$carpeta->id}")->assertOk()->assertSee('v2');

        $subir(UploadedFile::fake()->create('virus.exe', 10))->assertSessionHasErrors('archivos.0');
    }

    public function test_los_archivos_requieren_sesion(): void
    {
        $obra = $this->crearObra();
        $carpeta = $obra->carpetas()->first();
        $this->post("/obras/{$obra->id}/archivos", ['carpeta_id' => $carpeta->id, 'archivos' => [UploadedFile::fake()->create('a.pdf', 5)]]);
        $version = Documento::first()->versionActual;

        auth()->logout();
        $this->get("/versiones/{$version->id}/descargar")->assertRedirect('/login');
    }

    public function test_materiales_necesito_pedi_entregaron(): void
    {
        $obra = $this->crearObra();
        $proveedor = Contacto::create(['tipo' => 'proveedor', 'nombre' => 'Corralón Sur']);

        $this->post("/obras/{$obra->id}/materiales", ['nombre' => 'Cemento', 'unidad' => 'bolsa', 'cantidad_necesaria' => 100, 'fecha_necesaria' => today()->format('Y-m-d')])->assertRedirect();
        $item = ObraMaterial::firstOrFail();
        $this->assertSame('necesito', $item->estado->value);

        $this->post("/materiales/{$item->id}/movimientos", ['tipo' => 'pedido', 'cantidad' => 100, 'fecha' => today()->format('Y-m-d'), 'proveedor_id' => $proveedor->id]);
        $this->assertSame('pedido', $item->fresh()->estado->value);
        $this->assertSame($proveedor->id, $item->fresh()->proveedor_id);

        $this->post("/materiales/{$item->id}/movimientos", ['tipo' => 'entrega', 'cantidad' => 60, 'fecha' => today()->format('Y-m-d'), 'remito' => 'R-1']);
        $this->assertSame('entregado_parcial', $item->fresh()->estado->value);

        $this->post("/materiales/{$item->id}/movimientos", ['tipo' => 'entrega', 'cantidad' => 40, 'fecha' => today()->format('Y-m-d')]);
        $this->assertSame('entregado', $item->fresh()->estado->value);

        $this->get("/obras/{$obra->id}/materiales")->assertOk()->assertSee('Cemento')->assertSee('R-1');
    }

    public function test_gantt_tareas_y_dependencias(): void
    {
        $obra = $this->crearObra();
        $base = ['fecha_inicio' => '2026-10-10', 'fecha_fin' => '2026-10-20'];

        $this->post("/obras/{$obra->id}/tareas", $base + ['nombre' => 'Platea'])->assertRedirect();
        $platea = Tarea::firstOrFail();
        $this->post("/obras/{$obra->id}/tareas", ['nombre' => 'Mampostería', 'fecha_inicio' => '2026-10-21', 'fecha_fin' => '2026-11-15', 'dependencias' => [$platea->id]])->assertRedirect();

        $mamposteria = Tarea::where('nombre', 'Mampostería')->firstOrFail();
        $this->assertTrue($mamposteria->dependeDe->contains($platea));

        $this->patchJson("/tareas/{$platea->id}/mover", ['fecha_inicio' => '2026-10-12', 'fecha_fin' => '2026-10-22'])->assertOk();
        $this->patchJson("/tareas/{$platea->id}/mover", ['avance' => 40])->assertOk();
        $this->assertSame('2026-10-12', $platea->fresh()->fecha_inicio->format('Y-m-d'));
        $this->assertSame(40, $platea->fresh()->avance);
        $this->patchJson("/tareas/{$platea->id}/mover", ['fecha_inicio' => '2026-10-30', 'fecha_fin' => '2026-10-01'])->assertUnprocessable();

        $this->get("/obras/{$obra->id}/calendario")->assertOk()->assertSee('Platea');
        $this->get('/calendario')->assertOk()->assertSee('1 obra(s)');
    }

    public function test_cotizaciones_recibidas_emitidas_y_comparacion(): void
    {
        $obra = $this->crearObra();
        $rubro = Rubro::where('nombre', 'Instalación eléctrica')->first();
        $a = Contacto::create(['nombre' => 'Electricista A']);
        $b = Contacto::create(['nombre' => 'Electricista B']);

        $this->post('/cotizaciones', [
            'tipo' => 'recibida', 'titulo' => 'Eléctrica', 'contacto_id' => $a->id, 'obra_id' => $obra->id, 'rubro_id' => $rubro->id,
            'fecha' => today()->format('Y-m-d'), 'moneda' => 'ARS', 'estado' => 'pendiente',
            'items' => [['descripcion' => 'Boca', 'unidad' => 'u', 'cantidad' => 40, 'precio_unitario' => 25000]],
        ])->assertRedirect();
        $this->assertEquals(1000000, Cotizacion::first()->total);

        $this->post('/cotizaciones', [
            'tipo' => 'recibida', 'titulo' => 'Eléctrica', 'contacto_id' => $b->id, 'obra_id' => $obra->id, 'rubro_id' => $rubro->id,
            'fecha' => today()->format('Y-m-d'), 'moneda' => 'ARS', 'estado' => 'pendiente', 'total' => 850000,
        ])->assertRedirect();

        $this->get("/obras/{$obra->id}/cotizaciones")->assertOk()->assertSee('Comparación por rubro')->assertSee('Menor');

        // Emitida: hace falta a quién va dirigida.
        $emitida = ['tipo' => 'emitida', 'titulo' => 'Dirección de obra', 'fecha' => today()->format('Y-m-d'), 'moneda' => 'USD', 'estado' => 'borrador', 'total' => 3000];
        $this->post('/cotizaciones', $emitida)->assertSessionHasErrors('cliente_id');
        $this->post('/cotizaciones', $emitida + ['estudio_id' => $obra->estudio_id])->assertRedirect();

        $c = Cotizacion::where('tipo', 'emitida')->firstOrFail();
        $this->get("/cotizaciones/{$c->id}")->assertOk()->assertSee('US$ 3.000,00');
        $this->patch("/cotizaciones/{$c->id}/estado", ['estado' => 'aceptada'])->assertRedirect();
        $this->get('/cotizaciones?tipo=emitida')->assertOk()->assertSee('Dirección de obra')->assertDontSee('Electricista A');
    }

    public function test_contactos_seguros_y_alerta_de_gremio_sin_seguro(): void
    {
        $obra = $this->crearObra();
        $rubro = Rubro::first();

        $this->post('/contactos', ['tipo' => 'gremio', 'nombre' => 'Pedro Albañil', 'rubros' => [$rubro->id]])->assertRedirect();
        $pedro = Contacto::where('nombre', 'Pedro Albañil')->firstOrFail();
        $this->assertTrue($pedro->rubros->contains($rubro));

        $obra->tareas()->create(['nombre' => 'Muros', 'fecha_inicio' => today(), 'fecha_fin' => today()->addWeek(), 'contacto_id' => $pedro->id]);
        $this->get("/obras/{$obra->id}/seguros")->assertOk()->assertSee('Atención')->assertSee('Pedro Albañil');

        $this->post("/contactos/{$pedro->id}/seguros", [
            'tipo' => 'art', 'aseguradora' => 'Prevención ART', 'vigencia_desde' => today()->subMonth()->format('Y-m-d'),
            'vigencia_hasta' => today()->addDays(10)->format('Y-m-d'), 'obras' => [$obra->id],
        ])->assertRedirect();

        $this->get("/obras/{$obra->id}/seguros")->assertOk()->assertDontSee('Atención')->assertSee('Prevención ART');
        $this->get('/contactos?rubro='.$rubro->id)->assertOk()->assertSee('Pedro Albañil')->assertSee('Al día');

        $seguro = Seguro::firstOrFail();
        $this->post('/adjuntos', ['adjuntable_type' => 'seguro', 'adjuntable_id' => $seguro->id, 'archivos' => [UploadedFile::fake()->create('poliza.pdf', 20)]])->assertRedirect();
        $this->get("/contactos/{$pedro->id}")->assertOk()->assertSee('poliza.pdf');
        $this->post('/adjuntos', ['adjuntable_type' => 'user', 'adjuntable_id' => 1, 'archivos' => [UploadedFile::fake()->create('x.pdf', 1)]])->assertSessionHasErrors('adjuntable_type');
    }

    public function test_permisos(): void
    {
        $obra = $this->crearObra();
        $this->post("/obras/{$obra->id}/permisos", ['tipo' => 'Permiso de obra', 'organismo' => 'Municipio', 'estado' => 'presentado', 'fecha_vencimiento' => today()->addDays(20)->format('Y-m-d')])->assertRedirect();

        $this->get("/obras/{$obra->id}/permisos")->assertOk()->assertSee('Permiso de obra')->assertSee('Presentado');
        $this->get('/')->assertOk()->assertSee('Permiso de obra');
    }

    public function test_recordatorios_por_email_y_vencimientos_automaticos(): void
    {
        Mail::fake();
        $obra = $this->crearObra();

        $this->post('/recordatorios', ['titulo' => 'Llamar al plomero', 'fecha' => today()->format('Y-m-d'), 'hora' => '00:00', 'para' => [$this->admin->id], 'obra_id' => $obra->id])->assertRedirect();
        $this->post('/recordatorios', ['titulo' => 'Revisar obra', 'fecha' => today()->subDay()->format('Y-m-d'), 'hora' => '09:00', 'repeticion' => 'semanal', 'para' => [$this->admin->id]])->assertRedirect();

        $this->artisan('recordatorios:enviar')->assertSuccessful();
        Mail::assertSent(RecordatorioMail::class, 2);

        $this->assertNotNull(Recordatorio::where('titulo', 'Llamar al plomero')->first()->enviado_en);
        $semanal = Recordatorio::where('titulo', 'Revisar obra')->first();
        $this->assertNull($semanal->enviado_en);
        $this->assertTrue($semanal->fecha_hora->isFuture());

        // Seguro que vence pronto → recordatorio automático, sin duplicar.
        $gremio = Contacto::create(['nombre' => 'Gas SRL']);
        $gremio->seguros()->create(['tipo' => 'art', 'aseguradora' => 'X', 'vigencia_desde' => today()->subYear(), 'vigencia_hasta' => today()->addDays(5)]);
        $this->artisan('recordatorios:vencimientos')->assertSuccessful();
        $this->artisan('recordatorios:vencimientos')->assertSuccessful();
        $this->assertSame(1, Recordatorio::where('automatico', true)->count());

        $this->get('/recordatorios')->assertOk()->assertSee('Vence el seguro de Gas SRL');
    }

    public function test_inicio_con_datos(): void
    {
        $obra = $this->crearObra();
        $obra->tareas()->create(['nombre' => 'Replanteo', 'fecha_inicio' => today(), 'fecha_fin' => today()->addDays(2)]);

        $this->get('/')->assertOk()->assertSee('Replanteo')->assertSee('Esta semana en obra');
    }
}
