<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamBoard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExamBoardController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));

        $examBoards = ExamBoard::query()
            ->withCount('questions')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.exam-boards.index', compact('examBoards', 'search'));
    }

    public function create(): View
    {
        $examBoard = new ExamBoard(['active' => true]);
        return view('admin.exam-boards.create', compact('examBoard'));
    }

    public function store(Request $request): RedirectResponse
    {
        ExamBoard::query()->create($this->validatedData($request));
        return redirect()->route('admin.exam-boards.index')->with('success', 'Banca cadastrada com sucesso.');
    }

    public function edit(ExamBoard $examBoard): View
    {
        return view('admin.exam-boards.edit', compact('examBoard'));
    }

    public function update(Request $request, ExamBoard $examBoard): RedirectResponse
    {
        $examBoard->update($this->validatedData($request, $examBoard));
        return redirect()->route('admin.exam-boards.edit', $examBoard)->with('success', 'Banca atualizada com sucesso.');
    }

    public function destroy(ExamBoard $examBoard): RedirectResponse
    {
        if ($examBoard->questions()->exists()) {
            return back()->with('error', 'Não é possível excluir esta banca porque existem questões vinculadas a ela. Desative a banca, se necessário.');
        }

        $examBoard->delete();
        return redirect()->route('admin.exam-boards.index')->with('success', 'Banca removida com sucesso.');
    }

    private function validatedData(Request $request, ?ExamBoard $examBoard = null): array
    {
        $request->merge([
            'slug' => $request->filled('slug')
                ? Str::slug((string) $request->input('slug'))
                : Str::slug((string) $request->input('name')),
            'active' => $request->boolean('active'),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('exam_boards', 'name')->ignore($examBoard?->id)],
            'slug' => ['required', 'string', 'max:120', Rule::unique('exam_boards', 'slug')->ignore($examBoard?->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'active' => ['boolean'],
        ]);
    }
}
