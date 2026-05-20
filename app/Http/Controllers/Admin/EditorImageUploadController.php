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
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        $file = $request->file('file');

        $filename = now()->format('YmdHis') . '-' . Str::random(16) . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('editor/questions', $filename, 'public');

        return response()->json([
            'location' => Storage::disk('public')->url($path),
        ]);
    }
}
