@props(['modelo'])

@php use App\Support\Archivos; @endphp

<div {{ $attributes }}>
    <ul>
        @foreach ($modelo->adjuntos as $adjunto)
            <li class="flex items-baseline justify-between gap-4 border-b border-linea py-2 text-sm">
                <a href="{{ route('adjuntos.descargar', [$adjunto, 'ver' => 1]) }}" target="_blank" rel="noopener" class="enlace min-w-0 truncate">{{ $adjunto->nombre_original }}</a>
                <span class="flex shrink-0 items-baseline gap-3">
                    <span class="font-mono text-xs text-gris">{{ Archivos::tamanoLegible($adjunto->tamano) }}</span>
                    <x-eliminar :action="route('adjuntos.destroy', $adjunto)" pregunta="¿Eliminar este adjunto?" texto="×" class="inline" />
                </span>
            </li>
        @endforeach
    </ul>
    <form method="POST" action="{{ route('adjuntos.store') }}" enctype="multipart/form-data" class="mt-3" x-data="{ n: 0 }">
        @csrf
        <input type="hidden" name="adjuntable_type" value="{{ $modelo->getMorphClass() }}">
        <input type="hidden" name="adjuntable_id" value="{{ $modelo->getKey() }}">
        <label class="enlace cursor-pointer text-sm text-gris hover:text-tinta">
            <input type="file" name="archivos[]" multiple class="sr-only" x-on:change="n = $event.target.files.length; if (n) $el.form.requestSubmit()">
            <span x-text="n ? 'Subiendo…' : '+ Adjuntar archivo'">+ Adjuntar archivo</span>
        </label>
    </form>
</div>
