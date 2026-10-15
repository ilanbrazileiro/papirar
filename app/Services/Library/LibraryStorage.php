<?php
namespace App\Services\Library;

use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

class LibraryStorage
{
    public function disk(): FilesystemAdapter
    {
        if (! config('papirar_library.disk.key') || ! config('papirar_library.disk.secret') || ! config('papirar_library.disk.endpoint')) {
            throw new \RuntimeException('Credenciais R2 não configuradas.');
        }
        return Storage::build(config('papirar_library.disk'));
    }
}
