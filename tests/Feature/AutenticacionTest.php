<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Tests\TestCase;

class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invitado_es_redirigido_al_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/perfil')->assertRedirect('/login');
    }

    public function test_la_pantalla_de_login_se_muestra_con_cabeceras_de_seguridad(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Ingresar')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_no_existe_el_registro_publico(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }

    public function test_un_usuario_puede_ingresar_y_ve_el_inicio(): void
    {
        $user = User::factory()->create(['name' => 'Laura Martínez']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->get('/')->assertOk()->assertSee('Laura Martínez')->assertSee('Obras en curso');
        $this->assertDatabaseHas('actividad', ['user_id' => $user->id, 'accion' => 'login']);
    }

    public function test_el_email_no_distingue_mayusculas(): void
    {
        $user = User::factory()->create(['email' => 'laura@estudio.com']);

        $this->post('/login', ['email' => 'Laura@Estudio.com', 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_contrasena_incorrecta_no_ingresa_y_queda_registrado(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'incorrecta'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(1, Actividad::where('accion', 'login_fallido')->count());
    }

    public function test_un_usuario_desactivado_no_puede_ingresar(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['activo' => false])->save();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertGuest();
    }

    public function test_un_usuario_desactivado_con_sesion_abierta_es_expulsado(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $user->forceFill(['activo' => false])->save();

        $this->get('/')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_el_login_se_bloquea_despues_de_5_intentos(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $i) {
            $this->post('/login', ['email' => $user->email, 'password' => 'mal']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertTooManyRequests();
        $this->assertGuest();
    }

    public function test_con_dos_pasos_activado_pide_el_codigo(): void
    {
        $user = User::factory()->create();
        $secreto = app(TwoFactorAuthenticationProvider::class)->generateSecretKey();
        $user->forceFill([
            'two_factor_secret' => encrypt($secreto),
            'two_factor_recovery_codes' => encrypt(json_encode(['codigo-1'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/two-factor-challenge');
        $this->assertGuest();

        $this->get('/two-factor-challenge')->assertOk()->assertSee('Verificación');

        $codigo = app(\PragmaRX\Google2FA\Google2FA::class)->getCurrentOtp($secreto);
        $this->post('/two-factor-challenge', ['code' => $codigo])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_activar_dos_pasos_desde_el_perfil(): void
    {
        $user = User::factory()->create()->fresh();
        $this->actingAs($user);

        // Sin confirmar contraseña, el perfil ofrece confirmarla.
        $this->get('/perfil')->assertOk()->assertSee('Confirmar contraseña');

        $this->withSession(['auth.password_confirmed_at' => time()])
            ->post('/user/two-factor-authentication')
            ->assertRedirect();

        $this->get('/perfil')->assertOk()->assertSee('Escaneá este código');
        $this->assertNotNull($user->fresh()->two_factor_secret);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_cambiar_datos_del_perfil(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/user/profile-information', ['name' => 'Nuevo Nombre', 'email' => 'NUEVO@estudio.com'])
            ->assertSessionHasNoErrors();

        $this->assertSame('nuevo@estudio.com', $user->fresh()->email);
    }

    public function test_la_contrasena_nueva_debe_ser_segura(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/user/password', [
                'current_password' => 'password',
                'password' => 'corta',
                'password_confirmation' => 'corta',
            ])
            ->assertSessionHasErrorsIn('updatePassword', 'password');
    }
}
