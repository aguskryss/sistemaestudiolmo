@props(['action', 'pregunta' => '¿Eliminar este registro?', 'texto' => 'Eliminar'])

<form method="POST" action="{{ $action }}" x-data x-on:submit="if (! confirm(@js($pregunta))) $event.preventDefault()" {{ $attributes->only('class') }}>
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->except('class')->merge(['class' => 'enlace cursor-pointer text-sm text-gris hover:text-tinta']) }}>{{ $texto }}</button>
</form>
