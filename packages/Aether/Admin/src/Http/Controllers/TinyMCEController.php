<?php

namespace Aether\Admin\Http\Controllers;

use Aether\Core\Traits\Sanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TinyMCEController extends Controller
{
    use Sanitizer;

    /**
     * Ruta de la carpeta de almacenamiento.
     */
    private string $storagePath = 'tinymce';

    /**
     * Sube el archivo desde tinymce.
     */
    public function upload(): JsonResponse
    {
        $media = $this->storeMedia();

        if (! empty($media)) {
            return response()->json([
                'location' => $media['file_url'],
            ]);
        }

        return response()->json([]);
    }

    /**
     * Almacenar medios.
     */
    public function storeMedia(): array
    {
        if (! request()->hasFile('file')) {
            return [];
        }

        $file = request()->file('file');

        if (! $file instanceof UploadedFile) {
            return [];
        }

        $filename = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs($this->storagePath, $filename);

        $this->sanitizeSVG($path, $file);

        return [
            'file' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_url' => Storage::url($path),
        ];
    }
}
