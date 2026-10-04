@props(['valor' => '', 'placeholder' => 'Buscar…'])

<form method="GET" {{ $attributes->class('flex flex-wrap items-end gap-6') }} role="search">
    <div class="w-full max-w-xs">
        <label for="q" class="rotulo-texto text-gris">Buscar</label>
        <input id="q" name="q" type="search" value="{{ $valor }}" placeholder="{{ $placeholder }}" class="campo mt-1">
    </div>
    {{ $slot }}
    <button type="submit" class="btn btn-linea btn-chico">Filtrar</button>
    @if (request()->query())
        <a href="{{ url()->current() }}" class="enlace pb-2 text-sm text-gris hover:text-tinta">Limpiar</a>
    @endif
</form>
