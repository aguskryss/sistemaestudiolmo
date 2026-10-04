<?php

namespace App\Http\Controllers;

use App\Models\AgendaTarea;
use App\Models\Obra;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Agenda de tareas de cada arquitecto. Cada uno se carga las suyas; un admin puede asignarlas a cualquiera.
 */
class AgendaController extends Controller
{
    public function index(Request $request): View
    {
        $usuarios = User::where('activo', true)->orderBy('name')->get();
        $usuario = $usuarios->firstWhere('id', $request->integer('usuario')) ?? $request->user();

        return view('agenda.index', [
            'usuario' => $usuario,
            'usuarios' => $usuarios,
            'obras' => self::obrasParaSelect(),
            'pendientes' => AgendaTarea::with('obra')
                ->where('user_id', $usuario->id)
                ->whereNull('completada_en')
                ->whereDate('fecha', '<', today()->startOfWeek())
                ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '<', today()))
                ->orderBy('fecha')
                ->get(),
        ] + self::semana($request, $usuario));
    }

    /**
     * Datos de la semana para el calendario (también lo usa el Inicio).
     *
     * @return array{dias: Collection, tareas: Collection, desde: CarbonImmutable, anterior: string, siguiente: string}
     */
    public static function semana(Request $request, User $usuario): array
    {
        $referencia = rescue(fn () => CarbonImmutable::parse($request->query('semana')), today()->toImmutable(), false);
        $desde = $referencia->startOfWeek();
        $hasta = $desde->endOfWeek();

        return [
            'desde' => $desde,
            'dias' => collect(range(0, 6))->map(fn ($i) => $desde->addDays($i)),
            'tareas' => AgendaTarea::with(['obra', 'creadoPor'])
                ->where('user_id', $usuario->id)
                ->entre($desde, $hasta)
                ->orderByRaw('hora IS NULL')
                ->orderBy('hora')
                ->get(),
            'anterior' => $desde->subWeek()->format('Y-m-d'),
            'siguiente' => $desde->addWeek()->format('Y-m-d'),
        ];
    }

    public static function obrasParaSelect(): Collection
    {
        return Obra::where('estado', '!=', 'terminada')->orderByDesc('numero')->get()
            ->mapWithKeys(fn ($o) => [$o->id => $o->codigo_corto.' · '.$o->nombre]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        $tarea = new AgendaTarea(collect($datos)->except('user_id')->all());
        $tarea->user_id = $request->user()->esAdmin() ? ($datos['user_id'] ?? $request->user()->id) : $request->user()->id;
        $tarea->creado_por = $request->user()->id;
        $tarea->save();

        return back()->with('status', 'Tarea agendada.');
    }

    public function update(Request $request, AgendaTarea $tarea): RedirectResponse
    {
        $this->autorizar($request->user(), $tarea);
        $datos = $this->validar($request);

        $tarea->fill(collect($datos)->except('user_id')->all());
        if ($request->user()->esAdmin() && ! empty($datos['user_id'])) {
            $tarea->user_id = $datos['user_id'];
        }
        $tarea->save();

        return back()->with('status', 'Tarea actualizada.');
    }

    public function completar(Request $request, AgendaTarea $tarea): RedirectResponse
    {
        $this->autorizar($request->user(), $tarea);
        $tarea->forceFill(['completada_en' => $tarea->completada_en ? null : now()])->save();

        return back();
    }

    public function destroy(Request $request, AgendaTarea $tarea): RedirectResponse
    {
        $this->autorizar($request->user(), $tarea);
        $tarea->delete();

        return back()->with('status', 'Tarea eliminada.');
    }

    /** La puede tocar quien la tiene asignada, quien la creó o un admin. */
    private function autorizar(User $user, AgendaTarea $tarea): void
    {
        abort_unless($user->esAdmin() || $tarea->user_id === $user->id || $tarea->creado_por === $user->id, 403);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'fecha' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha'],
            'hora' => ['nullable', 'date_format:H:i'],
            'obra_id' => ['nullable', Rule::exists('obras', 'id')],
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('activo', true)],
        ], [], ['fecha_fin' => 'hasta']);
    }
}
