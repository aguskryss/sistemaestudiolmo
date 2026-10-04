@php $esActual = $actual?->id === $carpeta->id; @endphp

<li>
    <a href="{{ route('obras.archivos', [$obra, 'carpeta' => $carpeta->id]) }}"
       style="padding-left: {{ 0.75 + $nivel * 1 }}rem"
       @class(['flex items-baseline justify-between gap-3 py-2 pr-3', 'bg-tinta text-papel' => $esActual, 'hover:bg-hueso' => ! $esActual])>
        <span class="truncate">{{ $nivel ? '└ ' : '' }}{{ $carpeta->nombre }}</span>
        <span @class(['font-mono text-xs', 'text-papel/60' => $esActual, 'text-gris' => ! $esActual])>{{ $carpeta->documentos_count }}</span>
    </a>
</li>
@foreach ($hijas->get($carpeta->id, collect()) as $hija)
    @include('obras._carpeta', ['carpeta' => $hija, 'nivel' => $nivel + 1])
@endforeach
