<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseAccessController extends Controller
{
    public function index(Request $request): View
    {
        $accesses = CourseAccess::query()
            ->with(['user', 'course', 'subscription.transactions'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('cpf', 'like', "%{$search}%");
                    })->orWhereHas('course', function ($courseQuery) use ($search) {
                        $courseQuery->where('title', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('access_type'), fn ($query) => $query->where('access_type', $request->string('access_type')->toString()))
            ->when($request->filled('course_id'), fn ($query) => $query->where('course_id', $request->integer('course_id')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $courses = Course::query()->orderBy('title')->get();
        $statuses = CourseAccess::statusOptions();
        $accessTypes = CourseAccess::accessTypeOptions();

        return view('admin.course-accesses.index', compact('accesses', 'courses', 'statuses', 'accessTypes'));
    }

    public function create(): View
    {
        $access = new CourseAccess([
            'status' => CourseAccess::STATUS_ACTIVE,
            'access_type' => CourseAccess::TYPE_MANUAL,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'bonus_days' => 0,
            'cancel_at_period_end' => false,
        ]);

        return view('admin.course-accesses.create', $this->formData($access));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['access_type'] = $data['access_type'] ?? CourseAccess::TYPE_MANUAL;
        $data['cancel_at_period_end'] = (bool) ($data['cancel_at_period_end'] ?? false);
        $data['bonus_days'] = (int) ($data['bonus_days'] ?? 0);

        CourseAccess::query()->create($data);

        return redirect()
            ->route('admin.course-accesses.index')
            ->with('success', 'Acesso ao curso concedido com sucesso.');
    }

    public function edit(CourseAccess $courseAccess): View
    {
        $courseAccess->load(['user', 'course', 'subscription.transactions']);

        return view('admin.course-accesses.edit', $this->formData($courseAccess));
    }

    public function update(Request $request, CourseAccess $courseAccess): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['access_type'] = $data['access_type'] ?? CourseAccess::TYPE_MANUAL;
        $data['cancel_at_period_end'] = (bool) ($data['cancel_at_period_end'] ?? false);
        $data['bonus_days'] = (int) ($data['bonus_days'] ?? 0);

        $courseAccess->update($data);

        return redirect()
            ->route('admin.course-accesses.edit', $courseAccess)
            ->with('success', 'Acesso ao curso atualizado com sucesso.');
    }

    public function cancel(CourseAccess $courseAccess): RedirectResponse
    {
        $courseAccess->update([
            'status' => CourseAccess::STATUS_CANCELED,
            'canceled_at' => now(),
            'cancel_at_period_end' => true,
        ]);

        return redirect()
            ->route('admin.course-accesses.index')
            ->with('success', 'Acesso marcado como cancelado. O fim do período vigente foi preservado.');
    }

    private function formData(CourseAccess $access): array
    {
        return [
            'access' => $access,
            'users' => User::query()
                ->where('role', 'student')
                ->orderBy('name')
                ->get(),
            'courses' => Course::query()
                ->where('active', true)
                ->orderBy('title')
                ->get(),
            'statuses' => CourseAccess::statusOptions(),
            'accessTypes' => CourseAccess::accessTypeOptions(),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'student')],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'status' => ['required', Rule::in(array_keys(CourseAccess::statusOptions()))],
            'access_type' => ['nullable', Rule::in(array_keys(CourseAccess::accessTypeOptions()))],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'bonus_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'cancel_at_period_end' => ['nullable', 'boolean'],
        ]);
    }
}
