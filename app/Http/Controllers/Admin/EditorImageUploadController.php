<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditorImageUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'upload' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        $file = $request->file('upload');
        $filename = now()->format('YmdHis') . '-' . Str::random(16) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('editor/questions', $filename, 'public');

        return response()->json([
            'url' => asset(Storage::url($path)),
        ]);
    }
}
