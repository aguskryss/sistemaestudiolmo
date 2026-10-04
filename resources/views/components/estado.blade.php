@php
    $mensajes = [
        'profile-information-updated' => 'Datos actualizados.',
        'password-updated' => 'Contraseña actualizada.',
        'two-factor-authentication-enabled' => 'Escaneá el código y confirmá para terminar de activar la verificación.',
        'two-factor-authentication-confirmed' => 'Verificación en dos pasos activada.',
        'two-factor-authentication-disabled' => 'Verificación en dos pasos desactivada.',
        'recovery-codes-generated' => 'Se generaron códigos de recuperación nuevos.',
    ];
    $estado = session('status');
    $texto = $mensajes[$estado] ?? $estado;
@endphp

@if ($texto)
    <div {{ $attributes->class('border-l-2 border-tinta bg-hueso px-4 py-3 text-sm') }} role="status">
        {{ $texto }}
    </div>
@endif
