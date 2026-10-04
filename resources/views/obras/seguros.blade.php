<x-layouts.obra :obra="$obra" seccion="Seguros">
    @if ($sinSeguro->isNotEmpty())
        <div class="mb-12 border border-dashed border-tinta p-5">
            <p class="rotulo-texto">Atención</p>
            <p class="mt-2">Estos gremios tienen tareas en la obra y no tienen un seguro vigente asociado:</p>
            <ul class="mt-3 flex flex-wrap gap-2">
                @foreach ($sinSeguro as $contacto)
                    <li><a href="{{ route('contactos.show', $contacto) }}" class="etiqueta etiqueta-alerta hover:bg-hueso">{{ $contacto->nombre }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($disponibles->isNotEmpty())
        <form method="POST" action="{{ route('obras.seguros.vincular', $obra) }}" class="mb-12 flex max-w-2xl flex-wrap items-end gap-4">
            @csrf
            <div class="min-w-72 flex-1"><x-select name="seguro_id" label="Asociar un seguro vigente" placeholder="Elegí uno" :options="$disponibles" required /></div>
            <button type="submit" class="btn btn-chico">Asociar</button>
        </form>
    @endif
    <p class="-mt-8 mb-12 text-sm text-gris">Los seguros se cargan desde la ficha de cada <a href="{{ route('contactos.index', ['tipo' => 'gremio']) }}" class="enlace">contacto</a>.</p>

    @if ($obra->seguros->isEmpty())
        <x-vacio>Ningún seguro asociado a esta obra.</x-vacio>
    @else
        <div class="overflow-x-auto">
            <table class="tabla">
                <thead>
                    <tr><th>Gremio</th><th>Tipo</th><th>Aseguradora</th><th>Vigencia</th><th>Estado</th><th>Archivos</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($obra->seguros as $seguro)
                        @php [$texto, $clase] = $seguro->situacion(); @endphp
                        <tr>
                            <td><a href="{{ route('contactos.show', $seguro->contacto) }}" class="enlace">{{ $seguro->contacto->nombre }}</a></td>
                            <td class="text-sm">{{ $seguro->tipo->label() }}</td>
                            <td class="text-sm">{{ $seguro->aseguradora }}@if ($seguro->numero_poliza)<div class="font-mono text-xs text-gris">{{ $seguro->numero_poliza }}</div>@endif</td>
                            <td class="font-mono text-sm whitespace-nowrap">{{ $seguro->vigencia_desde->format('d.m.y') }} → {{ $seguro->vigencia_hasta->format('d.m.y') }}</td>
                            <td><span class="{{ $clase }}">{{ $texto }}</span></td>
                            <td class="text-sm">
                                @foreach ($seguro->adjuntos as $adjunto)
                                    <a href="{{ route('adjuntos.descargar', [$adjunto, 'ver' => 1]) }}" target="_blank" rel="noopener" class="enlace block truncate">{{ $adjunto->nombre_original }}</a>
                                @endforeach
                            </td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('obras.seguros.desvincular', [$obra, $seguro]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="enlace cursor-pointer text-sm text-gris hover:text-tinta">Quitar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.obra>
