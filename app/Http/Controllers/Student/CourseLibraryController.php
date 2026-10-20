<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\LibraryDocument;
use App\Services\Library\LibraryDownloadAccess;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseLibraryController extends Controller
{
    public function index(Request $request, Course $course, LibraryDownloadAccess $permissions): View
    {
        $access = CourseAccess::query()
            ->where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->exists();
        abort_unless($access, 403);

        $documents = LibraryDocument::query()
            ->where('course_id', $course->id)
            ->where('is_published', true)
            ->whereIn('type', ['official_exam', 'commented_pdf'])
            ->orderByRaw("CASE WHEN type = 'official_exam' THEN 0 ELSE 1 END")
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->get();

        $allowed = [];
        foreach ($documents as $document) {
            $allowed[$document->id] = $permissions->allowed($document, $request->user());
        }
        return view('student.courses.materials', compact('course', 'documents', 'allowed'));
    }
}
