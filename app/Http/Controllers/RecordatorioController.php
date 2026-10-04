<?php

namespace App\Http\Controllers;

use App\Enums\Repeticion;
use App\Models\Obra;
use App\Models\Recordatorio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecordatorioController extends Controller
{
    public function index(Request $request): View
    {
        $ver = $request->query('ver', 'pendientes');

        $recordatorios = Recordatorio::query()
            ->with(['usuario', 'recordable'])
            ->where('user_id', $request->user()->id)
            ->when($ver === 'pendientes', fn ($q) => $q->whereNull('completado_en')->orderBy('fecha_hora'))
            ->when($ver === 'completados', fn ($q) => $q->whereNotNull('completado_en')->orderByDesc('completado_en'))
            ->paginate(50)
            ->withQueryString();

        return view('recordatorios.index', [
            'recordatorios' => $recordatorios,
            'ver' => $ver,
            'usuarios' => User::where('activo', true)->orderBy('name')->pluck('name', 'id'),
            'obras' => Obra::where('estado', '!=', 'terminada')->orderByDesc('numero')->get()->mapWithKeys(fn ($o) => [$o->id => $o->codigo.' · '.$o->nombre]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i'],
            'repeticion' => ['nullable', Rule::enum(Repeticion::class)],
            'para' => ['required', 'array', 'min:1'],
            'para.*' => [Rule::exists('users', 'id')->where('activo', true)],
            'obra_id' => ['nullable', Rule::exists('obras', 'id')],
        ]);

        foreach (array_unique($datos['para']) as $userId) {
            $recordatorio = new Recordatorio([
                'user_id' => $userId,
                'titulo' => $datos['titulo'],
                'descripcion' => $datos['descripcion'] ?? null,
                'fecha_hora' => $datos['fecha'].' '.$datos['hora'],
                'repeticion' => $datos['repeticion'] ?? null,
                'recordable_type' => ! empty($datos['obra_id']) ? 'obra' : null,
                'recordable_id' => $datos['obra_id'] ?? null,
            ]);
            $recordatorio->creado_por = $request->user()->id;
            $recordatorio->save();
        }

        return back()->with('status', 'Recordatorio creado. Llega por email en la fecha y hora indicadas.');
    }

    public function completar(Request $request, Recordatorio $recordatorio): RedirectResponse
    {
        abort_unless($recordatorio->user_id === $request->user()->id, 403);

        $recordatorio->forceFill(['completado_en' => $recordatorio->completado_en ? null : now()])->save();

        return back();
    }

    public function destroy(Request $request, Recordatorio $recordatorio): RedirectResponse
    {
        abort_unless($recordatorio->user_id === $request->user()->id || $recordatorio->creado_por === $request->user()->id, 403);

        $recordatorio->delete();

        return back()->with('status', 'Recordatorio eliminado.');
    }
}
