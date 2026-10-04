<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;padding:0;background:#f5f5f3;font-family:Helvetica,Arial,sans-serif;color:#111111;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f3;padding:32px 16px;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border:1px solid #111111;">
                <tr><td style="padding:20px 28px;border-bottom:1px solid #111111;font-family:Georgia,serif;font-size:22px;">{{ config('app.name') }}</td></tr>
                <tr><td style="padding:28px;">
                    <p style="margin:0;font-family:'Courier New',monospace;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#6e6e6a;">Recordatorio · {{ $recordatorio->fecha_hora->format('d.m.Y H:i') }}</p>
                    <h1 style="margin:12px 0 0;font-family:Georgia,serif;font-weight:normal;font-size:28px;line-height:1.2;">{{ $recordatorio->titulo }}</h1>
                    @if ($recordatorio->descripcion)
                        <p style="margin:16px 0 0;font-size:15px;line-height:1.5;white-space:pre-line;">{{ $recordatorio->descripcion }}</p>
                    @endif
                    <p style="margin:28px 0 0;">
                        <a href="{{ route('recordatorios.index') }}" style="display:inline-block;background:#111111;color:#ffffff;text-decoration:none;padding:12px 20px;font-family:'Courier New',monospace;font-size:12px;letter-spacing:2px;text-transform:uppercase;">Ver en el sistema</a>
                    </p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
