<?php
namespace App\Services\Library;
use App\Models\CourseAccess;
use App\Models\LibraryDocument;
use App\Models\User;
class LibraryDownloadAccess
{
    public function allowed(LibraryDocument $document, ?User $user): bool
    {
        if (!$user) return false;
        $courseIds = $document->type === 'commented_pdf'
            ? app(CommentedMaterialScope::class)->matchingCourseIds($document)
            : [$document->course_id];
        if (!$courseIds) return false;
        return CourseAccess::query()->where('user_id', $user->id)
            ->whereIn('course_id', $courseIds)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(function ($q) {
                $q->where('access_type', CourseAccess::TYPE_PAID)
                  ->orWhere(fn($manual) => $manual->where('access_type', CourseAccess::TYPE_MANUAL)->where('allow_material_download', true));
            })
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))->exists();
    }
}
