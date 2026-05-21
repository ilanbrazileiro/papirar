<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EditorImageUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'upload' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ], [
            'upload.required' => 'Nenhuma imagem foi enviada.',
            'upload.image' => 'O arquivo enviado precisa ser uma imagem.',
            'upload.mimes' => 'Formato inválido. Use JPG, PNG, GIF ou WEBP.',
            'upload.max' => 'A imagem deve ter no máximo 5MB.',
        ]);

        $file = $request->file('upload');

        if (!$file || !$file->isValid()) {
            throw ValidationException::withMessages([
                'upload' => 'Upload inválido.',
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
        $filename = now()->format('YmdHis') . '-' . Str::random(16) . '.' . $extension;

        $path = $file->storeAs('editor/questions', $filename, 'public');

        $url = Storage::url($path);

        return response()->json([
            'location' => $url,
            'url' => $url,
        ]);
    }
}
