<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditorImageUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'upload' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ], [
            'upload.required' => 'Envie uma imagem.',
            'upload.image' => 'O arquivo enviado precisa ser uma imagem.',
            'upload.mimes' => 'Use imagens JPG, PNG, WEBP ou GIF.',
            'upload.max' => 'A imagem deve ter no máximo 5MB.',
        ]);

        $file = $request->file('upload');
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = now()->format('YmdHis') . '-' . Str::random(12) . '.' . $extension;

        $path = $file->storeAs('questions/editor-images/' . now()->format('Y/m'), $filename, 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
