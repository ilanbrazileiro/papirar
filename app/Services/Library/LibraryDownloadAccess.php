<?php

namespace App\Services\Library;

use App\Models\CourseAccess;
use App\Models\LibraryDocument;
use App\Models\User;

class LibraryDownloadAccess
{
    public function allowed(LibraryDocument $document, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return CourseAccess::query()
            ->where('user_id', $user->id)
            ->where('course_id', $document->course_id)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->where('access_type', CourseAccess::TYPE_PAID)
                    ->orWhere(function ($manual) {
                        $manual->where('access_type', CourseAccess::TYPE_MANUAL)
                            ->where('allow_material_download', true);
                    });
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->exists();
    }
}
