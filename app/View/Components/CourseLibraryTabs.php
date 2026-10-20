<?php

namespace App\View\Components;

use App\Models\Course;
use App\Models\LibraryDocument;
use App\Services\Library\LibraryDownloadAccess;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CourseLibraryTabs extends Component
{
    public function __construct(public Course $course)
    {
    }

    public function render(): View
    {
        $documents = LibraryDocument::query()
            ->where('course_id', $this->course->id)
            ->where('is_published', true)
            ->whereIn('type', ['commented_pdf', 'official_exam'])
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->get();

        $access = app(LibraryDownloadAccess::class);
        $allowed = [];
        foreach ($documents as $document) {
            $allowed[$document->id] = $access->allowed($document, auth()->user());
        }

        return view('components.course-library-tabs', compact('documents', 'allowed'));
    }
}
