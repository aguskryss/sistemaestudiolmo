<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Guarda y entrega archivos del disco privado (storage/app/private, fuera de public_html).
 */
class Archivos
{
    /** Extensiones permitidas: planos, documentos, imágenes y comprimidos. */
    public const EXTENSIONES = [
        'pdf', 'dwg', 'dxf', 'dwf', 'skp', 'rvt', 'ifc', '3dm',
        'doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx', 'txt', 'odt', 'ods',
        'jpg', 'jpeg', 'png', 'webp', 'heic', 'gif', 'tif', 'tiff',
        'zip', 'rar', '7z',
    ];

    /** Tamaño máximo por archivo, en KB (100 MB). */
    public const MAX_KB = 102400;

    /** Tipos que se pueden abrir en el navegador; el resto siempre se descarga. */
    private const VISTA_EN_LINEA = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public static function reglas(): array
    {
        return ['file', 'max:'.self::MAX_KB, 'extensions:'.implode(',', self::EXTENSIONES)];
    }

    /**
     * @return array{ruta: string, nombre_original: string, mime: string, tamano: int, hash: string}
     */
    public static function guardar(UploadedFile $archivo, string $carpeta): array
    {
        $extension = Str::lower($archivo->getClientOriginalExtension());
        $nombre = Str::uuid().($extension ? ".{$extension}" : '');

        return [
            'ruta' => $archivo->storeAs($carpeta, $nombre, 'local'),
            'nombre_original' => Str::limit(basename($archivo->getClientOriginalName()), 250, ''),
            'mime' => $archivo->getMimeType() ?? 'application/octet-stream',
            'tamano' => $archivo->getSize(),
            'hash' => hash_file('sha256', $archivo->getRealPath()),
        ];
    }

    public static function entregar(string $ruta, string $nombre, string $mime, bool $enLinea = false): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($ruta), 404);

        $disposicion = $enLinea && in_array($mime, self::VISTA_EN_LINEA, true) ? 'inline' : 'attachment';

        return Storage::disk('local')->response($ruta, $nombre, [
            'Content-Type' => $disposicion === 'inline' ? $mime : 'application/octet-stream',
            'Cache-Control' => 'private, no-store',
        ], $disposicion);
    }

    public static function eliminar(?string $ruta): void
    {
        if ($ruta) {
            Storage::disk('local')->delete($ruta);
        }
    }

    public static function tamanoLegible(int $bytes): string
    {
        return match (true) {
            $bytes >= 1048576 => number_format($bytes / 1048576, 1, ',', '.').' MB',
            $bytes >= 1024 => number_format($bytes / 1024, 0, ',', '.').' KB',
            default => $bytes.' B',
        };
    }
}
