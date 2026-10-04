@php
    use App\Enums\EstadoCotizacion;
    use App\Support\Formato;
    $mostrarObra ??= true;
    $claseEstado = fn (EstadoCotizacion $e) => match ($e) {
        EstadoCotizacion::Aceptada => 'etiqueta etiqueta-llena',
        EstadoCotizacion::Rechazada, EstadoCotizacion::Vencida => 'etiqueta etiqueta-tenue',
        EstadoCotizacion::Borrador => 'etiqueta etiqueta-alerta',
        default => 'etiqueta',
    };
@endphp

<div class="overflow-x-auto">
    <table class="tabla">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Cotización</th>
                <th>Tipo</th>
                <th>De / Para</th>
                @if ($mostrarObra) <th>Obra</th> @endif
                <th class="num">Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cotizaciones as $c)
                <tr>
                    <td class="font-mono text-sm whitespace-nowrap">{{ $c->fecha->format('d.m.Y') }}</td>
                    <td>
                        <a href="{{ route('cotizaciones.show', $c) }}" class="enlace">{{ $c->titulo }}</a>
                        <div class="text-sm text-gris">{{ collect([$c->numero ? 'N° '.$c->numero : null, $c->rubro?->nombre])->filter()->join(' · ') }}</div>
                    </td>
                    <td><span class="etiqueta {{ $c->tipo->value === 'emitida' ? 'etiqueta-llena' : '' }}">{{ $c->tipo->label() }}</span></td>
                    <td class="text-sm">
                        @if ($c->tipo->value === 'recibida')
                            {{ $c->contacto?->nombre ?? '—' }}
                        @else
                            {{ $c->estudio?->nombre ?? $c->cliente?->nombre ?? '—' }}
                        @endif
                    </td>
                    @if ($mostrarObra)
                        <td class="text-sm">{{ $c->obra ? $c->obra->codigo : '—' }}</td>
                    @endif
                    <td class="num">{{ Formato::dinero($c->total, $c->moneda) }}</td>
                    <td><span class="{{ $claseEstado($c->estado) }}">{{ $c->estado->label() }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
